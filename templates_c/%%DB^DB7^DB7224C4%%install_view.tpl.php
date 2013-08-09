<?php /* Smarty version 2.6.18, created on 2007-08-13 11:26:19
         compiled from install_view.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../inc/templates/std_header.tpl", 'smarty_include_vars' => array('page' => ($this->_tpl_vars['page']),'configInstance' => $this->_tpl_vars['configInstance'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<h1><?php echo $this->_tpl_vars['welcome']; ?>
</h1>
	
	<h2><?php echo $this->_tpl_vars['testSection']['title']; ?>
</h2>
	<?php $_from = $this->_tpl_vars['testSection']['tests']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['test']):
?>
	<div class="testresult <?php if ($this->_tpl_vars['test']['result']): ?>testgood<?php else: ?>testbad<?php endif; ?>">
	<?php echo $this->_tpl_vars['test']['message']; ?>
</div>
	<?php endforeach; endif; unset($_from); ?>
	
	<?php echo $this->_tpl_vars['finalMessage']; ?>

	
	<?php if ($this->_tpl_vars['showSettingsForm']): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "install_form.tpl", 'smarty_include_vars' => array('form_fields' => ($this->_tpl_vars['form_fields']),'configInstance' => $this->_tpl_vars['configInstance'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php else: ?>  	
	<?php if (! $this->_tpl_vars['hideUpgradeForm']): ?>
	<h2>Ready to upgrade...</h2>
	<form method="post" action="<?php echo $this->_tpl_vars['SCRIPT_NAME']; ?>
">
	
		<p><input class="button_submit" type="submit" value="start upgrade" />
		<input type="hidden" name="install_form_submitted" value="install_form_submitted" />
		</p>
	</form>
	<?php endif; ?>
	
	<?php endif; ?>

  </body>
</html>