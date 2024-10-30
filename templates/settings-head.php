<div class="wrap">
	<h2><?php _e( 'Contact Form 7 Salesmate Settings', 'cf7-salesmate' );?></h2>

	<?php if(!empty($this->cf7_salesmate_forms) && $show_full_form) { ?>

		<h2 class="nav-tab-wrapper">
			<a href="?page=<?php echo CF7_SALESMATE_PLUGIN_SLUG; ?>" class="nav-tab <?php echo $this->active_tabsalesmate == 'general_settings' ? 'nav-tab-active' : ''; ?>">General Settings</a>
		<?php foreach( $this->cf7_salesmate_forms as $cf7_form_id ) { ?>
		  <a href="?page=<?php echo CF7_SALESMATE_PLUGIN_SLUG; ?>&tab=form_<?php echo $cf7_form_id; ?>" class="nav-tab <?php echo $this->active_tabsalesmate == 'form_'. $cf7_form_id ? 'nav-tab-active' : ''; ?>"><?php echo $this->cf7_formsforsalesmate[$cf7_form_id]; ?></a>
		<?php } ?>
			<a href="?page=<?php echo CF7_SALESMATE_PLUGIN_SLUG; ?>&tab=salesmatelogs" class="nav-tab <?php echo $this->active_tabsalesmate == 'salesmatelogs' ? 'nav-tab-active' : ''; ?>">Logs</a>
		</h2>

	<?php } ?>
