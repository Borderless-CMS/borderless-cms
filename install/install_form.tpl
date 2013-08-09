<h2>General configuration settings</h2>
<form method="post" action="{$SCRIPT_NAME}">
  <div id="installform" class="form">
	
  <fieldset id="install_form_settings_system">
	<legend>{$legends.system}</legend>
	
	<p><label for="siteUrl">{$form_fields.siteUrl.label}</label>
	<input class="inp required" id="siteUrl" name="siteUrl" value="{$form_fields.siteUrl.value|default:$form_fields.siteUrl.default}" />
	{if $form_fields.siteUrl.comment ne ''}<span class="info comment">{$form_fields.siteUrl.comment}</span>{/if}</p>
	
	<p><label for="user_admin_password">{$form_fields.user_admin_password.label}</label>
	<input type="password" class="inp" name="user_admin_password" id="user_admin_password" value="" />
	{if $form_fields.user_admin_password.comment ne ''}<span class="info comment">{$form_fields.user_admin_password.comment}</span>{/if}</p>
	
	<p><label for="adm_email">{$form_fields.adm_email.label}</label>
	<input class="inp required" id="adm_email" name="adm_email" value="{$form_fields.adm_email.value|default:$form_fields.adm_email.default}" />
	{if $form_fields.adm_email.comment ne ''}<span class="info comment">{$form_fields.adm_email.comment}</span>{/if}</p>
	
	<p><label for="offlineMessage">{$form_fields.offlineMessage.label} <span class="info">(optional)</span></label>
	<input class="inp" id="offlineMessage" name="offlineMessage" value="{$form_fields.offlineMessage.value|default:$form_fields.offlineMessage.default}" />
	{if $form_fields.offlineMessage.comment ne ''}<span class="info comment">{$form_fields.offlineMessage.comment}</span>{/if}</p>
  </fieldset>

  <fieldset id="install_form_settings_db">
	<legend>{$legends.db}</legend>
	
	<p><label for="dbType">{$form_fields.dbType.label}</label>
	<select class="inp required" name="dbType" id="dbType">
		{html_options values=$supported_db_types output=$supported_db_types}
	</select>
	{if $form_fields.dbType.comment ne ''}<span class="info comment">{$form_fields.dbType.comment}</span>{/if}</p>
	
	<p><label for="dbServer">{$form_fields.dbServer.label}</label>
	<input class="inp required" id="dbServer" name="dbServer" value="{$form_fields.dbServer.value|default:$form_fields.dbServer.default}" />
	{if $form_fields.dbServer.comment ne ''}<span class="info comment">{$form_fields.dbServer.comment}</span>{/if}</p>
	
	<p><label for="dbUser">{$form_fields.dbUser.label}</label>
	<input class="inp required" id="dbUser" name="dbUser" value="{$form_fields.dbUser.value|default:$form_fields.dbUser.default}" /> 
	{if $form_fields.dbUser.comment ne ''}<span class="info comment">{$form_fields.dbUser.comment}</span>{/if}</p>
	
	<p><label for="dbPass">{$form_fields.dbPass.label}</label>
	<input type="password" class="inp required" name="dbPass" id="dbPass" value="{$form_fields.dbPass.value}" />
	{if $form_fields.dbPass.comment ne ''}<span class="info comment">{$form_fields.dbPass.comment}</span>{/if}</p>
	
	<p><label for="dbDatabase">{$form_fields.dbDatabase.label}</label>
	<input class="inp required" id="dbDatabase" name="dbDatabase" value="{$form_fields.dbDatabase.value|default:$form_fields.dbDatabase.default}" />
	{if $form_fields.dbDatabase.comment ne ''}<span class="info comment">{$form_fields.dbDatabase.comment}</span>{/if}</p>

	<p><label for="table_prefix">{$form_fields.table_prefix.label} <span class="info">(optional)</span></label>
	<input class="inp" id="table_prefix" name="table_prefix" value="{$form_fields.table_prefix.value|default:$form_fields.table_prefix.default}" />
	{if $form_fields.table_prefix.comment ne ''}<span class="info comment">{$form_fields.table_prefix.comment}</span>{/if}</p>
	
</fieldset>
	
  <div id="install_form_footer">
	<p><label for="confirm_backup_self_accountable" class="checkbox">{$form_fields.confirm_backup_self_accountable.label}</label>
	<input type="checkbox"  name="confirm_backup_self_accountable" id="confirm_backup_self_accountable" /></p>
	
	<p><input class="button submit" type="submit" value="install / upgrade" />
	<input type="hidden" name="install_form_submitted" value="install_form_submitted" />
	</p>
  </div>
  </div>
</form>