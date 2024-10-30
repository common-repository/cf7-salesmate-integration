<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page='.esc_html($_GET['page']).'&tab='.$this->active_tabsalesmate.'&noheader=true' ) ); ?>" enctype="multipart/form-data">
  <?php wp_nonce_field( 'cf7_salesmate', 'save_cf7_salesmate' ); ?>
  <div class="cf7_salesmate_form">
    <?php if(count($listOwners) > 0){ ?>
    <table class="form-table" width="100%">
    <tr>
        <th scope="row"><label for="cf7_salesmate_form_fields"><?php _e( 'When a Duplicate Record is Found', 'cf7-salesmate' );?></label><br/></th>
        <td>
          <select name="record">
              <option value="3" <?php if($record == 3) echo 'selected="selected"'; ?>>Place a note in timeline</option>
              <option value="1" <?php if($record == 1) echo 'selected="selected"'; ?>>Always create new record</option>
              <option value="2" <?php if($record == 2) echo 'selected="selected"'; ?>>Update existing record</option>
          </select>
        </td>
          </tr>
      <tr>
         <th scope="row"><label for="cf7_salesmate_form_fields"><?php _e( 'Owner', 'cf7-salesmate' );?><span class="required">*</span></label><br/></th>
         <td>
           <div class='cf7_salesmate_field_value field_value_<?php echo $form_id; ?>'>
           <select name="owner_id" id="owner_id" required=required>
             <option value="">Choose owner</option>
             <?php for($i=0; $i<count($listOwners); $i++) { ?>
                <option value="<?php echo $listOwners[$i]['value']; ?>" <?php selected( $owner_values['owner'], $listOwners[$i]['value'] ); ?>><?php echo $listOwners[$i]['label']; ?></option>
             <?php } ?>
            </select>
           </div>
         </td>
      </tr>
      <tr>
         <th scope="row"><label for="cf7_salesmate_form_fields"><?php _e( 'Contacts Fields', 'cf7-salesmate' );?></label><br/></th>
         <td>
           <?php if(count($contact_fields)>0) { ?>
             <?php foreach ($contact_fields as $key => $value) { ?>
               <div class='cf7_salesmate_field_value field_value_<?php echo $form_id; ?>'>
              <label class='salesmate-field-label'><?php echo $key; ?><?php if($value['isRequired']>0){ ?><span class="required">*</span><?php } ?></label>
               <select name="contact_<?php echo $value['name']; ?>" id="contact_<?php echo $value['name']; ?>" <?php if($value['isRequired']>0){ echo 'required=required'; } ?>>
                 <option value="">Choose field</option>
                 <?php foreach ($contactform_fields as $field) { ?>
                    <?php if($field->name != ''){ ?>
                      <option value="<?php echo $field->name; ?>" <?php selected( $contact_values[$value['name']], $field->name ); ?>><?php echo $field->name; ?></option>
                    <?php } ?>
                 <?php } ?>
               </select>
             </div>
             <br/>
            <?php } ?>
          <?php }else { ?>
            <!-- <script>alert("Connection Timeout");</script> -->
            <div class="notice notice-warning is-dismissible">
              <p>Connection Timeout</p>
            </div>
          <?php } ?>
         </td>
      </tr>
      <tr>
        <th scope="row"><label for="cf7_salesmate_form_fields"><?php _e( 'Deal', 'cf7-salesmate' );?></label><br/></th>
        <td>
          <input type="checkbox" class="dealenable" name="dealenable" value="yes" <?php if($dealEnable=='yes') echo 'checked="checked"';?><label for="dealenable">Enable Deal</label><br>
        </td>
      </tr>
    </table>

    <table class="form-table dealSection" style="<?php echo ($dealEnable == 'no' || $dealEnable == '')?'display:none;':'display:block;'; ?>" width="100%">
      <?php if(count($deal_fields)>0){ ?>
       <tr>
         <th scope="row"><label for="cf7_salesmate_form_fields"><?php _e( 'Deal Fields', 'cf7-salesmate' );?></label><br/></th>
           <td>
           <?php foreach ($deal_fields as $key => $dealfieldvalue) { ?>
               <div class='cf7_salesmate_field_value field_value_<?php echo $form_id; ?>'>
                 <label class='salesmate-field-label'><?php echo $key; ?><?php if($dealfieldvalue['isRequired']>0){ ?><span class="required">*</span><?php } ?></label>
                 <select class="<?php echo $dealfieldvalue['class']; ?>" name="dealsalesmate_<?php echo $dealfieldvalue['name']; ?>" id="dealsalesmate_<?php echo $dealfieldvalue['name']; ?>" <?php if($dealfieldvalue['isRequired']>0){ echo ($dealEnable == 'yes')?'required=required':''; } ?>>
                   <option value="">Choose field</option>
                   <?php foreach ($contactform_fields as $fielddeal) { ?>
                      <?php if($fielddeal->name != ''){ ?>
                        <option value="<?php echo $fielddeal->name; ?>" <?php selected( $deal_values[$dealfieldvalue['name']], $fielddeal->name ); ?>><?php echo $fielddeal->name; ?></option>
                      <?php } ?>
                   <?php } ?>
                 </select>
               </div>
               <br/>
           <?php } ?>

            <?php if(isset($dealSources) && $dealSources!=""){ ?>
              <div class='cf7_salesmate_field_value field_value_<?php echo $form_id; ?>'>
              <label class='salesmate-field-label'>Source</label>
              <select name="dealsalesmate_source" id="dealsalesmate_source">
                <option value="">Choose source</option>
                <?php for($i=0; $i<count($dealSources); $i++) { ?>
                   <option value="<?php echo $dealSources[$i]; ?>" <?php selected($deal_values['source'], $dealSources[$i]); ?>><?php echo $dealSources[$i]; ?></option>
                <?php } ?>
              </select>
            </div>
            <br/>
            <?php } ?>

            <?php if(isset($dealPriority) && $dealPriority!=""){ ?>
               <div class='cf7_salesmate_field_value field_value_<?php echo $form_id; ?>'>
               <label class='salesmate-field-label'>Priority</label>
               <select name="dealsalesmate_priority" id="dealsalesmate_priority">
                 <option value="">Choose priority</option>
                 <?php for($i=0; $i<count($dealPriority); $i++) { ?>
                   <option value="<?php echo $dealPriority[$i]; ?>" <?php selected($deal_values['priority'], $dealPriority[$i]); ?>><?php echo $dealPriority[$i]; ?></option>
                 <?php } ?>
               </select>
             </div>
             <br/>
            <?php } ?>

           <?php if(count($dealPipelines)>0 && count($dealStages)>0) {  ?>
             <div class='cf7_salesmate_field_value field_value_<?php echo $form_id; ?>'>
               <label class='salesmate-field-label'>Pipeline<span class="required">*</span></label>
                <select name="dealsalesmate_pipeline" class="medatory" id="dealsalesmate_pipeline">
                   <?php for($i=0;$i<count($dealPipelines);$i++){ ?>
                     <option value="<?php echo $dealPipelines[$i]['value']; ?>" <?php selected( $deal_values['pipeline'], $dealPipelines[$i]['value'] ); ?> data-index="<?php echo $dealPipelines[$i]['data-index']; ?>"><?php echo $dealPipelines[$i]['label']; ?></option>
                  <?php } ?>
                </select>
             </div>
             <br/>

             <div class='cf7_salesmate_field_value field_value_<?php echo $form_id; ?>'>
               <label class='salesmate-field-label'>Stage<span class="required">*</span></label>
                <select name="dealsalesmate_stage" class="medatory" id="dealsalesmate_stage">
                   <?php for($i=0;$i<count($dealStages);$i++){ ?>
                     <option value="<?php echo $dealStages[$i]['value']; ?>" <?php selected( $deal_values['stages'], $dealStages[$i]['value'] ); ?> data-parentindex="<?php echo $dealStages[$i]['data-parentindex']; ?>" ><?php echo $dealStages[$i]['label']; ?></option>
                  <?php } ?>
                </select>
             </div>
             <br/>
           <?php } ?>

           <?php if(count($deal_currencies)>0){ ?>
             <div class='cf7_salesmate_field_value field_value_<?php echo $form_id; ?>'>
             <label class='salesmate-field-label'>Currency</label>
             <select name="dealsalesmate_currency" id="dealsalesmate_currency">
               <option value="">Choose currency</option>
               <?php for($j=0; $j<count($deal_currencies); $j++) { ?>
                 <option value="<?php echo $deal_currencies[$j]['value']; ?>" <?php selected( $deal_values['currency'], $deal_currencies[$j]['value'] ); ?> ><?php echo $deal_currencies[$j]['label']; ?></option>
               <?php } ?>
              </select>
            </div>
           <?php } ?>
         </td>
       </tr>
       <?php }else { ?>
         <!-- <script>alert('Connection Timeout');</script> -->
         <div class="notice notice-warning is-dismissible">
           <p>Connection Timeout</p>
         </div>
       <?php } ?>
    </table>
    <input type="hidden" id="contactform_id" name="contactform_id" value="<?php echo $form_id; ?>" />
    <p class="submit">
      <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
    </p>
  <?php } ?>
  </div>
</form>
