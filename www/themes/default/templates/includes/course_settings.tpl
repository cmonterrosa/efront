{if $T_OP == course_info}
	{capture name = 't_course_info_code'}
		<fieldset class = "fieldsetSeparator">
			<legend>{$smarty.const._COURSEINFORMATION}</legend>
			{$T_COURSE_INFO_HTML}
		</fieldset>
		<fieldset class = "fieldsetSeparator">
			<legend>{$smarty.const._COURSEMETADATA}</legend>
			{$T_COURSE_METADATA_HTML}
		</fieldset>
	{/capture}
	{eF_template_printBlock title = "`$smarty.const._INFORMATIONFORCOURSE`<span class = 'innerTableName'>&nbsp;&quot;`$T_CURRENT_COURSE->course.name`&quot;</span>" data = $smarty.capture.t_course_info_code image = '32x32/information.png'  main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}
{elseif $T_OP == 'course_certificates'}
	{if $smarty.get.edit_user}
		{capture name = 't_course_user_progress'}
		<fieldset class = "fieldsetSeparator">
			<legend>{$smarty.const._LESSONSPROGRESS}</legend>
			<table>
				<tr>
			{foreach name = 'lessons_list' item = "lesson" key = "id" from = $T_USER_COURSE_LESSON_STATUS}
					<td style = "width:50%;">
					<table>
						<tr><td class = "labelCell">{$smarty.const._LESSON}:&nbsp;</td>
							<td class = "elementCell">{$lesson->lesson.name}</td></tr>
						<tr><td class = "labelCell">{$smarty.const._COMPLETED}:&nbsp;</td>
							<td class = "elementCell">{if $lesson->lesson.completed}{$smarty.const._YES}{else}{$smarty.const._NO}{/if}</td></tr>
					{if $lesson->lesson.score}
						<tr><td class = "labelCell">{$smarty.const._SCORE}:&nbsp;</td>
							<td class = "elementCell">{$lesson->lesson.score}&nbsp;%</td></tr>
					{/if}
						<tr><td class = "labelCell">{$smarty.const._CONTENTDONE}:&nbsp;</td>
							<td class = "progressCell" style = "vertical-align:top">
								<span class = "progressNumber">{if $lesson->lesson.overall_progress.percentage}{$lesson->lesson.overall_progress.percentage}{else}0{/if}%</span>
								<span class = "progressBar" style = "width:{$lesson->lesson.overall_progress.percentage}px;">&nbsp;</span>
							</td></tr>
					</table>
					</td>
				</tr><tr>
			{/foreach}
				</tr>
			</table>
		</fieldset>
		<fieldset class = "fieldsetSeparator">
			<legend>{$smarty.const._COMPLETECOURSE}</legend>
			{$T_COMPLETE_LESSON_FORM.javascript}
			<form {$T_COMPLETE_COURSE_FORM.attributes}>
				{$T_COMPLETE_COURSE_FORM.hidden}
				<table class = "formElements">
					<tr><td class = "labelCell">{$T_COMPLETE_COURSE_FORM.completed.label}&nbsp;</td><td>{$T_COMPLETE_COURSE_FORM.completed.html}</td></tr>
					
					<tr><td class = "labelCell">{$smarty.const._COMPLETEDON}:&nbsp;</td>
						<td class = "elementCell">{if !$T_USER_COURSE->user.completed}#filter:timestamp_time-{$T_USER_COURSE->user.to_timestamp}#{/if}
						{eF_template_html_select_date prefix="completion_" time = $T_TO_TIMESTAMP start_year="-5" end_year="+5" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="completion_" time = $T_TO_TIMESTAMP display_seconds = false}	
					</td></tr>
						
					<tr><td class = "labelCell">{$T_COMPLETE_COURSE_FORM.score.label}&nbsp;</td><td>{$T_COMPLETE_COURSE_FORM.score.html}</td></tr>
					{if !$T_USER_COURSE->user.completed}
					<tr><td></td>
						<td class = "infoCell">{$smarty.const._PROPOSEDSCOREISAVERAGELESSONSCORE}</td></tr>
					{/if}
					{if $T_COMPLETE_COURSE_FORM.score.error}<tr><td></td><td class = "formError">{$T_COMPLETE_COURSE_FORM.score.error}</td></tr>{/if}
					<tr><td></td>
						<td><span>
							<img onclick = "toggledInstanceEditor = 'comments';javascript:toggleEditor('comments','simpleEditor');" class = "handle" style="vertical-align:middle" src = "images/16x16/order.png" title = "{$smarty.const._TOGGLEHTMLEDITORMODE}" alt = "{$smarty.const._TOGGLEHTMLEDITORMODE}" />&nbsp;
							<a href = "javascript:void(0)" onclick = "toggledInstanceEditor = 'comments';javascript:toggleEditor('comments','simpleEditor');" id = "toggleeditor_link">{$smarty.const._TOGGLEHTMLEDITORMODE}</a>
						</span></td></tr>
					<tr><td class = "labelCell">{$T_COMPLETE_COURSE_FORM.comments.label}&nbsp;</td><td>{$T_COMPLETE_COURSE_FORM.comments.html}</td></tr>
					{if $T_COMPLETE_COURSE_FORM.comments.error}<tr><td></td><td class = "formError">{$T_COMPLETE_COURSE_FORM.comments.error}</td></tr>{/if}
					<tr><td colspan = "100%">&nbsp;</td></tr>
					<tr><td></td><td>{$T_COMPLETE_COURSE_FORM.submit_course_complete.html}</td></tr>
				</table>
			</form>
		</fieldset>
		{/capture}
		{eF_template_printBlock title = "`$T_USER_COURSE->user.name` `$T_USER_COURSE->user.surname`&#039s `$smarty.const._PROGRESS`" data = $smarty.capture.t_course_user_progress image = '32x32/users.png' help='Course_actions'}
	{if $T_MESSAGE_TYPE == 'success'}
	<script>
		re = /\?/;
		!re.test(parent.location) ? parent.location = parent.location+'?reset_popup=1' : parent.location = parent.location+'&reset_popup=1';
	</script>
	{/if}
	{elseif $smarty.get.issue_certificate}

	{else}
		{capture name = 't_course_certificates_code'}
			<script>var autocompleteyes = '{$smarty.const._AUTOCOMPLETE}: {$smarty.const._YES}';var autocompleteno = '{$smarty.const._AUTOCOMPLETE}: {$smarty.const._NO}';
					var autocertificateyes = '{$smarty.const._AUTOMATICCERTIFICATES}: {$smarty.const._YES}';var autocertificateno = '{$smarty.const._AUTOMATICCERTIFICATES}: {$smarty.const._NO}';</script>
			<div class = "headerTools">
				<span>
					<img src = "images/16x16/autocomplete.png" title = "{$smarty.const._AUTOCOMPLETE}" alt = "{$smarty.const._AUTOCOMPLETE}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'} onclick = "setAutoComplete(this)" {/if}>{$smarty.const._AUTOCOMPLETE}: {if $T_CURRENT_COURSE->options.auto_complete}{$smarty.const._YES}{else}{$smarty.const._NO}{/if}</a>
				</span>
			{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
				<span>
					<img src = "images/16x16/certificate.png" title = "{$smarty.const._FORMATCERTIFICATE}" alt = "{$smarty.const._FORMATCERTIFICATE}"/>
					<a href = "{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=format_certificate" >{$smarty.const._FORMATCERTIFICATE}</a>
				</span>
				<span id = "auto_certificates" {if !$T_CURRENT_COURSE->options.auto_complete}style = "display:none"{/if}>
					<img src = "images/16x16/certificate.png" title = "{$smarty.const._AUTOCERTIFICATES}" alt = "{$smarty.const._AUTOCERTIFICATES}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'} onclick = "setAutoCertificate(this)" {/if}>{$smarty.const._AUTOMATICCERTIFICATES}: {if $T_CURRENT_COURSE->options.auto_certificate}{$smarty.const._YES}{else}{$smarty.const._NO}{/if}</a>
				</span>
				<span id = "certificate_all">
					<img src = "images/16x16/certificate.png" title = "{$smarty.const._ISSUECERTIFICATEFORCOMPLTETED}" alt = "{$smarty.const._ISSUECERTIFICATEFORCOMPLTETED}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'} onclick = "issueCertificateAll(this)" {/if}>{$smarty.const._ISSUECERTIFICATEFORCOMPLTETED}</a>
				</span>
				<span id = "certificate_date">
					<img src = "images/16x16/certificate.png" title = "{$smarty.const._SETCERTIFICATEDATEFORCOMPLTETED}" alt = "{$smarty.const._SETCERTIFICATEDATEFORCOMPLTETED}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'} onclick = "setCertificateDate(this)" {/if}>{$smarty.const._SETCERTIFICATEDATEFORCOMPLTETED}</a>
				</span>
				<span id = "revole_expired">
					<img src = "images/16x16/certificate.png" title = "{$smarty.const._REVOKECERTIFICATEFOREXPIRED}" alt = "{$smarty.const._REVOKECERTIFICATEFOREXPIRED}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'} onclick = "revokeExpiredCertificates(this)" {/if}>{$smarty.const._REVOKECERTIFICATEFOREXPIRED}</a>
				</span>
			{/if} {* #cpp#endif *}
				<span>
					<img src = "images/16x16/success.png" title = "{$smarty.const._SETALLUSERSSTATUSCOMPLETED}" alt = "{$smarty.const._SETALLUSERSSTATUSCOMPLETED}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'} onclick = "setAllUsersStatusCompleted(this)" {/if}>{$smarty.const._SETALLUSERSSTATUSCOMPLETED}</a>
				</span>
				<span>
					<img src = "images/16x16/success.png" title = "{$smarty.const._SETSHOWNUSERSSTATUSCOMPLETED}" alt = "{$smarty.const._SETSHOWNUSERSSTATUSCOMPLETED}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'} onclick = "setShownUsersStatusCompleted(this)" {/if}>{$smarty.const._SETSHOWNUSERSSTATUSCOMPLETED}</a>
				</span>
				<span>
					<img src = "images/16x16/refresh.png" title = "{$smarty.const._RESETALLUSERS}" alt = "{$smarty.const._RESETALLUSERS}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'} onclick = "if (confirm('{$smarty.const._IRREVERSIBLEACTIONAREYOUSURE}')) resetAllUsers(this)" {/if}>{$smarty.const._RESETALLUSERS}</a>
				</span>
			</div>
			{assign var = "courseUsers_url" value = "`$smarty.server.PHP_SELF`?`$T_BASE_URL`&op=course_certificates&"}
			{assign var = "_change_handles_" value = false}
			{assign var = "certificate_export_method" value = $T_CERTIFICATE_EXPORT_METHOD}
			{include file = "includes/common/course_users_list.tpl"}
		{/capture}
		{eF_template_printBlock title = "`$smarty.const._COMPLETION`<span class = 'innerTableName'>&nbsp;&quot;`$T_CURRENT_COURSE->course.name`&quot;</span>" data = $smarty.capture.t_course_certificates_code image = '32x32/autocomplete.png'  main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}
	{/if}
{elseif $T_OP == 'format_certificate'}
	{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
				{capture name = 't_certificate_code'}
					<div class = "headerTools">
						<span>
							<img src="images/16x16/add.png" title="{$smarty.const._ADDCERTIFICATETEMPLATEXML}" alt="{$smarty.const._ADDCERTIFICATETEMPLATEXML}"/>&nbsp;<a href="{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=add_certificate_template&popup=1" onclick="eF_js_showDivPopup(event, '{$smarty.const._ADDCERTIFICATETEMPLATEXML}', 3)" target="POPUP_FRAME">{$smarty.const._ADDCERTIFICATETEMPLATEXML}</a>&nbsp;
						</span>
						<span>
							&nbsp;<img src="images/16x16/go_into.png" title="{$smarty.const._FORMATCERTIFICATELIVEDOCXMETHOD}" alt="{$smarty.const._FORMATCERTIFICATELIVEDOCXMETHOD}"/>&nbsp;<a href="{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=format_certificate_docx">{$smarty.const._FORMATCERTIFICATELIVEDOCXMETHOD}</a>
						</span>
					</div>

					<fieldset class="fieldsetSeparator">
					<legend>{$smarty.const._FORMATCERTIFICATETEMPLATE}</legend>

					{$T_CERTIFICATE_FORM.javascript}
					<form {$T_CERTIFICATE_FORM.attributes}>
						{$T_CERTIFICATE_FORM.hidden}
						<table class="formElements" style="width:100%;">
							<tr>
								<td class="labelCell">{$T_CERTIFICATE_FORM.existing_certificate.label}:&nbsp;</td>
								<td class="elementCell">{$T_CERTIFICATE_FORM.existing_certificate.html}&nbsp;</td>
							</tr>
							<tr height="7px"></tr>
							<tr>
								<td class="labelCell">{$smarty.const._CERTIFICATEOPERATIONS}:&nbsp;</td>
								<td>
									<a href="javascript:void(0);" onclick="location='{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=course_certificates&export=xml&preview=1&certificate_tpl='+$('select_certificate').value">{$smarty.const._PREVIEW}</a>&nbsp;<div style="display: inline;border-right: 1px solid #333333;"></div>
									<a href="javascript:void(0);" id="edit_certificate_template_href" onclick="setCertificateOperationsHref('{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=edit_certificate_template&popup=1&template_id='); eF_js_showDivPopup(event, '{$smarty.const._EDITCERTIFICATETEMPLATEXML}', 3)" target="POPUP_FRAME" {if $T_CURRENT_TEMPLATE_TYPE == 'main'}style="display:none;"{/if}>{$smarty.const._EDIT}</a><div {if $T_CURRENT_TEMPLATE_TYPE == 'main'}style="display:none; border-right: 1px solid #333333;"{else}style="display: inline; border-right: 1px solid #333333;"{/if} id="edit_certificate_template_separator">&nbsp;</div>
									<a href="javascript:void(0);" id="rename_certificate_template_href" onclick="setCertificateOperationsHref('{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=rename_certificate_template&popup=1&template_id='); eF_js_showDivPopup(event, '{$smarty.const._RENAMECERTIFICATETEMPLATEXML}', 0)" target="POPUP_FRAME" {if $T_CURRENT_TEMPLATE_TYPE == 'main'}style="display:none;"{/if}>{$smarty.const._RENAME}</a><div {if $T_CURRENT_TEMPLATE_TYPE == 'main'}style="display:none; border-right: 1px solid #333333;"{else}style="display: inline; border-right: 1px solid #333333;"{/if} id="rename_certificate_template_separator">&nbsp;</div>
									<a href="javascript:void(0);" id="clone_certificate_template_href" onclick="setCertificateOperationsHref('{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=clone_certificate_template&popup=1&template_id='); eF_js_showDivPopup(event, '{$smarty.const._CLONECERTIFICATETEMPLATEXML}', 0)" target="POPUP_FRAME">{$smarty.const._CLONE}</a><div {if $T_CURRENT_TEMPLATE_TYPE == 'main'}style="display:none; border-right: 1px solid #333333;"{else}style="display: inline; border-right: 1px solid #333333;"{/if} id="clone_certificate_template_separator">&nbsp;</div>
									<a href="javascript:void(0);" id="delete_certificate_template_href" onclick="setCertificateOperationsHref('{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=delete_certificate_template&popup=1&template_id='); return confirm('{$smarty.const._IRREVERSIBLEACTIONAREYOUSURE}')" {if $T_CURRENT_TEMPLATE_TYPE == 'main'}style="display:none;"{/if}>{$smarty.const._DELETE}</a>
								</td>
							</tr>
						{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}	
							<tr>
								<td class="labelCell">{$T_CERTIFICATE_FORM.custom1.label}:&nbsp;</td>
								<td class="elementCell">{$T_CERTIFICATE_FORM.custom1.html}&nbsp;</td>
							</tr>
							<tr>
								<td class="labelCell">{$T_CERTIFICATE_FORM.custom2.label}:&nbsp;</td>
								<td class="elementCell">{$T_CERTIFICATE_FORM.custom2.html}&nbsp;</td>
							</tr>
							<tr>
								<td class="labelCell">{$T_CERTIFICATE_FORM.custom3.label}:&nbsp;</td>
								<td class="elementCell">{$T_CERTIFICATE_FORM.custom3.html}&nbsp;</td>
							</tr>
						{/if} {* #cpp#endif *}	
						</table>
					</fieldset>
					{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
						<fieldset class="fieldsetSeparator" style="margin-top: 5px;">
						<legend>{$smarty.const._SETCERTIFICATEEXPIRATION}</legend>
					{/if} {* #cpp#endif *}
						<table class="formElements" style="width:100%;">
							{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
							<tr>
								<td class="labelCell">{$smarty.const._DURATIONFOREXPIRATION}:&nbsp;</td>
								<td class="elementCell">{$T_CERTIFICATE_FORM.months.html}&nbsp;{$T_CERTIFICATE_FORM.months.label},&nbsp;{$T_CERTIFICATE_FORM.days.html}&nbsp;{$T_CERTIFICATE_FORM.days.label}</td>
							</tr>
							<tr>
								<td></td>
								<td class="infoCell" style="white-space:normal;" colspan="2">{$smarty.const._EXPIREINSTRUCTIONS}</td>
							</tr>
							<tr id="resetRow" style="display:none;">
								<td class="labelCell">{$T_CERTIFICATE_FORM.reset.label}:&nbsp;</td>
								<td class="elementCell">{$T_CERTIFICATE_FORM.reset.html}&nbsp;{$smarty.const._ORRESETCOURSEBEFOREEXPIRE}:&nbsp;{$T_CERTIFICATE_FORM.months_reset.html}&nbsp;{$T_CERTIFICATE_FORM.months_reset.label},&nbsp;{$T_CERTIFICATE_FORM.days_reset.html}&nbsp;{$T_CERTIFICATE_FORM.days_reset.label}</td>
							</tr>

							{/if} {* #cpp#endif *}
							<tr height="7px"></tr>
							<tr>
								<td></td>
								<td colspan="2">{$T_CERTIFICATE_FORM.submit_certificate.html}</td>
							</tr>
						</table>
					{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
						</fieldset>
					{/if} {* #cpp#endif *}
					</form>
				{/capture}
				{eF_template_printBlock title = $smarty.const._FORMATCERTIFICATE data = $smarty.capture.t_certificate_code image = '32x32/certificate.png'  main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}
	{/if} {* #cpp#endif *}
{elseif $T_OP == 'format_certificate_docx'}
	{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
				{capture name = 't_certificate_code'}
					<div class = "headerTools">
						<span>
							<img src="images/16x16/go_into.png" title="{$smarty.const._FORMATCERTIFICATEFPDFMETHOD}" alt="{$smarty.const._FORMATCERTIFICATEFPDFMETHOD}"/>&nbsp;<a href="{$smarty.server.PHP_SELF}?{$T_BASE_URL}&op=format_certificate&switch=1">{$smarty.const._FORMATCERTIFICATEFPDFMETHOD}</a>
						</span>
					</div>

					<fieldset class="fieldsetSeparator">
					<legend>{$smarty.const._FORMATCERTIFICATETEMPLATE}</legend>

					{$T_CERTIFICATE_FORM.javascript}
					<form {$T_CERTIFICATE_FORM.attributes}>
						{$T_CERTIFICATE_FORM.hidden}
						<table class = "formElements" style = "width:100%">
							<tr><td class = "labelCell">{$T_CERTIFICATE_FORM.file_upload.label}:&nbsp;</td>
								<td class = "elementCell" colspan="3">{$T_CERTIFICATE_FORM.file_upload.html}</td></tr>
							<tr><td class = "labelCell">{$T_CERTIFICATE_FORM.existing_certificate.label}:&nbsp;</td>
								<td class = "elementCell" colspan="1">{$T_CERTIFICATE_FORM.existing_certificate.html}&nbsp;</td>
							</tr>
							<tr><td colspan = "1"></td><td class = "infoCell" style = "white-space:normal;" colspan = "3">
								{$smarty.const._CERTIFICATEINSTRUCTIONS}
								</td>
							</tr>
						</table>
					</fieldset>

		{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
					<fieldset class="fieldsetSeparator" style="margin-top: 0px;">
					<legend>{$smarty.const._SETCERTIFICATEEXPIRATION}</legend>
		{/if} {* #cpp#endif *}
						<table class="formElements" style="width:100%;">
		{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
							<tr><td class = "labelCell">{$smarty.const._DURATIONFOREXPIRATION}:&nbsp;</td>
								<td class = "elementCell">{$T_CERTIFICATE_FORM.months.html} {$T_CERTIFICATE_FORM.months.label}, {$T_CERTIFICATE_FORM.days.html} {$T_CERTIFICATE_FORM.days.label}</td></tr>
							<tr><td colspan = "1"></td><td class = "infoCell" style = "white-space:normal;" colspan = "3">
								{$smarty.const._EXPIREINSTRUCTIONS}
								</td>
							</tr>
							<tr id="resetRow" style="display:none"><td class = "labelCell">{$T_CERTIFICATE_FORM.reset.label}:&nbsp;</td>
								<td class = "elementCell">{$T_CERTIFICATE_FORM.reset.html}&nbsp;{$smarty.const._ORRESETCOURSEBEFOREEXPIRE}:&nbsp;{$T_CERTIFICATE_FORM.months_reset.html}&nbsp;{$T_CERTIFICATE_FORM.months_reset.label},&nbsp;{$T_CERTIFICATE_FORM.days_reset.html}&nbsp;{$T_CERTIFICATE_FORM.days_reset.label}</td>
							</tr>
		{/if} {* #cpp#endif *}
							<tr><td></td>
								<td colspan="3">{$T_CERTIFICATE_FORM.preview.html} &nbsp;
												{$T_CERTIFICATE_FORM.submit_certificate.html}
								</td>
							</tr>
						</table>
	{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
					</fieldset>
	{/if} {* #cpp#endif *}
					</form>
				{/capture}
				{eF_template_printBlock title = $smarty.const._FORMATCERTIFICATE data = $smarty.capture.t_certificate_code image = '32x32/certificate.png'  main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}
	{/if} {* #cpp#endif *}
{elseif $T_OP == 'add_certificate_template' || $T_OP == 'edit_certificate_template'}
	{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
				{capture name = 't_add_certificate_template_code'}
					{$T_ADD_CERTIFICATE_TEMPLATE_FORM.javascript}
					<form {$T_ADD_CERTIFICATE_TEMPLATE_FORM.attributes}>
						{$T_ADD_CERTIFICATE_TEMPLATE_FORM.hidden}
						<table class="formElements" style="width:100%;">
							<tr>
								<td class="labelCell">{$T_ADD_CERTIFICATE_TEMPLATE_FORM.certificate_name.label}:&nbsp;</td>
								<td class="elementCell">{$T_ADD_CERTIFICATE_TEMPLATE_FORM.certificate_name.html}&nbsp;</td>
							</tr>
							<tr height="7px"></tr>
							<tr>
								<td class="elementCell" colspan="2">{$T_ADD_CERTIFICATE_TEMPLATE_FORM.certificate_xml.html}</td>
							</tr>
							<tr height="7px"></tr>
							<tr>
								<td class="infoCell" style="white-space:normal;" colspan="2">{$smarty.const._CERTIFICATEINSTRUCTIONSXML}</td>
							</tr>
							<tr height="7px"></tr>
							<tr>
								<td colspan="2">{$T_ADD_CERTIFICATE_TEMPLATE_FORM.preview_certificate_template.html}&nbsp;{$T_ADD_CERTIFICATE_TEMPLATE_FORM.submit_certificate_template.html}</td>
							</tr>
						</table>
					</form>
				{/capture}
{if $T_OP == 'add_certificate_template'}
				{eF_template_printBlock title=$smarty.const._ADDCERTIFICATETEMPLATEXML data=$smarty.capture.t_add_certificate_template_code image='32x32/certificate.png' help='Course_actions'}
{elseif $T_OP == 'edit_certificate_template'}
				{eF_template_printBlock title=$smarty.const._EDITCERTIFICATETEMPLATEXML data=$smarty.capture.t_add_certificate_template_code image='32x32/certificate.png' help='Course_actions'}
{/if}
	{if $T_MESSAGE && $T_MESSAGE_TYPE == 'success'}
	<script>
		re = /\?/;
		!re.test(parent.location) ? parent.location = parent.location+'{$T_ADD_CERTIFICATE_TEMPLATE_REDIRECT}' : parent.location = parent.location+'{$T_ADD_CERTIFICATE_TEMPLATE_REDIRECT}';
	</script>
	{/if}

	{/if} {* #cpp#endif *}
{elseif $T_OP == 'rename_certificate_template'}
	{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
				{capture name = 't_rename_certificate_template_code'}
					{$T_RENAME_CERTIFICATE_TEMPLATE_FORM.javascript}
					<form {$T_RENAME_CERTIFICATE_TEMPLATE_FORM.attributes}>
						{$T_RENAME_CERTIFICATE_TEMPLATE_FORM.hidden}
						<table class="formElements" style="margin-left:50px;">
							<tr>
								<td class="labelCell">{$T_RENAME_CERTIFICATE_TEMPLATE_FORM.certificate_name.label}:&nbsp;</td>
								<td class="elementCell">{$T_RENAME_CERTIFICATE_TEMPLATE_FORM.certificate_name.html}&nbsp;</td>
							</tr>
							<tr height="7px"></tr>
							<tr>
								<td></td>
								<td colspan="2">{$T_RENAME_CERTIFICATE_TEMPLATE_FORM.rename_certificate_template.html}</td>
							</tr>
						</table>
					</form>
				{/capture}
				{eF_template_printBlock title=$smarty.const._RENAMECERTIFICATETEMPLATEXML data=$smarty.capture.t_rename_certificate_template_code image='32x32/certificate.png' help='Course_actions'}
	{if $T_MESSAGE && $T_MESSAGE_TYPE}
	<script>
		re = /\?/;
		!re.test(parent.location) ? parent.location = parent.location+'{$T_RENAME_CERTIFICATE_TEMPLATE_REDIRECT}' : parent.location = parent.location+'{$T_RENAME_CERTIFICATE_TEMPLATE_REDIRECT}';
	</script>
	{/if}

	{/if} {* #cpp#endif *}
{elseif $T_OP == 'clone_certificate_template'}
	{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
				{capture name = 't_clone_certificate_template_code'}
					{$T_CLONE_CERTIFICATE_TEMPLATE_FORM.javascript}
					<form {$T_CLONE_CERTIFICATE_TEMPLATE_FORM.attributes}>
						{$T_CLONE_CERTIFICATE_TEMPLATE_FORM.hidden}
						<table class="formElements" style="margin-left:50px;">
							<tr>
								<td class="labelCell">{$T_CLONE_CERTIFICATE_TEMPLATE_FORM.certificate_name.label}:&nbsp;</td>
								<td class="elementCell">{$T_CLONE_CERTIFICATE_TEMPLATE_FORM.certificate_name.html}&nbsp;</td>
							</tr>
							<tr height="7px"></tr>
							<tr>
								<td></td>
								<td colspan="2">{$T_CLONE_CERTIFICATE_TEMPLATE_FORM.clone_certificate_template.html}</td>
							</tr>
						</table>
					</form>
				{/capture}
				{eF_template_printBlock title=$smarty.const._CLONECERTIFICATETEMPLATEXML data=$smarty.capture.t_clone_certificate_template_code image='32x32/certificate.png' help='Course_actions'}
	{if $T_MESSAGE && $T_MESSAGE_TYPE}
	<script>
		re = /\?/;
		!re.test(parent.location) ? parent.location = parent.location+'{$T_CLONE_CERTIFICATE_TEMPLATE_REDIRECT}' : parent.location = parent.location+'{$T_CLONE_CERTIFICATE_TEMPLATE_REDIRECT}';
	</script>
	{/if}

	{/if} {* #cpp#endif *}
{elseif $T_OP == 'course_rules'}
		<script>var dependson = '&nbsp;{$smarty.const._DEPENDSON}&nbsp;';var generallyavailable = '&nbsp;{$smarty.const._GENERALLYAVAILABLE}&nbsp;';</script>
		{capture name = 't_course_rules_code'}
			<fieldset class = "fieldsetSeparator">
				<legend>{$smarty.const._COURSELESSONSRULES}</legend>
						{$T_COURSE_RULES_FORM.javascript}
						<form {$T_COURSE_RULES_FORM.attributes}>
						{$T_COURSE_RULES_FORM.hidden}
						<table style = "max-width:100%">
				{foreach name = 'rules_list' item = 'item' key = 'key' from = $T_COURSE_LESSONS}
							<tr class = "defaultRowHeight {if !$item.active}deactivatedTableElement{/if}">
								<td id = "first_node_{$item.id}" style = "white-space:nowrap">{$item.name}</td>
								<td id = "label_{$item.id}"	  style = "white-space:nowrap;">&nbsp;{$smarty.const._GENERALLYAVAILABLE}&nbsp;</td>
								<td id = "insert_node_{$item.id}"></td>
								<td id = "last_node_{$item.id}"  style = "white-space:nowrap;text-align:right;vertical-align:bottom">
									&nbsp;<img src = "images/16x16/error_delete.png" title = "{$smarty.const._DELETECONDITION}" alt = "{$smarty.const._DELETECONDITION}" border = "0" id = "delete_icon_{$item.id}" onclick = "eF_js_removeCourseRule({$item.id})" style = "display:none"/>
									{if $T_COURSE_LESSONS|@sizeof > 1}&nbsp;<img src = "images/16x16/add.png"   title = "{$smarty.const._ADDCONDITION}"	alt = "{$smarty.const._ADDCONDITION}"	border = "0" id = "add_icon_{$item.id}" onclick = "eF_js_addCourseRule({$item.id})"/>{/if}
								</td>
							</tr>
				{/foreach}
							<tr><td>&nbsp;</td></tr>
							<tr><td></td><td class = "submitCell">{if $T_COURSE_LESSONS|@sizeof > 1}{$T_COURSE_RULES_FORM.submit_rule.html}{/if}</td></tr>
						</table>
						</form>

						{*Auxilliary select element, used below in building conditions*}
						<select name = "condition" id = "conditions" style = "display:none;margin-left:5px;vertical-align:middle">
							<option value = "and">{$smarty.const._AND}</option>
							<option value = "or">{$smarty.const._OR}</option>
						</select>

						<script type = "text/javascript">
							var lessonsIds   = new Array();
							var lessonsNames = new Array();
							var calls = new Array();
						{foreach name = 'lessons_list' item = 'lesson' key = 'key' from = $T_COURSE_LESSONS}	{*Create javascript arrays*}
							lessonsIds.push('{$lesson.id}');
							lessonsNames.push('{$lesson.name|@addSlashes}');
						{/foreach}
						{foreach name = 'course_rules_list' item = "rule" key = "key" from = $T_COURSE_RULES}
							{foreach name = 'lesson_rules' item = "lesson_id" key = "index" from = $rule.lesson}
								{if !$rule.condition.$index || $rule.condition.$index == 'and'}{assign var = 'condition' value = 0}{else}{assign var = 'condition' value = 1}{/if}
								calls.push(new Array({$key}, {$lesson_id}, {$condition}));
							{/foreach}
						{/foreach}
						</script>
					</fieldset>
{if ($smarty.const.G_VERSIONTYPE != 'community')} {* #cpp#ifndef COMMUNITY *}
	{if ($smarty.const.G_VERSIONTYPE != 'standard')} {* #cpp#ifndef STANDARD *}
					<script>translations['_COURSEDOESNOTEXIST'] = '{$smarty.const._COURSEDOESNOTEXIST}';</script>					
					<fieldset class = "fieldsetSeparator">
						<legend>{$smarty.const._RULESBETWEENCOURSES}</legend>
						<table class = "statisticsSelectList">
							<tr><td class = "labelCell">{$smarty.const._COURSEDEPENDSON}:</td>
								<td class = "elementCell">
									<input type = "text" id = "autocomplete" class = "autoCompleteTextBox" value = "{$T_DEPENDSON_COURSE}" onclick = "this.value='';$('autocomplete_course_hidden').value='';"/>
									<img id = "busy" src = "images/16x16/clock.png" style = "display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
									<div id = "autocomplete_courses" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
									<input type = "hidden" id = "autocomplete_course_hidden" value = "{$T_CURRENT_COURSE->course.depends_on}"/>
								</td>
							</tr>
							<tr><td></td>
								<td class = "infoCell">{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td>
							</tr>
						{if !$T_CURRENT_USER->coreAccess.course_settings || $T_CURRENT_USER->coreAccess.course_settings == 'change'}	
							<tr><td></td>
								<td class = "submitCell"><input type = "submit" value = "{$smarty.const._SUBMIT}" class = "flatButton" onclick = "setInterCourseRules(this);"/></td>
							</tr>
						{/if}
						</table>
					</fieldset>					
	{/if} {* #cpp#endif *}
{/if} {* #cpp#endif *}						
				{/capture}
				{eF_template_printBlock title = $smarty.const._COURSERULES data = $smarty.capture.t_course_rules_code image = '32x32/rules.png'  main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}
{elseif $T_OP == 'course_order'}
	{assign var = "title" value = $title|cat:'&nbsp;&raquo;&nbsp;<a class = "titleLink" href = "'|cat:$smarty.server.PHP_SELF|cat:'?ctg=courses&course='|cat:$smarty.get.course|cat:'&op=course_order">'|cat:$smarty.const._ORDERFORCOURSE|cat:' &quot;'|cat:$T_CURRENT_COURSE->course.name|cat:'&quot;</a>'}
		{capture name = 't_course_rules_code'}
			<fieldset class = "fieldsetSeparator">
				<legend>{$smarty.const._DRAGITEMSTOCHANGELESSONSORDER}</legend>
				<ul id = "dhtmlgoodies_lessons_tree" class = "dhtmlgoodies_tree">
				{foreach name = 'lessons_list' key = 'key' item = 'lesson'  from = $T_COURSE_LESSONS}
					<li id = "dragtree_{$lesson.id}" noChildren = "true">
						<a class = "{if !$lesson.active}deactivatedLinkElement{/if}" href = "javascript:void(0)">&nbsp;{$lesson.name|eF_truncate:100}</a>
					</li>
				{/foreach}
				</ul>
			</fieldset>
			<br/>
		{if !$T_CURRENT_USER->coreAccess.course_settings || $T_CURRENT_USER->coreAccess.course_settings == 'change'}
			<input id = "save_button" class = "flatButton" type="button" onclick="saveQuestionTree(this)" value="{$smarty.const._SAVECHANGES}">
		{/if}
		{/capture}
		{eF_template_printBlock title = $smarty.const._COURSEORDER data = $smarty.capture.t_course_rules_code image = '32x32/order.png'  main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}

{elseif $T_OP == 'course_scheduling'}
		<script>var noscheduleset = '{$smarty.const._NOSCHEDULESET}';
				var switchcalendarmode = '{$smarty.const._SWITCHCALENDARMODE}';
				var switchperiodmode = '{$smarty.const._SWITCHPERIODMODE}';
		</script>

		{capture name = 't_course_scheduling_code'}
			<div class = "headerTools">
			{if (!$T_CURRENT_USER->coreAccess.calendar == 'change' || $T_CURRENT_USER->coreAccess.calendar == 'change') &&  (!$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change') }
				<span>
					<img src = "images/16x16/add.png" title = "{$smarty.const._ADDCALENDAR}" alt = "{$smarty.const._ADDCALENDAR}"/>
					<a href = "{$smarty.server.PHP_SELF}?ctg=calendar&add=1&course={$smarty.get.course}&popup=1" onclick = "eF_js_showDivPopup(event, '{$smarty.const._ADDCALENDAR}', 2)" target = "POPUP_FRAME">{$smarty.const._ADDCALENDAR}</a>
				</span>
			</div>
			{/if}
			<fieldset class = "fieldsetSeparator">
			<legend>{$smarty.const._COURSESCHEDULE}</legend>
			<table>
				<tr><td>{$smarty.const._COURSESCHEDULE}:&nbsp;</td>
					<td>
						<span id = "schedule_dates_0">
						{if $T_CURRENT_COURSE->course.start_date}
							{$smarty.const._FROM} #filter:timestamp_time_nosec-{$T_CURRENT_COURSE->course.start_date}#
							{$smarty.const._TO} #filter:timestamp_time_nosec-{$T_CURRENT_COURSE->course.end_date}#
							{assign var = "start_date" value = $T_CURRENT_COURSE->course.start_date}
							{assign var = "end_date" value = $T_CURRENT_COURSE->course.end_date}
						{else}
							<span class = "emptyCategory">{$smarty.const._NOSCHEDULESET}</span>
							{assign var = "start_date" value = time()}
							{assign var = "end_date" value = time()}
						{/if}&nbsp;
						</span>
						<table id = "schedule_dates_form_0" style = "display:none">
							<tr><td>{$smarty.const._FROM}&nbsp;</td><td>{eF_template_html_select_date prefix="from_" time=$start_date start_year="-5" end_year="+5" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="from_" time = $start_date display_seconds = false}&nbsp;</td></tr>
							<tr><td>{$smarty.const._TO}&nbsp;</td><td>{eF_template_html_select_date prefix="to_"	 time=$end_date   start_year="-5" end_year="+5" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="to_"   time = $end_date   display_seconds = false}&nbsp;</td></tr>
						</table>
					</td>
					<td>
				{if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'}
						<span id = "add_schedule_link_0">
							<img src = "images/16x16/{if $T_CURRENT_COURSE->course.start_date}edit.png{else}add.png{/if}" alt = "{$smarty.const._ADDSCHEDULE}" title = "{$smarty.const._ADDSCHEDULE}" class = "handle" onclick = "showEdit(0)"/>
							<img src = "images/16x16/error_delete.png" id = "remove_schedule_link_0" alt = "{$smarty.const._DELETESCHEDULE}" title = "{$smarty.const._DELETESCHEDULE}" class = "handle" onclick = "deleteSchedule(this, 0)" {if !$T_CURRENT_COURSE->course.start_date}style = "display:none"{/if}/>
						</span>&nbsp;
						<img src = "images/16x16/success.png" alt = "{$smarty.const._SAVE}" title = "{$smarty.const._SAVE}" class = "ajaxHandle" id = "set_schedules_link_0" style = "display:none" onclick = "setSchedule(this, 0)"/>&nbsp;
						<img src = "images/16x16/error_delete.png" alt = "{$smarty.const._CANCEL}" title = "{$smarty.const._CANCEL}" class = "ajaxHandle" id = "remove_schedule_link_0" style = "display:none" onclick = "hideEdit(0)" />
				{/if}
					</td></tr>
			</table>
			</fieldset>
			<fieldset class = "fieldsetSeparator">
			<legend>{$smarty.const._LESSONSCHEDULE}</legend>
			<table>
			{foreach name = 'lessons_list' key = "id" item = "lesson" from = $T_COURSE_LESSONS}
				<tr {if !$lesson.active}class = "deactivatedTableElement"{/if}><td>{$lesson.name}:&nbsp;</td>
					<td>
						<span id = "schedule_dates_{$id}">
						{if $lesson.start_date}
							{$smarty.const._FROM} #filter:timestamp_time_nosec-{$lesson.start_date}#
							{$smarty.const._TO} #filter:timestamp_time_nosec-{$lesson.end_date}#
							{assign var = "start_date" value = $lesson.start_date}
							{assign var = "end_date" value = $lesson.end_date}						
						{elseif $lesson.start_period}
							<span>{$smarty.const._FROM}&nbsp;{$lesson.start_period}&nbsp;{$smarty.const._DAYSAFTERCOURSEENROLLMENT}&nbsp;{$smarty.const._AND}&nbsp;{$smarty.const._FOR}&nbsp;{$lesson.end_period}&nbsp;{$smarty.const._DAYSCONDITIONALLOWERCASE}</span>
						{else}
							<span class = "emptyCategory">{$smarty.const._NOSCHEDULESET}</span>
							{assign var = "start_date" value = time()}
							{assign var = "end_date" value = time()}
						{/if}&nbsp;
						</span>
						<table id = "schedule_dates_form_{$id}" style = "display:none">
							<tr><td>{$smarty.const._FROM}&nbsp;</td><td>{eF_template_html_select_date prefix="from_" time=$start_date start_year="-5" end_year="+5" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="from_" time = $start_date display_seconds = false}&nbsp;</td></tr>
							<tr><td>{$smarty.const._TO}&nbsp;</td><td>{eF_template_html_select_date prefix="to_"   time=$end_date   start_year="-5" end_year="+5" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="to_"   time = $end_date   display_seconds = false}&nbsp;</td></tr>
						</table>
						<table id = "deadline_dates_form_{$id}" style = "display:none">
							<tr><td>{$smarty.const._FROM}&nbsp;
							
								<select name="start_period" id = "start_period_{$id}">
										{foreach name = 'roles_list' key = 'key' item = 'item' from = $T_DAYS_AFTER_ENROLLMENT}
														<option value="{$key}" {if ($lesson.start_period == $key)}selected{/if}>{$item}</option>
											{/foreach}
								</select>{$smarty.const._DAYSAFTERCOURSEENROLLMENT}</td>
							<td>&nbsp;{$smarty.const._AND}&nbsp;{$smarty.const._FOR}&nbsp;
							
							<select name="stop_period" id = "stop_period_{$id}">
										{foreach name = 'roles_list' key = 'key' item = 'item' from = $T_DAYS_AFTER_ENROLLMENT}
														<option value="{$key}" {if ($lesson.end_period == $key)}selected{/if}>{$item}</option>
											{/foreach}
								</select>&nbsp;{$smarty.const._DAYS}</td></tr>
						</table>
						
					</td>
					<td>
					{if !$T_CURRENT_USER->coreAccess.course_settings == 'change' || $T_CURRENT_USER->coreAccess.course_settings == 'change'}
						<span id = "add_schedule_link_{$id}">
							<img src = "images/16x16/{if $lesson.start_date || $lesson.start_period}edit.png{else}add.png{/if}" alt = "{$smarty.const._ADDSCHEDULE}" title = "{$smarty.const._ADDSCHEDULE}" class = "handle" onclick = "showEdit({$id})"/>
							<img src = "images/16x16/error_delete.png" alt = "{$smarty.const._DELETESCHEDULE}" title = "{$smarty.const._DELETESCHEDULE}" class = "handle" onclick = "deleteSchedule(this, {$id})" {if !$lesson.start_date && !$lesson.start_period}style = "display:none"{/if}/>
						</span>&nbsp;
						

						<img  src = "images/16x16/success.png" alt = "{$smarty.const._SAVE}" title = "{$smarty.const._SAVE}" class = "ajaxHandle" id = "set_schedules_link_{$id}" style = "display:none" onclick = "setSchedule(this, {$id})"/>
						<img id = "period_submit_{$id}" style="display:none" src = "images/16x16/success.png" alt = "{$smarty.const._SAVE}" title = "{$smarty.const._SAVE}" class = "ajaxHandle"  style = "display:none" onclick = "setPeriod(this, {$id})"/>
						
						
						<img src = "images/16x16/error_delete.png" alt = "{$smarty.const._CANCEL}" title = "{$smarty.const._CANCEL}" class = "ajaxHandle" id = "remove_schedule_link_{$id}" style = "display:none" onclick = "hideEdit({$id})" />
						
						<img src = "images/16x16/calendar.png" alt = "{$smarty.const._SWITCHCALENDARMODE}" title = "{$smarty.const._SWITCHCALENDARMODE}" class = "ajaxHandle" style = "display:none" id="toggle_way_{$id}"  onclick = "switchWay({$id})"/>&nbsp;
						
					{/if}
					</td></tr>
			{/foreach}
			</table>
			</fieldset>
		{/capture}
		{eF_template_printBlock title = $smarty.const._COURSESCHEDULE data = $smarty.capture.t_course_scheduling_code image = '32x32/calendar.png'  main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}
{elseif $T_OP == 'export_course'}
	{capture name = 't_export_course_code'}
		<fieldset class = "fieldsetSeparator">
		<legend>{$smarty.const._EXPORTCOURSE}</legend>
		{$T_EXPORT_COURSE_FORM.javascript}
		<form {$T_EXPORT_COURSE_FORM.attributes}>
			{$T_EXPORT_COURSE_FORM.hidden}
			<table class = "formElements" style = "margin-left:0px">										{if $T_NEW_EXPORTED_FILE}
				<tr><td colspan = "2">{$smarty.const._DOWNLOADEXPORTEDCOURSE}:&nbsp; <a href = "view_file.php?file={$T_NEW_EXPORTED_FILE.id}&action=download">{$T_NEW_EXPORTED_FILE.name}</a> ({$T_NEW_EXPORTED_FILE.size} {$smarty.const.KB}, #filter:timestamp-{$T_NEW_EXPORTED_FILE.timestamp}#)</td></tr>
		{elseif $T_EXPORTED_FILE}
				<tr><td colspan = "2">{$smarty.const._EXISTINGFILE}:&nbsp;<a href = "view_file.php?file={$T_EXPORTED_FILE.id}&action=download">{$T_EXPORTED_FILE.name}</a> ({$T_EXPORTED_FILE.size} {$smarty.const.KB}, #filter:timestamp-{$T_EXPORTED_FILE.timestamp}#)</td></tr>
		{/if}
				<tr><td class = "labelCell">{$smarty.const._CLICKTOEXPORTCOURSE}:&nbsp;</td>
					<td class = "elementCell">{$T_EXPORT_COURSE_FORM.submit_export_course.html}</td></tr>
			</table>
		</form>
		</fieldset>
	{/capture}
	{eF_template_printBlock title = "`$smarty.const._EXPORTCOURSE`<span class = 'innerTableName'>&nbsp;&quot;`$T_CURRENT_COURSE->course.name`&quot;</span>" data = $smarty.capture.t_export_course_code image = '32x32/export.png' main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}
{elseif $T_OP == 'import_course'}
		{capture name = 't_import_course_code'}
			<fieldset class = "fieldsetSeparator">
			<legend>{$smarty.const._IMPORTCOURSE}</legend>
			{$T_IMPORT_COURSE_FORM.javascript}
			<form {$T_IMPORT_COURSE_FORM.attributes}>
				{$T_IMPORT_COURSE_FORM.hidden}
				<table class = "formElements">
					<tr><td colspan = "2">{$smarty.const._COURSEIMPORTNOTICE}</td></tr>
					<tr><td class = "labelCell">{$smarty.const._COURSEDATAFILE}:&nbsp;</td>
						<td>{$T_IMPORT_COURSE_FORM.file_upload.html}</td></tr>
					<tr><td></td><td class = "infoCell">{$smarty.const._EACHFILESIZEMUSTBESMALLERTHAN} <b>{$T_MAX_FILESIZE}</b> {$smarty.const._KB}</td></tr>
					<tr><td colspan = "100%">&nbsp;</td></tr>
					<tr><td></td><td>{$T_IMPORT_COURSE_FORM.submit_import_course.html}</td></tr>
				</table>
			</form>
			</fieldset>
		{/capture}
		{eF_template_printBlock title = $smarty.const._IMPORTCOURSE data = $smarty.capture.t_import_course_code image = '32x32/import.png'  main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS help='Course_actions'}
{elseif $T_MODULE_TABPAGE}
        {capture name = "module_tab_page"}
            {include file = $T_MODULE_TABPAGE.file}
        {/capture}
		{eF_template_printBlock title = $T_MODULE_TABPAGE.title data = $smarty.capture.module_tab_page image = $T_MODULE_TABPAGE.image main_options = $T_TABLE_OPTIONS options = $T_COURSE_OPTIONS1}
{/if}

