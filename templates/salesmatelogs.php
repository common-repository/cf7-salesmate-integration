<?php
global $wpdb;
$rw = $wpdb->get_results("SELECT * FROM $wpdb->prefix".'salesmatelogs');
?>
<form method="post" class="logform" action="<?php echo esc_url( admin_url( 'admin.php?page='.esc_html($_GET['page']).'&tab='.$this->active_tabsalesmate.'&noheader=true' ) ); ?>" enctype="multipart/form-data">
  <input type="hidden" name="logdata" value="yes"/>
  <?php wp_nonce_field( 'cf7_salesmate', 'save_cf7_salesmate' ); ?>
  <div class="cf7_salesmate_form">
    <h2 class="logtitle">Failed logs</h2>
    <div class="logData">
      <?php if(count($rw)>0) { ?>
      <table>
        <thead>
        <tr>
          <th><span>Index</span></th>
          <th><span>Form Id</span></th>
          <th><span>Module</span></th>
          <th><span>Detail</span></th>
          <th><span>Sended Data</span></th>
          <th><span>Generated At</span></th>
          <th><span>Operations</span></th>
        </tr>
      </thead>
      <tbody>
        <?php  $i=1; foreach ($rw as $datas){ ?>
          <tr>
           <td><?php echo $i; ?></td>
           <td><?php echo $datas->form_id; ?></td>
           <td><?php echo $datas->fromodule; ?></td>
           <td><textarea cols="50" rows="4"><?php echo $datas->errlogs; ?></textarea></td>
           <td><textarea class="sendeddata" cols="50" rows="4"><?php echo $datas->trieddata; ?></textarea></td>
           <td><?php echo $datas->logat; ?></td>
           <td><input type="button" class="button-secondary retry" data-index="<?php echo $datas->id; ?>" value="Retry"/>&nbsp;<input type="button" class="button-secondary remove" value="Delete" data-index="<?php echo $datas->id; ?>"/></td>
         </tr>
        <?php $i++; } ?>
      </tbody>
      </table>
    <?php }else { ?>
      <div class="nodata"><span>No Logs Available</span></div>
     <?php } ?>
    </div>
  </div>
  <?php if(count($rw)>0) { ?>
    <input type="submit" name="Submit" class="button-primary logclear" value="<?php esc_attr_e( 'Clear Logs' ) ?>" />
  <?php } ?>
</form>
