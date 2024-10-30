<?php

/**
 * CF7 Salesmate Class
 *
 * @package   cf7_salesmate
 * @author 		Rapidops Inc.
 * @license   GPL-2.0+
 */

 class Cf7_Salesmate {
   /**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0
	 *
	 */

   const VERSION = '1.0.0';
   public $db;
   /**
	 * Instance of this class.
	 *
	 * @since 1.0
	 *
	 */
   protected static $instancesalesmate = null;

   private function __construct() {
     global $wpdb;
     $this->db = $wpdb;
     // Add Classes
	   require_once plugin_dir_path( __FILE__ ) . '/class/class-cf7-salesmate-admin-settings.php';
     // Load Admin Settings
	   $this->admin_settings = Cf7_Salesmate_Admin_Settings::get_instance();
     
     if($this->admin_settings->cf7_installedsalesmate && $this->admin_settings->cf7_salesmate_domain !='' && $this->admin_settings->cf7_salesmate_accesskey !='' && $this->admin_settings->cf7_salesmate_privatekey !='' && $this->admin_settings->cf7_salesmate_token){
     add_action( 'wpcf7_mail_sent', array( $this, 'salesmate_cf7_send_to_salesmatesubmission' ) );
     }
   }

   /**
	 * Return an instance of this class.
	 *
	 * @since 1.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instancesalesmate) {
			self::$instancesalesmate = new self;
		}

		return self::$instancesalesmate;
	}

  /**
  * Return a salesmate configuration.
  *
  *
  * @return Associative array
  */
  public function salesmate_cf7_salesmateDetails() {
    $domainname = str_replace('https://','', $this->admin_settings->cf7_salesmate_domain);
    $salesmate_header_data = array(
      'x-linkname'=> $domainname,
      'accessToken'=> $this->admin_settings->cf7_salesmate_token,
      'Content-Type'=>'application/json'
    );
    return $salesmate_header_data;
  }

  /**
  * Handle call rquest to api at salesmate.
  *
  * @since 1.0
  *
  * @return object A single instance of this class.
  */
 public function salesmate_cf7_makePostRequest($endpoint=false,$postdata=false,$type,$method=""){
  
     $url1 = "";
     if($type == 'search'){
         $url1 = $this->admin_settings->cf7_salesmate_domain.'/apis/'.$endpoint.'/v4/'.$type;
     }else if($type == '' || $type == 'Owners' || ( $type == 'contact' && $method == 'PUT' )){
        $url1 = $this->admin_settings->cf7_salesmate_domain.'/apis/'.$endpoint;
     } else if($type == 'save' || $type == 'contact'){
         $url1 = $this->admin_settings->cf7_salesmate_domain.'/apis/'.$endpoint.'/v4';
    }else{
         $url1 = $this->admin_settings->cf7_salesmate_domain.'/apis/v1/'.$endpoint;
     }

          $request1 = array(
         'timeout' => 60,
         'body' => $postdata,
         'headers' => $this->salesmate_cf7_salesmateDetails()
     );
     $request2 = array(
      'timeout' => 60,
      'body' => $postdata,
      'method' => $method,
      'headers' => $this->salesmate_cf7_salesmateDetails()
  );
    
  if( $method == "PUT" ){
    $response = wp_remote_request($url1, $request2);
    }else if( $method == "GET" ){
      $response = wp_remote_get($url1, $request1);
    }else{
      $response = wp_remote_post($url1, $request1);
    }  

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
    public function header_data() {

      $check_header_data = array(
        'AppAccessKey:'.$this->admin_settings->cf7_salesmate_accesskey,
        'AppPrivateKey:'.$this->admin_settings->cf7_salesmate_privatekey,
        'sessiontoken:'.$this->admin_settings->cf7_salesmate_token,
        'Content-Type:application/json',
        'cache-control:no-cache',

      );
    return $check_header_data;
    }

 /**
 * Front end form submission handling
 *
 * @since 1.0
 *
 * @return nothing.
 */
public function salesmate_cf7_send_to_salesmatesubmission($submission) {
      $cf7_sends_request = false;
      if(in_array($submission->id(), $this->admin_settings->cf7_salesmate_forms)) {
          $merge_contactfields = array();
          $merge_dealFields = array();
          $dealEnable = '';
          $record = '';
          $submitted_formid = intval(sanitize_text_field($_POST['_wpcf7']));
          $formmeta = get_post_meta($submitted_formid);
          $ownerId = "";
          $email = "";
          $deal_primarycontact_id="";
          $contact_chk = array();
          $company_new = array();
          $new_company_array=array();
          $deal_title = "";
          $companyID = "";

          //Notes for submissions
          $submission2 = WPCF7_Submission::get_instance();
          $posted_data = $submission2->get_posted_data();
        // $uploaded_files = $submission2->uploaded_files();

          $posted_data = array_slice($posted_data,5,count($posted_data));
          $posted_data['Form title'] = $submission->title();
          
          $merge_contactfields['tags'] = $posted_data['your-tags'];
         
          foreach ($formmeta as $key => $metavalue) {
              $data = $metavalue[0];
				
              if(isset($metavalue[0]) && $metavalue[0] != '') {

                 if($key == 'owner'){
                   $merge_contactfields[$key] = !empty($metavalue[0])?(int)$metavalue[0]:1;
                   $ownerId = !empty($metavalue[0])?(int)$metavalue[0]:1;
                 }else if ($key == 'enabledeal') {
                   $dealEnable = $metavalue[0];
                 }else if ($key == 'record') {
                  $record = $metavalue[0];
                } else if($key == 'dealsalesmate_currency'){
                   $merge_dealFields[str_replace('dealsalesmate_', '', $key)] = !empty($metavalue[0])?$metavalue[0]:'';
                 }else if($key == 'dealsalesmate_pipeline'){
                   $merge_dealFields[str_replace('dealsalesmate_', '', $key)] = !empty($metavalue[0])?$metavalue[0]:'';
                 }else if($key == 'dealsalesmate_stage') {
                   $merge_dealFields[str_replace('dealsalesmate_', '', $key)] = !empty($metavalue[0])?$metavalue[0]:'';
                 }else if($key == 'dealsalesmate_dealValue') {
                   $merge_dealFields[str_replace('dealsalesmate_', '', $key)] = (!empty($_POST[$metavalue[0]]) && is_numeric($_POST[$metavalue[0]])) ? (int)sanitize_text_field($_POST[$metavalue[0]]) : 0;
                 }else if($key == 'dealsalesmate_title'){
                   $merge_dealFields[str_replace('dealsalesmate_', '', $key)] = (!empty($_POST[$metavalue[0]])) ? sanitize_text_field($_POST[$metavalue[0]]) : '';
                   $deal_title = (!empty($_POST[$metavalue[0]])) ? sanitize_text_field($_POST[$metavalue[0]]) : '';
                 }else if ($key == 'dealsalesmate_source') {
                   $merge_dealFields[str_replace('dealsalesmate_', '', $key)] = (!empty($metavalue[0])) ? $metavalue[0] : '';
                 }else if($key == 'dealsalesmate_priority'){
                   $merge_dealFields[str_replace('dealsalesmate_', '', $key)] = (!empty($metavalue[0])) ? $metavalue[0] : '';
                 }else if(strpos($key, 'contact_') === 0) {

                   if(str_replace('contact_', '', $key) == 'email' ){

                     if(isset($_POST[$metavalue[0]])){
                       $merge_contactfields[str_replace('contact_', '', $key)] = sanitize_text_field($_POST[$metavalue[0]]);
                       $email = sanitize_email($_POST[$metavalue[0]]);
                     }

                    }else if($key == 'contact_currency'){
                      $merge_contactfields[str_replace('contact_', '', $key)] = !empty($metavalue[0])?$metavalue[0]:'';
                    }
                   else if(str_replace('contact_', '', $key) == 'website') {

                     if(isset($_POST[$metavalue[0]])) {

                       if(!filter_var($_POST[$metavalue[0]], FILTER_VALIDATE_URL) === false){
                           $merge_contactfields[str_replace('contact_', '', $key)] = sanitize_text_field($_POST[$metavalue[0]]);
                       }else {
                           $merge_contactfields[str_replace('contact_', '', $key)] = '';
                       }
                     }

                   }else if(str_replace('contact_', '', $key) == 'company') {

                     if(isset($_POST[$metavalue[0]])) {

                       $seachCompanydata = '{"fields": ["name","website"],"query": {"group": {"operator": "AND","rules": [{"condition": "EQUALS","moduleName": "Company","field": {"fieldName": "name"},"data": "'.sanitize_text_field($_POST[$metavalue[0]]).'"}]}}}';
                       $searchCompany = $this->salesmate_cf7_makePostRequest('company',$seachCompanydata,'search','POST');
                       if(is_object($searchCompany)){
                         if(count($searchCompany->Data->data)>0) {
                           $companyID = $searchCompany->Data->data[0]->id;
                         } else {
                           $company_new['name'] = sanitize_text_field($_POST[$metavalue[0]]);
                           $company_new['owner']= $ownerId;
                           $company_data=json_encode($company_new);
                           $rsltCompany=$this->salesmate_cf7_makePostRequest('company',$company_data,'save','POST');
                           $companyID = $rsltCompany->Data->id;
                        }
                       $merge_contactfields[str_replace('contact_', '', $key)] = $companyID;
                      }

                     }

                   } else {
                     $merge_contactfields[str_replace('contact_', '', $key)] = isset($_POST[$metavalue[0]]) ? sanitize_text_field($_POST[$metavalue[0]]): '' ;
                   }
                 } else if(strpos($key, 'dealsalesmate_') === 0) {
                   $merge_dealFields[str_replace('dealsalesmate_', '', $key)] = isset($_POST[$metavalue[0]]) ? sanitize_text_field($_POST[$metavalue[0]]): '' ;
                 }
            }
          }

          if($ownerId!='') {
            $merge_dealFields['owner'] = $ownerId;
            $merge_dealFields['primaryCompany'] = $companyID;
            $merge_dealFields['status'] = 'Open';
          }

          $send_salesmate_data = json_encode($merge_contactfields);
          $searchContact = '{"displayingFields":["contact.company.name","contact.email","contact.id","contact.mobile","contact.name","contact.lastName"],"filterQuery":{"group":{"operator":"AND","rules":[{"condition":"EQUALS","moduleName":"Contact","field":{"fieldName":"contact.email","displayName":"Email"},"data":"'.$email.'","eventType":"Email"}]}},"sort":{"fieldName":"","order":""},"moduleId":1,"reportType":"get_data","getRecordsCount":true}';
          $search = $this->salesmate_cf7_makePostRequest('contact',$searchContact,'search','POST');
          if(is_object($search)){
           
            if(count($search->Data->data)>0){
              $deal_primarycontact_id = $search->Data->data[0]->id;
              if( $record == 1 ){
                $data = $this->salesmate_cf7_createContact($send_salesmate_data);
              }else if( $record == 2 ){
                $id = $this->sorting_data_by_date($search->Data->data);
                $this->salesmate_cf7_updateContact($send_salesmate_data,$id );
                $this->salesmate_cf7_createNote($id,$posted_data,'contact');
              }else if( $record == 3 ){
                $id = $this->sorting_data_by_date($search->Data->data);
                $this->salesmate_cf7_createNote($id,$posted_data,'contact');
              }
            }
          
            foreach($search->Data->data as $chk_contact){
        			array_push($contact_chk, $chk_contact->email);
      			}
          }
          
          if($email!=''){
            if(!in_array($email,$contact_chk))
            {
              $rslt=$this->salesmate_cf7_makePostRequest('contact',$send_salesmate_data,'save','POST');
              if(is_object($rslt)){
                if($rslt->Status=="success"){
                 
                  $deal_primarycontact_id = $rslt->Data->id;
                  if($dealEnable == 'yes') {
                  }else {
                      $this->salesmate_cf7_createNote($rslt->Data->id,$posted_data,'contact');
                  }
                }
              }else {
                  $insrt=$this->db->insert(
                	$this->db->prefix.'salesmatelogs',
                	array(
                		'form_id' => sanitize_text_field($_POST['_wpcf7']),
                		'fromodule' => 'Contacts',
                    'errlogs' => $rslt,
                    'trieddata'=> $send_salesmate_data,
                    'logat' => current_time('mysql', 1)
                	)
                );
              }
            }
          }else {
            $rslt=$this->salesmate_cf7_makePostRequest('contact',$send_salesmate_data,'save','POST');

            if(is_object($rslt)){
               if($rslt->Status == "success"){
                $deal_primarycontact_id = $rslt->Data->id;
                if($dealEnable == 'yes') {
                }else {
                  $this->salesmate_cf7_createNote($rslt->Data->id,$posted_data,'contact');
                }
              }
            }else {
                  $insrt=$this->db->insert(
                      $this->db->prefix.'salesmatelogs',
                      array(
                        'form_id' => sanitize_text_field($_POST['_wpcf7']),
                        'fromodule' => 'Contacts',
                        'errlogs' => $rslt,
                        'trieddata' => $send_salesmate_data,
                        'logat' => current_time('mysql', 1)
                      )
                  );
              }
            }

          if($deal_primarycontact_id != '') {
            $merge_dealFields['primaryContact'] = $deal_primarycontact_id;
          }

          if($dealEnable == 'yes') {

              $searchDeal = '{"displayingFields":["deal.id","deal.title"],"filterQuery":{"group":{"operator":"AND","rules":[{"condition":"EQUALS","moduleName":"Deal","field":{"fieldName":"deal.title","displayName":"Title","type":"Text"},"data":"'.$deal_title.'","eventType":"Title"}]}},"sort":{"fieldName":"","order":""},"pipeline":"","moduleId":4,"reportType":"get_data","getRecordsCount":true}';
              $dealData = $this->salesmate_cf7_makePostRequest('deal',$searchDeal,'search','POST');
              if(is_object($dealData)){
          //    if(count($dealData->Data->data) <= 0) {
               $dealAddData = json_encode($merge_dealFields);
               $deal = $this->salesmate_cf7_makePostRequest('deal',$dealAddData,'save','POST');
               if($deal->Status == 'success'){
                 $this->salesmate_cf7_createNote($deal->Data->id,$posted_data,'deal');
               }
               if(!is_object($deal)){
                  $this->db->insert(
                     	$this->db->prefix.'salesmatelogs',
                     	array(
                     		'form_id' => sanitize_text_field($_POST['_wpcf7']),
                     		'fromodule' => 'Deal',
                        'errlogs' => $deal,
                        'trieddata'=>$dealAddData,
                        'logat' => current_time('mysql', 1)
                     	)
                  );
               }
             //}
           }else {
              $this->db->insert(
                  $this->db->prefix.'salesmatelogs',
                  array(
                    'form_id' => sanitize_text_field($_POST['_wpcf7']),
                    'fromodule' => 'Deal',
                     'errlogs' => $deal,
                     'trieddata'=>$dealAddData,
                     'logat' => current_time('mysql', 1)
                  )
               );
            }
          }
	     }
   }


   public function sorting_data_by_date( $search_data ){
    
    $diff1 = NULL;
    $recent = NULL;
    foreach($search_data as $value){
      $current_value = $value->createdAt;
            if($diff1 == NULL){        
                $diff1 = $current_value;
                $recent = $value->id;
            }else{
                if($current_value > $diff1){
                  $diff1 = $current_value;
                  $recent = $value->id;
                }
            }
    }
    return $recent;
   }

   public function salesmate_cf7_createNote($contactId,$posted_data,$module) {
     $note_contact="";
     $note_contact.="<p><br>";
     $note_contact.='<b>Contact Form Submission:</b><br><br>';
     foreach ($posted_data as $key => $value) {
       if(!is_array($value)){
         if($value!=''){
			$final_val = preg_replace("/(?:\s*<br[^>]*>\s*){3,}/s", "<br><br>", str_replace(array("\n","\r"), "<br>",trim($value)));
			$note_contact.='<b>'.str_replace("-", " ",preg_replace('/[^A-Za-z0-9\-]/', '',ucfirst(str_replace(" ", "-" ,$key)))).': </b>'.$final_val.'<br>';
         }else {
           $note_contact.='<b>'.str_replace("-", " ",preg_replace('/[^A-Za-z0-9\-]/', '',ucfirst(str_replace(" ", "-" ,$key)))).': </b> - <br>';
         }
       }else {
         $multivaluenote="";
         $j=0;
         for($i=0; $i<count($value); $i++){
             $multivaluenote.= ($j+1).'.'.$value[$i].'<br>';
         }
		 $final_valmulti = preg_replace("/(?:\s*<br[^>]*>\s*){3,}/s", "<br><br>", str_replace(array("\n","\r"), "<br>",trim($multivaluenote)));
         $note_contact.='<b>'.str_replace("-", " ",preg_replace('/[^A-Za-z0-9\-]/', '',ucfirst(str_replace(" ", "-" ,$key)))).': </b>'.$final_valmulti.'<br>';

       }

     }
     $note_contact.="</p>";
     // $note_contact="<p><b>Contact Note</b><br>ID:'".($search->Data->data[0]->id!='')?$search->Data->data[0]->id:'-'."<br>";
     $noteCollection = array();
     $salesmate_cf7_createNote = '{"note":"'.$note_contact.'"}';

     if($module == 'contact'){
       $noteresponse = $this->salesmate_cf7_makePostRequest('modules/1/object/'.$contactId.'/notes',$salesmate_cf7_createNote,'addnote','POST');
     }else {
       $noteresponse = $this->salesmate_cf7_makePostRequest('modules/4/object/'.$contactId.'/notes',$salesmate_cf7_createNote,'addnote','POST');
     }
   }

   public function salesmate_cf7_updateContact($posted_data, $id){
    
    $data = json_decode($posted_data);
    $tags = $data->tags;

    $tags_endpoint = 'contact/'.$id;
    $tags_rslt=$this->salesmate_cf7_makePostRequest($tags_endpoint,'','contact','GET');
    
    $data->tags = $tags_rslt->Data[0]->tags.','.$tags;
    $posted_data = json_encode($data);
    
    $endpoint = 'contact/v4/'.$id;
    $rslt=$this->salesmate_cf7_makePostRequest($endpoint,$posted_data,'contact',"PUT");
    
    if(!is_object($rslt)){
      $err = json_decode($rslt);
      if($err->Status == 'failure'){
        echo "update";
        echo "<pre>";
        print_r($err);
      }
    }
   }
   public function salesmate_cf7_createContact($posted_data){
    $endpoint = 'contact';
      $rslt=$this->salesmate_cf7_makePostRequest($endpoint,$posted_data,'contact','POST');
      
      if(!is_object($rslt)){
        $err = json_decode($rslt);
        if($err->Status == 'failure'){
          echo "create";
        echo "<pre>";
          print_r($err);
        }
      }else{
        return $rslt;
      }
  }
 }
