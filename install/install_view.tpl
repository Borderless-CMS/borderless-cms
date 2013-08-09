{include file="../inc/templates/std_header.tpl" page=`$page` configInstance=$configInstance}
	<h1>{$welcome}</h1>
	
	<h2>{$testSection.title}</h2>
	{foreach from=$testSection.tests item=test}
	<div class="testresult {if $test.result}testgood{else}testbad{/if}">
	{$test.message}</div>
	{/foreach}
	
	{$finalMessage}
	
	{if $showSettingsForm}
		{include file="install_form.tpl" form_fields=`$form_fields` configInstance=$configInstance}
	{else}  {* end of settings form *}
	
	{if not $hideUpgradeForm}
	<h2>Ready to upgrade...</h2>
	<form method="post" action="{$SCRIPT_NAME}">
	
		<p><input class="button_submit" type="submit" value="start upgrade" />
		<input type="hidden" name="install_form_submitted" value="install_form_submitted" />
		</p>
	</form>
	{/if}
	
	{/if}

  </body>
</html>