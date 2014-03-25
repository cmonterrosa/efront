{capture name = "view_config"}
	{if !$smarty.get.op || $smarty.get.op == 'general'}
		{capture name="general_security"}
			{eF_template_printForm form=$T_GENERAL_SECURITY_FORM}
		{/capture}
		{capture name = "general_locale"}
			{eF_template_printForm form=$T_GENERAL_LOCALE_FORM}
		{/capture}
		{capture name = "general_smtp"}
			{eF_template_printForm form=$T_GENERAL_SMTP_FORM}
		{/capture}
		{capture name = "external_php"}
			{eF_template_printForm form=$T_GENERAL_PHP_FORM}
		{/capture}

		<div class="tabber">
			{eF_template_printBlock tabber = "main" title=$smarty.const._GENERALSETTINGS data=$smarty.capture.general_main image='32x32/settings.png' help = 'System_settings#General_settings'}
			{eF_template_printBlock tabber = "security" title=$smarty.const._SECURITYSETTINGS data=$smarty.capture.general_security image='32x32/generic.png' help = 'System_settings#Security_settings'}
			{eF_template_printBlock tabber = "locale" title=$smarty.const._LOCALE data=$smarty.capture.general_locale image='32x32/languages.png' help = 'System_settings#Locale_settings'}
			{eF_template_printBlock tabber = "smtp" title=$smarty.const._EMAILSETTINGS data=$smarty.capture.general_smtp image='32x32/mail.png' help = 'System_settings#E-mail_settings'}
			{eF_template_printBlock tabber = "php" title=$smarty.const._CONFIGURATION data=$smarty.capture.external_php image='32x32/php.png' help = 'System_settings#PHP'}
		</div>

	{elseif $smarty.get.op == 'user'}
		{capture name = "user_main"}
			{eF_template_printForm form=$T_USER_MAIN_FORM}
		{/capture}
		{capture name = "user_multiple_logins"}
			{eF_template_printForm form=$T_USER_MULTIPLE_LOGINS_FORM}
		{/capture}
		{capture name = "user_webserver_authentication"}
			{eF_template_printForm form=$T_USER_WEBSERVER_AUTHENTICATION_FORM}
		{/capture}

		<div class="tabber">
			{eF_template_printBlock tabber = "main" title=$smarty.const._USERACTIVATIONSETTINGS data=$smarty.capture.user_main image='32x32/user.png' help = 'System_settings#User_activation.2Fregistration'}
			{eF_template_printBlock tabber = "multiple_logins" title=$smarty.const._MULTIPLELOGINS data=$smarty.capture.user_multiple_logins image='32x32/users.png' help = 'System_settings#Multiple_logins'}
			{eF_template_printBlock tabber = "webserver_authentication" title=$smarty.const._WEBSERVERAUTHENTICATION data=$smarty.capture.user_webserver_authentication image='32x32/generic.png' help = 'System_settings#Web_server_authentication'}
		</div>
	{elseif $smarty.get.op == 'appearance'}
		{capture name = "appearance_main"}
			{eF_template_printForm form=$T_APPEARANCE_MAIN_FORM}
		{/capture}
		{capture name = "appearance_logo"}
			{eF_template_printForm form=$T_APPEARANCE_LOGO_FORM}
		{/capture}
		{capture name = "appearance_favicon"}
			{eF_template_printForm form=$T_APPEARANCE_FAVICON_FORM}
		{/capture}

		<div class="tabber">
			{eF_template_printBlock tabber = "main" title=$smarty.const._APPEARANCE data=$smarty.capture.appearance_main image='32x32/layout.png' help = 'System_settings#Appearance_2'}
			{eF_template_printBlock tabber = "logo" title=$smarty.const._LOGO data=$smarty.capture.appearance_logo image='32x32/themes.png' help = 'System_settings#Logo'}
			{eF_template_printBlock tabber = "favicon" title=$smarty.const._FAVICON data=$smarty.capture.appearance_favicon image='32x32/themes.png' help = 'System_settings#Favicon'}
		</div>
	{elseif $smarty.get.op == 'external'}
		{capture name = "external_main"}
			{eF_template_printForm form=$T_EXTERNAL_MAIN_FORM}
		{/capture}
		{capture name = "external_math"}
			{eF_template_printForm form=$T_EXTERNAL_MATH_FORM}
		{/capture}
		{capture name = "external_livedocx"}
			{eF_template_printForm form=$T_EXTERNAL_LIVEDOCX_FORM}
		{/capture}
{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
	{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
		{capture name = "external_facebook"}
			{eF_template_printForm form=$T_EXTERNAL_FACEBOOK_FORM}
		{/capture}
	{/if} {* #cpp#endif *}
{/if} {* #cpp#endif *}
{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
	{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
		{capture name = "external_ldap"}
			{if $T_EXTENSION_MISSING == 'ldap'}
				{$smarty.const._PHPLDAPEXTENSIONISNOTLOADED}
			{else}
				{eF_template_printForm form=$T_EXTERNAL_LDAP_FORM}
			{/if}
		{/capture}
	{/if} {* #cpp#endif *}
{/if} {* #cpp#endif *}
		<div class="tabber">
			{eF_template_printBlock tabber = "options" title=$smarty.const._EXTERNALTOOLS data=$smarty.capture.external_main image='32x32/generic.png' help = 'System_settings#External_tools_2'}
			{eF_template_printBlock tabber = "math" title=$smarty.const._MATHSETTINGS data=$smarty.capture.external_math image='32x32/generic.png'  help = 'System_settings#Math_Settings'}
			{eF_template_printBlock tabber = "livedocx" title=$smarty.const._PHPLIVEDOCX data=$smarty.capture.external_livedocx image='32x32/generic.png'  help = 'System_settings#PHP_Livedocx'}
			{eF_template_printBlock tabber = "ldap" title=$smarty.const._LDAP data=$smarty.capture.external_ldap image='32x32/generic.png'  help = 'System_settings#LDAP'}
	{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
			{eF_template_printBlock tabber = "facebook" title=$smarty.const._FACEBOOK data=$smarty.capture.external_facebook image='32x32/generic.png' help = 'System_settings#External_tools_2'}
	{/if} {* #cpp#endif *}
		</div>
	{elseif $smarty.get.op == 'customization'}
			{eF_template_printForm form=$T_MODE_FORM}
	{/if}
{/capture}

{*moduleConfig: The configuration settings page*}
{capture name = "moduleConfig"}
	<tr><td class="moduleCell">
		{eF_template_printBlock title = $smarty.const._CONFIGURATIONVARIABLES data = $smarty.capture.view_config image='32x32/tools.png' help = 'System_settings' main_options = $T_TABLE_OPTIONS options = $T_THEMES_LINK}
    </td></tr>
{/capture}