<?php
/**
* Admin Settings Pages
*
*
*/

class Cf7_Salesmate_Admin_Settings {

  protected static $instancesalesmate = null;

  public $cf7_salesmate_domain = '';
  public $cf7_salesmate_accesskey = '';
  public $cf7_salesmate_privatekey = '';
  public $cf7_salesmate_token = '';

  public $cf7_installedsalesmate = false;
  public $checkValid = false;

  // public $salesmate = null;

	public $active_tabsalesmate = '';
  public $salesmateAdminAPI = '';
  public static function get_instance() {
    // If the single instance hasn't been set, set it now.
		if ( null == self::$instancesalesmate ) {
			self::$instancesalesmate = new self;
		}
		return self::$instancesalesmate;

	}

  private function __construct() {

  		// Define some variables
      $this->cf7_formsforsalesmate 							= $this->get_cf7_formslist();
      $this->cf7_salesmate_domain 	= get_option( 'cf7_salesmate_domain' );
      $this->cf7_salesmate_accesskey 	= get_option( 'cf7_salesmate_accesskey' );
      $this->cf7_salesmate_privatekey 	= get_option( 'cf7_salesmate_privatekey' );
      $this->cf7_salesmate_token 	= get_option( 'cf7_salesmate_token' );

      $this->cf7_salesmate_forms 		= ( false != get_option( 'my_cf7_salesmate_forms' ) ? get_option( 'my_cf7_salesmate_forms' ) : array() );

  		if(class_exists('WPCF7_ContactForm')) {
  			$this->cf7_installedsalesmate = true;
  		}

      // If it is not installed give admin warning
      if ( !$this->cf7_installedsalesmate ) {
  			add_action('admin_notices', array($this, 'no_cf7_salesmate_admin_notice'));
        return true;
  		}

      // If there is no API Key set, send a warning
	    if($this->cf7_salesmate_domain == '' || $this->cf7_salesmate_accesskey == '' || $this->cf7_salesmate_privatekey == '' || $this->cf7_salesmate_token == '') {
		  	add_action('admin_notices', array($this, 'no_credential_admin_notice'));
		  }

      if($this->cf7_installedsalesmate && is_admin()) {
	      // Add the settings page and menu item.
  			add_action( 'admin_menu', array( $this, 'salesmate_plugin_admin_menu' ) );
  			add_action( 'admin_enqueue_scripts', array($this, 'salesmate_admin_enqueue_scripts') );
        add_action( 'wp_ajax_process_reservation', array( $this, 'process_reservation' ) );
        add_action( 'wp_ajax_nopriv_process_reservation', array( $this, 'process_reservation' ) );

        add_action( 'wp_ajax_remove_log', array( $this, 'remove_log' ) );
        add_action( 'wp_ajax_nopriv_remove_log', array( $this, 'remove_log' ) );
  			// Add an action link pointing to the settings page.
  			add_filter( 'plugin_action_links_' . CF7_SALESMATE_PLUGIN_BASENAME, array( $this, 'salesmate_add_action_links' ) );
  		}

    require_once plugin_dir_path( __FILE__ ) . '/class-admin-salesmateAPI.php';
    $this->salesmateAdminAPI = new Cf7_Salesmate_Admin_API();
  }

  /**
 * Return notice string
 *
 *
 * @return string admin notice if salesmate credential not added
 */
function no_credential_admin_notice(){

  echo '<div class="notice notice-warning is-dismissible">
    <p>Please enter your Salesmate Credential as needed in the <a href="' . admin_url( 'admin.php?page=cf7_salesmate' ) . '">settings</a> to use Contact Form 7 Salesmate Integration.</p>
    </div>';
}

  /**
	 * Add settings action link to the plugins page.
	 *
	 * @param array $links
	 *
	 *
	 * @return array Plugin settings links
	 */
	public function salesmate_add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . CF7_SALESMATE_PLUGIN_SLUG ) . '">' . __( 'Settings', CF7_SALESMATE_PLUGIN_SLUG ) . '</a>'
			),
			$links
		);
	}

  /**
	 * Register the settings menu for this plugin into the WordPress Settings menu.
	 *
	 */
	public function salesmate_plugin_admin_menu() {

		add_submenu_page( 'wpcf7', __( 'Salesmate Integration Settings', 'cf7-salesmate' ), __( 'Salesmate Integration', 'cf7-salesmate' ), 'manage_options', CF7_SALESMATE_PLUGIN_SLUG, array( $this, 'cf7_salesmate_options' ) );
	}

  /**
	 * Enqueue Admin Scripts
	 *
  */
	public function salesmate_admin_enqueue_scripts($hook) {

		if(isset($_GET['page']) && $_GET['page'] == 'cf7_salesmate') {
      echo '<script> var dmn="'.$this->cf7_salesmate_domain.'"; var hdr1 = "'.$this->cf7_salesmate_accesskey.'"; var hdr2 = "'.$this->cf7_salesmate_privatekey.'"; var hdr3 = "'.$this->cf7_salesmate_token.'";</script>';
			wp_enqueue_script( 'cf7_salemate_admin_js', plugins_url( '../assest/js/salesmateAdmin.js', __FILE__ ), array(), rand(0,999) );
      // wp_localize_script('ajax_script', 'myAjax', array('url'=> admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( "process_reservation_nonce" ),));
      wp_enqueue_script( 'cf7_salemate_admin_js' );
      wp_localize_script( 'cf7_salemate_admin_js', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php')) );
      wp_enqueue_style( 'cf7_salemate_admin_css', plugins_url('../assest/css/salesmateadminstyle.css', __FILE__) );
		}
	}

  /**
	 * Return notice string
	 *
	 *
	 *
	 * @return string if not installed parent plugin
	 */
	function no_cf7_salesmate_admin_notice(){
		echo '<div class="notice notice-warning is-dismissible">
			<p>It looks like Contact Form 7 is not installed and is required for CF7 Salesmate on Submission. Please download CF7 to use this plugin.</p>
			</div>';
	}

  /**
  * All forms listing.
  *
  *@return Array
  */
  public function get_cf7_formslist() {

  		// Get all the contact forms
  		 $args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1); $cf7Forms = get_posts( $args );
		 if(count($cf7Forms) > 0){
  		foreach ($cf7Forms as $contact_form) {
  			$this->cf7_formsforsalesmate[$contact_form->ID] = $contact_form->post_title;
  		}
		}
      return $this->cf7_formsforsalesmate;

  	}

    /**
    * All fields of particular form by id.
    *
    *@return Array of form fields
    */
    public function get_cf7_contactform_fields($form_id) {

    		$contact_form = WPCF7_ContactForm::get_instance($form_id);
    		$manager = WPCF7_FormTagsManager::get_instance();
        
    		$scanned_form_tags = $manager->scan( $contact_form->prop( 'form' ) );
    		// $filtered_form_tags = $manager->filter( $scanned_form_tags, NULL );

    		return $scanned_form_tags;
  	}

   /**
	 * Render the settings page for this plugin.
	 *
   * No returns
	 */
	public function cf7_salesmate_options() {

		if ( ! current_user_can( 'edit_posts' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
    // Set the active tab
    $this->active_tabsalesmate = 'general_settings';
    $contact_form_tab = false;

    if( isset( $_GET[ 'tab' ] ) ) {
			$this->active_tabsalesmate = sanitize_key($_GET[ 'tab' ]);
		}

    if(strpos($this->active_tabsalesmate, 'form_') !== false) {
      $contact_form_tab = true;
      $form_id = str_replace('form_', '', $this->active_tabsalesmate);
      $contactform_fields = $this->get_cf7_contactform_fields($form_id);
      
      $contact_fields = $this->getContactFields();
      
      $deal_fields = $this->getDealFields();
     
      $deal_currencies = $this->getDealCurrencyOptionsField();

      $listOwners = $this->getOwners();
      $dealPipelines = $this->getPilineStages('pipeline');
      $dealStages = $this->getPilineStages('Stage');
      $dealSource = $this->getSourcePriority();
      $dealPriority="";
      $dealSources="";
      if(isset($dealSource['priority']) && count($dealSource['priority'])>0){
        $dealPriority = $dealSource['priority'];
      }

      if(isset($dealSource['source']) && count($dealSource['source'])>0){
       $dealSources = $dealSource['source'];
      }

      $contact_currency=$deal_currencies;

      $currency_value = "";
      $contact_values = array();
      $deal_values = array();
      $owner_values = array();
      $dealEnable = "";
      $record = "";

      if(count($listOwners)>0){
          $owner_values['owner'] = get_post_meta($form_id, 'owner', true);
      }

      $dealEnable = get_post_meta($form_id, 'enabledeal', true);

      $record = get_post_meta($form_id, 'record', 3);

      if(count($contact_fields)>0) {
        foreach ($contact_fields as $key => $value) {
    				$contact_values[$value['name']] = get_post_meta($form_id, 'contact_'.$value['name'], true);
  			}
      }

      if(count($contact_currency)>0){
        $contact_values['currency'] = get_post_meta($form_id, 'contact_currency', true);
      }

      if(count($deal_fields)>0) {
        foreach ($deal_fields as $key => $dealvalue) {
    				$deal_values[$dealvalue['name']] = get_post_meta($form_id, 'dealsalesmate_'.$dealvalue['name'], true);
  			}
      }

      if(count($deal_currencies)>0) {
        $deal_values['currency'] = get_post_meta($form_id, 'dealsalesmate_currency', true);
      }

      if(isset($dealSources) && $dealSources!="") {
        $deal_values['source'] = get_post_meta($form_id, 'dealsalesmate_source', true);
      }

      if(isset($dealPriority) && $dealPriority!="") {
        $deal_values['priority'] = get_post_meta($form_id, 'dealsalesmate_priority', true);
      }

      if(count($dealPipelines)>0 && count($dealStages)>0) {
         $deal_values['pipeline'] = get_post_meta($form_id, 'dealsalesmate_pipeline', true);
         $deal_values['stages'] = get_post_meta($form_id, 'dealsalesmate_stage', true);
      }

    }

    // No point in showing the form without an api key
    $show_full_form = false;

		if($this->cf7_salesmate_domain != '' && $this->cf7_salesmate_accesskey != '' && $this->cf7_salesmate_privatekey != '' && $this->cf7_salesmate_token!='') {
			$show_full_form = true;
		}

    if($contact_form_tab && $show_full_form) {
      if ( ! empty( $_POST ) && check_admin_referer( 'cf7_salesmate', 'save_cf7_salesmate' ) ) {
        $this->save_cf7_formlist_settings();
      }
    }else if(!empty($_POST['logdata']) && check_admin_referer( 'cf7_salesmate', 'save_cf7_salesmate' )){
        $this->clearTable();
    }else {
      if ( ! empty( $_POST ) && check_admin_referer( 'cf7_salesmate', 'save_cf7_salesmate' ) ) {
        $this->save_general_salesmate_settings_form();
      }
    }

    // Display the Header
  		include(plugin_dir_path( __FILE__ ).'../templates/settings-head.php');

      // Display the form based on the tab
      if($contact_form_tab && $show_full_form) {
        include(plugin_dir_path( __FILE__ ) . '../templates/mapsalesmatefields.php');
      } else if($this->active_tabsalesmate=='salesmatelogs' && $show_full_form){
        include(plugin_dir_path( __FILE__ ) . '../templates/salesmatelogs.php');
      }else {
        include(plugin_dir_path( __FILE__ ) . '../templates/settings-general.php');
      }

      // Display the footer
    include(plugin_dir_path( __FILE__ ) . '../templates/settings-footer.php');
	}

  /**
  * Salesmate credential.
  *
  *@return Array of salesmate credential
  */
  public function getAdminHeaderData(){
    $domainname = str_replace('https://','', $this->cf7_salesmate_domain);
      $check_header_data = array(
          'x-linkname'=> $domainname,
          'accessToken'=> trim($this->cf7_salesmate_token),
          'Content-Type'=>'application/json'
      );
      return $check_header_data;
  }

  /**
  * Get Deal pipeline and its stages.
  *
  *@return Array
  */
  public function getPilineStages($pipleinorstage){

    $stageandPipeline = $this->salesmateAdminAPI->createCall($this->cf7_salesmate_domain, $this->getAdminHeaderData(),'core/v4/deal-pipelines','GET');
    $stageOptions = array();
    $pipelineOptions = array();

    if(is_object($stageandPipeline)) {
        for($k=0; $k<count($stageandPipeline->Data); $k++) {
             $pipelineOptions[]=array(
               'name' =>$stageandPipeline->Data[$k]->pipeline,
               'value'=>$stageandPipeline->Data[$k]->pipeline,
               'label' => __($stageandPipeline->Data[$k]->pipeline ,'cf7-salesmate'),
               'data-index'=> $k
             );
              for($l=0;$l<count($stageandPipeline->Data[$k]->stages);$l++)
              {
                $stageOptions[]=array(
                  'name' =>$stageandPipeline->Data[$k]->stages[$l]->stage,
                  'value' =>$stageandPipeline->Data[$k]->stages[$l]->stage,
                  'label' => __($stageandPipeline->Data[$k]->stages[$l]->stage,'cf7-salesmate'),
                  'data-parentindex' => $k
                 );
              }
         }
    }

    if($pipleinorstage == 'pipeline') {
      return $pipelineOptions;
    }else {
      return $stageOptions;
    }
  }

  /**
  * Get source and priority options for deal.
  *
  * @return Array
  */
  public function getSourcePriority(){
    $dealsourceFields = $this->salesmateAdminAPI->createCall($this->cf7_salesmate_domain, $this->getAdminHeaderData(),'deals/getEditableFields/v4','GET');
    $sourceOptions = array();
    if(is_object($dealsourceFields)){
      //Source options
      $dataSourece=array_filter($dealsourceFields->Data, function($obj){
        if($obj->fieldName == 'source') {
          return true;
        }
      });
      $optionssource = array_values($dataSourece);
      $optionsData = json_decode($optionssource[0]->fieldOptions);
      $sourceOptions['source'] = $optionsData->values;

      //Priority options
      $dataPriority = array_filter($dealsourceFields->Data, function($obj){

        if($obj->fieldName == 'priority') {
          return true;
        }
      });
      if(count($dataPriority)>0){
      $optionspriority = array_values($dataPriority);
      $optionsPriorityData = json_decode($optionspriority[0]->fieldOptions);
      $sourceOptions['priority'] = $optionsPriorityData->values;
    }
      return $sourceOptions;
    }
  }

  /**
  * Get list of all contact fields from salesamte
  *
  *@return Array
  */
  public function getContactFields(){

     $fields = $this->salesmateAdminAPI->createCall($this->cf7_salesmate_domain, $this->getAdminHeaderData(),'v1/contacts/getEditableFields','GET');
     $contactFields = array();
     $arrayIgnoreContact = array("owner","currency","company");
     if(is_object($fields)){
      
       foreach ($fields->Data as $value_field) {
         if(!in_array($value_field->fieldName, $arrayIgnoreContact) && !in_array($value_field->type, $arrayIgnoreContact)){
          // if($value_field->type!='Boolean' && $value_field->type != 'Lookup' && $value_field->fieldName != 'owner'){
            $contactFields[$value_field->displayName] = array(
               'name' => $value_field->fieldName,
               'label' => esc_html__($value_field->displayName, 'cf7-salesmate'),
               'isRequired' => $value_field->isRequired
            );
          }
       }

       $contactFields['Company']=array(
          'name' => 'company',
          'label' => esc_html__('Company', 'cf7-salesmate'),
          'isRequired' => 0
       );
       
        return $contactFields;

     }else {
        if($fields==4002){
          echo '<div class="notice notice-warning is-dismissible invalidCred">
      			<p>Invalid Credential</p>
      			</div>';
        }
     }
  }

  /**
  * Get currencies of deal.
  *
  *@return Array
  */
  public function getDealCurrencyOptionsField(){

    $currency = $this->salesmateAdminAPI->createCall($this->cf7_salesmate_domain, $this->getAdminHeaderData(),'v3/lookups/active/currency?q=','GET');
    // echo '<pre>';
    // print_r($currency);
    // echo '</pre>';
    $currencyOptions = array();
    if(is_object($currency)) {
       foreach ($currency->Data as $value) {
         $currencyOptions[] = array(
           'name' => $value->name,
           'value' => $value->code,
           'label' => $value->displayLabel
         );
       }
    }
   return $currencyOptions;
  }

  /**
  * Get all avtive owners from salesmate.
  *
  *@return Array
  */
  public function getOwners(){
    
    $fields = $this->salesmateAdminAPI->createCall($this->cf7_salesmate_domain, $this->getAdminHeaderData(),'core/v4/users?status=active','GET');
    $owners = array();
    if(is_object($fields)){
      
      for ($j = 0; $j < count($fields->Data); $j++) {
        $owners[]=array(
          'name' => $fields->Data[$j]->firstName . '' . $fields->Data[$j]->lastName,
          'value' => $fields->Data[$j]->id,
          'label' => __($fields->Data[$j]->firstName . " " . $fields->Data[$j]->lastName,'cf7-salesmate')
        );
      }
      return $owners;

    }else {
      if($fields==4002){
        echo '<div class="notice notice-warning is-dismissible invalidCred">
          <p>Invalid Credential</p>
          </div>';
      }
    }
  }

  /**
  * Get Deal fields from salesmate.
  *
  *@return Array
  */
  public function getDealFields(){

    $dealFields = $this->salesmateAdminAPI->createCall($this->cf7_salesmate_domain, $this->getAdminHeaderData(),'v1/deals/getEditableFields','GET');
    $displayDealFields = array();
    $arrayIgnore = array("primaryContact","owner","priority","pipeline","stage","status","source","currency");
    if(is_object($dealFields)){
      foreach ($dealFields->Data as $fieldvalue) {

         if(!in_array($fieldvalue->fieldName, $arrayIgnore) &&  !in_array($fieldvalue->type, $arrayIgnore)){

           $displayDealFields[$fieldvalue->displayName] = array(
              'name' => $fieldvalue->fieldName,
              'label' => esc_html__($fieldvalue->displayName, 'cf7-salesmate'),
              'isRequired' => $fieldvalue->isRequired,
              'class' => ($fieldvalue->isRequired == 1)?'medatory':''
            );

         }
      }
    }

    return $displayDealFields;
  }

  /**
  * Save all general settings at admin side.
  *
  *@return Array
  */
  protected function save_general_salesmate_settings_form(){
    //add or update cf7 pipedrive API Key
	$salesmate_domain = esc_url_raw( $_POST['domainname'] );
    $salesmate_accesskey = sanitize_key( $_POST['appaccesskey'] );
    $salesmate_privatekey = sanitize_key( $_POST['appprivatekey'] );
    $salesmate_token = sanitize_key( $_POST['sessiontoken'] );

		if ( $this->cf7_salesmate_domain !== false && $this->cf7_salesmate_accesskey !== false && $this->cf7_salesmate_privatekey !== false && $this->cf7_salesmate_token !== false) {

			update_option( 'cf7_salesmate_domain', $salesmate_domain );
      update_option( 'cf7_salesmate_accesskey', $salesmate_accesskey );
      update_option( 'cf7_salesmate_privatekey', $salesmate_privatekey );
      update_option( 'cf7_salesmate_token', $salesmate_token );
		} else {

			add_option( 'cf7_salesmate_domain', $salesmate_domain, null, 'no' );
      add_option( 'cf7_salesmate_accesskey', $salesmate_accesskey, null, 'no' );
      add_option( 'cf7_salesmate_privatekey', $salesmate_privatekey, null, 'no' );
      add_option( 'cf7_salesmate_token', $salesmate_token, null, 'no' );
		}

    //Save form to map with salesmate
  	if ( $this->cf7_formsforsalesmate !== false ) {
      if( isset($_POST['cf7_salesmate_forms']) && is_array($_POST['cf7_salesmate_forms']) ) {
        $cf7_salesmate_forms = $_POST['cf7_salesmate_forms'];
				$cf7_salesmate_forms = array_map('absint', $cf7_salesmate_forms);

				if(is_array($cf7_salesmate_forms)) {
					update_option( 'my_cf7_salesmate_forms', $cf7_salesmate_forms );
					$this->cf7_salesmate_forms = get_option('my_cf7_salesmate_forms');
				}

      }else {
          update_option( 'my_cf7_salesmate_forms', array() );
		      $this->cf7_salesmate_forms = array();
      }
    }

     wp_redirect( admin_url( 'admin.php?page='.sanitize_key($_GET['page']).'&updated=1' ) );
  }

  /*
  * Remove all data from log table
  * @Return nothing
  */
  protected function clearTable(){
    global $wpdb;
    $delete = $wpdb->query("TRUNCATE TABLE $wpdb->prefix".'salesmatelogs');
    wp_redirect( admin_url( 'admin.php?page='.sanitize_key($_GET['page']).'&tab='.$this->active_tabsalesmate.'&updated=1' ) );
  }

  /*
  * Retry post remote request from the log table
  */
  public function retryPostRequest($endpoint=false,$postdata=false,$type){

      $url1 = "";

      if($type == 'search'){
          $url1 = $this->cf7_salesmate_domain.'/apis/v4'.$endpoint;
      } else {
          $url1 = $this->cf7_salesmate_domain.'/apis/v1/'.$endpoint;
      }

      $request1 = array(
          'timeout' => 60,
          'body' => $postdata,
          'headers' => $this->getAdminHeaderData()
      );

     $response = wp_remote_post($url1, $request1);

     if(!is_wp_error($response) && isset( $response['body'] )){
         $data = json_decode($response['body']);
         if($data->Status == 'success')
         {
            return $data;
         }else {
           return $response['body'];
         }

     }else {
       return;
     }
  }

  public function process_reservation(){
    global $wpdb;
    $data = $wpdb->get_results("SELECT trieddata,fromodule FROM $wpdb->prefix".'salesmatelogs where id='.sanitize_key($_POST['myID']));

    $resend=$data[0]->trieddata;
    $modules = $data[0]->fromodule;

    if($resend!="" && $modules!="" ){

      switch ($data[0]->fromodule) {

        case 'Contacts':
            $rslt=$this->retryPostRequest('contacts',$resend,'contact');
              if(is_object($rslt)){
                if($rslt->Status == "success"){
                  $removeqry = $wpdb->delete( $wpdb->prefix.'salesmatelogs', array( 'id' => sanitize_key($_POST['myID'] )) );
                  echo $removeqry;
                }
              }else {
                 $err = json_decode($rslt);
                 if($err->Status == 'failure'){
                   echo 2;
                 }
              }
          break;
        case 'Deal':
            $deal = $this->retryPostRequest('deals',$resend,'Deal');
            if(is_object($deal)){
              $removeqry = $wpdb->delete( $wpdb->prefix.'salesmatelogs', array( 'id' => sanitize_key($_POST['myID'] )) );
              echo $removeqry;
            }else {
              $err = json_decode($deal);
              if($err->Status == 'failure'){
                echo 2;
              }
            }
        default:

          break;

      }

    }else {
      echo 4;
    }
    wp_die();
  }

  public function remove_log(){
    global $wpdb;
    $removeqry = $wpdb->delete( $wpdb->prefix.'salesmatelogs', array( 'id' => sanitize_key($_POST['myID'] )) );
    echo $removeqry;
    wp_die();
  }
  /**
  * Save all the mapping fields of salesmate with contact form 7's fields.
  *
  *@return Array
  */
  protected function save_cf7_formlist_settings() {

    $contact_fields = $this->getContactFields();
    $deals_fields = $this->getDealFields();

    // Sanity check
		if(isset($_POST['contactform_id'])) {
			$form_id = esc_attr($_POST['contactform_id']);

    } else {
      trigger_error('Salesmate Error: No Form ID with contact form saved data');
			return;
		}

    if(isset($_POST['owner_id']) && !empty($_POST['owner_id'])){
      update_post_meta($form_id, 'owner', esc_attr($_POST['owner_id']));
    }


    foreach ($contact_fields as $key => $value) {
      if(isset($_POST['contact_'.$value['name']])) {
        update_post_meta($form_id, 'contact_'.$value['name'], esc_attr($_POST['contact_'.$value['name']]));
      }
    }

    /*Deal Section*/
    foreach ($deals_fields as $key => $dealvalue) {
      if(isset($_POST['dealsalesmate_'.$dealvalue['name']])) {
        update_post_meta($form_id, 'dealsalesmate_'.$dealvalue['name'], esc_attr($_POST['dealsalesmate_'.$dealvalue['name']]));
      }
    }

    if(isset($_POST['dealsalesmate_pipeline'])){
      update_post_meta($form_id, 'dealsalesmate_pipeline', esc_attr($_POST['dealsalesmate_pipeline']));
    }

    if(isset($_POST['dealsalesmate_stage'])){
      update_post_meta($form_id, 'dealsalesmate_stage', esc_attr($_POST['dealsalesmate_stage']));
    }

    if(isset($_POST['dealsalesmate_source'])) {
      update_post_meta($form_id, 'dealsalesmate_source', esc_attr($_POST['dealsalesmate_source']));
    }

    if(isset($_POST['dealsalesmate_priority'])) {
      update_post_meta($form_id, 'dealsalesmate_priority', esc_attr($_POST['dealsalesmate_priority']));
    }

    if(isset($_POST['dealsalesmate_currency'])){
      update_post_meta($form_id, 'dealsalesmate_currency', esc_attr($_POST['dealsalesmate_currency']));
    }

    if(isset($_POST['contact_currency'])){
      update_post_meta($form_id, 'contact_currency', esc_attr($_POST['contact_currency']));
    }

    if(isset($_POST['dealenable']) && !empty($_POST['dealenable'])){
      update_post_meta($form_id, 'enabledeal', esc_attr($_POST['dealenable']));
    }else {
      update_post_meta($form_id, 'enabledeal', 'no');
    }

    if(isset($_POST['record']) && !empty($_POST['record'])){
      update_post_meta($form_id, 'record', esc_attr($_POST['record']));
    }else {
      update_post_meta($form_id, 'record', '3');
    }

    wp_redirect( admin_url( 'admin.php?page='.sanitize_key($_GET['page']).'&tab='.$this->active_tabsalesmate.'&updated=1' ) );
  }

}
