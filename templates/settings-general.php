<?php
	$fields = $this->salesmateAdminAPI->createCall($this->cf7_salesmate_domain, $this->getAdminHeaderData(),'users/active','GET');
	$flag = true;
	if(!is_object($fields)){
		if($fields == 4002){
			echo '<div class="notice notice-warning is-dismissible invalidCred">
				<p>Invalid Credential</p>
				</div>';
				$flag = false;
		}
	}
 ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page='.esc_html($_GET["page"]).'&noheader=true' ) ); ?>" enctype="multipart/form-data">
	<?php wp_nonce_field( 'cf7_salesmate', 'save_cf7_salesmate' ); ?>
	<div class="cf7_salesmate_form">
		<table class="form-table" width="100%">
      <!--Domain Name -->
			<tr>
				<th scope="row"><label for="cf7_salesmate_domain"><?php _e( 'Domain Name', 'cf7-salesmate' );?></label></th>
				<td><input type="text" name="domainname" placeholder="Ex:  https://example.salesmate.io" id="cf7_salesmate_domain" maxlength="255" size="75" value="<?php echo $this->cf7_salesmate_domain; ?>"></td>
			</tr>

      <!--App Access Key -->
      <tr>
				<th scope="row"><label for="cf7_salesmate_appaccesskey"><?php _e( 'App Access Key', 'cf7-salesmate' );?></label></th>
				<td><input type="text" name="appaccesskey" id="cf7_salesmate_appaccesskey" maxlength="255" size="75" value="<?php echo $this->cf7_salesmate_accesskey; ?>"></td>
			</tr>

      <!--App Private Key -->
      <tr>
				<th scope="row"><label for="cf7_salesmate_appprivatekey"><?php _e( 'App Private Key', 'cf7-salesmate' );?></label></th>
				<td><input type="text" name="appprivatekey" id="cf7_salesmate_appprivatekey" maxlength="255" size="75" value="<?php echo $this->cf7_salesmate_privatekey; ?>"></td>
			</tr>

      <!--Session Tokens -->
      <tr>
				<th scope="row"><label for="cf7_salesmate_sessiontoken"><?php _e( 'Session Token', 'cf7-salesmate' );?></label></th>
				<td><input type="text" name="sessiontoken" id="cf7_salesmate_sessiontoken" maxlength="255" size="75" value="<?php echo $this->cf7_salesmate_token; ?>"></td>
			</tr>
      <?php if($show_full_form && $flag){ ?>
        <tr>
  				<th scope="row"><label for="cf7_salemate_form"><?php _e( 'Contact Form 7', 'cf7-salesmate' );?></label><br/><small>Map the Contact Forms you want.</small></label></th>
  				<td>
  					<?php foreach ( $this->cf7_formsforsalesmate as $form_id => $form_title ): ?>
  					<input type="checkbox" name="cf7_salesmate_forms[]" value="<?php echo $form_id; ?>" <?php if(in_array($form_id, $this->cf7_salesmate_forms)) echo 'checked="checked"';?><label for="<?php echo $form_title; ?>"><?php echo $form_title; ?></label><br>
  					<?php endforeach;?>
  				</td>
        </tr>
      <?php } ?>
		</table>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
		</p>
	</div>
</form>
