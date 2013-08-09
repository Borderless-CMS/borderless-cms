<?php /* Smarty version 2.6.18, created on 2007-08-13 11:26:19
         compiled from install_form.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'install_form.tpl', 9, false),array('function', 'html_options', 'install_form.tpl', 30, false),)), $this); ?>
<h2>General configuration settings</h2>
<form method="post" action="<?php echo $this->_tpl_vars['SCRIPT_NAME']; ?>
">
  <div id="installform" class="form">
	
  <fieldset id="install_form_settings_system">
	<legend><?php echo $this->_tpl_vars['legends']['system']; ?>
</legend>
	
	<p><label for="siteUrl"><?php echo $this->_tpl_vars['form_fields']['siteUrl']['label']; ?>
</label>
	<input class="inp required" id="siteUrl" name="siteUrl" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['form_fields']['siteUrl']['value'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['form_fields']['siteUrl']['default']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['form_fields']['siteUrl']['default'])); ?>
" />
	<?php if ($this->_tpl_vars['form_fields']['siteUrl']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['siteUrl']['comment']; ?>
</span><?php endif; ?></p>
	
	<p><label for="user_admin_password"><?php echo $this->_tpl_vars['form_fields']['user_admin_password']['label']; ?>
</label>
	<input type="password" class="inp" name="user_admin_password" id="user_admin_password" value="" />
	<?php if ($this->_tpl_vars['form_fields']['user_admin_password']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['user_admin_password']['comment']; ?>
</span><?php endif; ?></p>
	
	<p><label for="adm_email"><?php echo $this->_tpl_vars['form_fields']['adm_email']['label']; ?>
</label>
	<input class="inp required" id="adm_email" name="adm_email" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['form_fields']['adm_email']['value'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['form_fields']['adm_email']['default']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['form_fields']['adm_email']['default'])); ?>
" />
	<?php if ($this->_tpl_vars['form_fields']['adm_email']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['adm_email']['comment']; ?>
</span><?php endif; ?></p>
	
	<p><label for="offlineMessage"><?php echo $this->_tpl_vars['form_fields']['offlineMessage']['label']; ?>
 <span class="info">(optional)</span></label>
	<input class="inp" id="offlineMessage" name="offlineMessage" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['form_fields']['offlineMessage']['value'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['form_fields']['offlineMessage']['default']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['form_fields']['offlineMessage']['default'])); ?>
" />
	<?php if ($this->_tpl_vars['form_fields']['offlineMessage']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['offlineMessage']['comment']; ?>
</span><?php endif; ?></p>
  </fieldset>

  <fieldset id="install_form_settings_db">
	<legend><?php echo $this->_tpl_vars['legends']['db']; ?>
</legend>
	
	<p><label for="dbType"><?php echo $this->_tpl_vars['form_fields']['dbType']['label']; ?>
</label>
	<select class="inp required" name="dbType" id="dbType">
		<?php echo smarty_function_html_options(array('values' => $this->_tpl_vars['supported_db_types'],'output' => $this->_tpl_vars['supported_db_types']), $this);?>

	</select>
	<?php if ($this->_tpl_vars['form_fields']['dbType']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['dbType']['comment']; ?>
</span><?php endif; ?></p>
	
	<p><label for="dbServer"><?php echo $this->_tpl_vars['form_fields']['dbServer']['label']; ?>
</label>
	<input class="inp required" id="dbServer" name="dbServer" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['form_fields']['dbServer']['value'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['form_fields']['dbServer']['default']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['form_fields']['dbServer']['default'])); ?>
" />
	<?php if ($this->_tpl_vars['form_fields']['dbServer']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['dbServer']['comment']; ?>
</span><?php endif; ?></p>
	
	<p><label for="dbUser"><?php echo $this->_tpl_vars['form_fields']['dbUser']['label']; ?>
</label>
	<input class="inp required" id="dbUser" name="dbUser" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['form_fields']['dbUser']['value'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['form_fields']['dbUser']['default']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['form_fields']['dbUser']['default'])); ?>
" /> 
	<?php if ($this->_tpl_vars['form_fields']['dbUser']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['dbUser']['comment']; ?>
</span><?php endif; ?></p>
	
	<p><label for="dbPass"><?php echo $this->_tpl_vars['form_fields']['dbPass']['label']; ?>
</label>
	<input type="password" class="inp required" name="dbPass" id="dbPass" value="<?php echo $this->_tpl_vars['form_fields']['dbPass']['value']; ?>
" />
	<?php if ($this->_tpl_vars['form_fields']['dbPass']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['dbPass']['comment']; ?>
</span><?php endif; ?></p>
	
	<p><label for="dbDatabase"><?php echo $this->_tpl_vars['form_fields']['dbDatabase']['label']; ?>
</label>
	<input class="inp required" id="dbDatabase" name="dbDatabase" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['form_fields']['dbDatabase']['value'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['form_fields']['dbDatabase']['default']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['form_fields']['dbDatabase']['default'])); ?>
" />
	<?php if ($this->_tpl_vars['form_fields']['dbDatabase']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['dbDatabase']['comment']; ?>
</span><?php endif; ?></p>

	<p><label for="table_prefix"><?php echo $this->_tpl_vars['form_fields']['table_prefix']['label']; ?>
 <span class="info">(optional)</span></label>
	<input class="inp" id="table_prefix" name="table_prefix" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['form_fields']['table_prefix']['value'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['form_fields']['table_prefix']['default']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['form_fields']['table_prefix']['default'])); ?>
" />
	<?php if ($this->_tpl_vars['form_fields']['table_prefix']['comment'] != ''): ?><span class="info comment"><?php echo $this->_tpl_vars['form_fields']['table_prefix']['comment']; ?>
</span><?php endif; ?></p>
	
</fieldset>
	
  <div id="install_form_footer">
	<p><label for="confirm_backup_self_accountable" class="checkbox"><?php echo $this->_tpl_vars['form_fields']['confirm_backup_self_accountable']['label']; ?>
</label>
	<input type="checkbox"  name="confirm_backup_self_accountable" id="confirm_backup_self_accountable" /></p>
	
	<p><input class="button submit" type="submit" value="install / upgrade" />
	<input type="hidden" name="install_form_submitted" value="install_form_submitted" />
	</p>
  </div>
  </div>
</form>