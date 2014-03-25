{if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
	{assign var = "_change_" value = 1}
{/if}

	{*moduleReports: Show employees satisfying some criteria *}
		<script>
		var searchSkillTemplate = '{$T_REPORT_FORM.search_skill_template.html|replace:"\n":""}';
		var youShouldEitherProvideExistingOrNewGroup = '{$smarty.const._YOUSHOULDPROVIDEEXISTINGORNEWGROUP}';
		</script>

	   {capture name = 't_reports_code'}
			<table class = "formElements">
			<tr><td>{$T_REPORT_FORM.criteria.all_criteria.html}   </td><td>{$smarty.const._SATISFYALLCRITERIA}</td></tr>
			<tr><td>{$T_REPORT_FORM.criteria.any_criteria.html}   </td><td>{$smarty.const._SATISFYANYCRITERIA}</td></tr>
				{if isset($smarty.get.all) && $smarty.get.all=="false" }
					{literal}
					<script>document.getElementById('any_criteria').checked = true;</script>
					{/literal}
				{else}
					{literal}
					<script>document.getElementById('all_criteria').checked = true;</script>
					{/literal}
				{/if}
			<tr><td>&nbsp;</td></tr>
			<table>
			<br>

			<table><!--style="visibility:hidden"-->
			<tr><td>{$T_REPORT_FORM.search_branch.label}:&nbsp;</td><td>{$T_REPORT_FORM.search_branch.html}</td><td>{$T_REPORT_FORM.include_subbranches.html}</td><td id="include_subbranches_label">({$T_REPORT_FORM.include_subbranches.label})</td></tr>
				{if isset($smarty.get.branch_ID) && $smarty.get.branch_ID != 0}
					{literal}
					<script>
					var branch_select = document.getElementById('search_branch');
					for (i = 0; i < branch_select.options.length; i++) {
						if (branch_select.options[i].value == '{/literal}{$smarty.get.branch_ID}{literal}') {
							 branch_select.options[i].selected = true;
							 break;
						}
					}
					</script>
					{/literal}
				{/if}
			<tr><td>{$T_REPORT_FORM.search_job_description.label}:&nbsp;</td><td>{$T_REPORT_FORM.search_job_description.html}</td></tr>
				{if isset($smarty.get.job_description_ID) && $smarty.get.job_description_ID != ""}
					{literal}
					<script>
					var a;
					var job_description_select = document.getElementById('search_job_description');
					for (i = 0; i < job_description_select.options.length; i++) {
						a = new String("{/literal}{$smarty.get.job_description_ID}{literal}");
						if (job_description_select.options[i].value.toString() == a) {
							 job_description_select.options[i].selected = true;
						} else if (job_description_select.options[i].value == "__emptybranch_name" || job_description_select.options[i].value == "__emptyother_branch") {
							job_description_select.options[i].disabled = true;
						}
					}
					</script>
					{/literal}
				{/if}

			<tr><td>{$T_REPORT_FORM.search_skill.label}:&nbsp;</td><td>{$T_REPORT_FORM.search_skill.html}</td>
				<td><a href="javascript:void(0);" onclick="add_new_criterium_row()"><img src="images/16x16/add.png" title="{$smarty.const._NEWSEARCHCRITERIUM}" alt="{$smarty.const._NEWSEARCHCRITERIUM}" border="0"/></a></td></tr>
				{if isset($smarty.get.skill_ID) && $smarty.get.skill_ID != 0}
					{literal}
					<script>
					var skill_select = document.getElementById('search_skill');
					for (i = 0; i < skill_select.options.length; i++) {
						if (skill_select.options[i].value == '{/literal}{$smarty.get.skill_ID}{literal}') {
							 skill_select.options[i].selected = true;
							 break;
						}
					}
					</script>
					{/literal}
				{/if}
			<tr><td></td><td colspan=2><table border = "0" id="criteriaTable"></table></td></tr>

			<tr><td>&nbsp;</td></tr>
			</table>
	   {/capture}

	   {* Form with all advanced search criteria *}
	   {capture name = 't_reports_advanced_search'}
			<table class = "formElements">
				<tr><td width = "33%">
					{if $smarty.const.G_VERSIONTYPE == 'educational'} {* #cpp#ifdef EDUCATIONAL *}
					<tr><td class = "labelCell">{$T_REPORT_FORM.criteria.all_criteria.html}   </td><td>{$smarty.const._SATISFYALLCRITERIA}</td></tr>
					<tr><td class = "labelCell">{$T_REPORT_FORM.criteria.any_criteria.html}   </td><td>{$smarty.const._SATISFYANYCRITERIA}</td></tr>
						{if isset($smarty.get.all) && $smarty.get.all=="false" }
							{literal}
							<script>document.getElementById('any_criteria').checked = true;</script>
							{/literal}
						{else}
							{literal}
							<script>document.getElementById('all_criteria').checked = true;</script>
							{/literal}
						{/if}
					<tr><td>&nbsp;</td></tr>
					{else} {* #cpp#else *}
					<table>
					{/if} {* #cpp#endif *}
						<tr><td class = "labelCell">{$T_REPORT_FORM.new_login.label}:&nbsp;</td><td>{$T_REPORT_FORM.new_login.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.name.label}:&nbsp;</td><td>{$T_REPORT_FORM.name.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.surname.label}:&nbsp;</td><td>{$T_REPORT_FORM.surname.html}</td></tr>

						{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
							<tr><td class = "labelCell">{$T_REPORT_FORM.father.label}:&nbsp;</td><td>{$T_REPORT_FORM.father.html}</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.sex.label}:&nbsp;</td><td>{$T_REPORT_FORM.sex.html}</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.marital_status.label}:&nbsp;</td><td>{$T_REPORT_FORM.marital_status.html}</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.birthday.label}:&nbsp;</td><td>{$T_REPORT_FORM.birthday.html}</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.birthplace.label}:&nbsp;</td><td>{$T_REPORT_FORM.birthplace.html}</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.birthcountry.label}:&nbsp;</td><td>{$T_REPORT_FORM.birthcountry.html}</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.mother_tongue.label}:&nbsp;</td><td>{$T_REPORT_FORM.mother_tongue.html}</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.nationality.label}:&nbsp;</td><td>{$T_REPORT_FORM.nationality.html}</td></tr>
							<tr><td colspan=2>&nbsp;</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.office.label}:&nbsp;</td><td>{$T_REPORT_FORM.office.html}</td></tr>
							<tr><td class = "labelCell">{$T_REPORT_FORM.company_internal_phone.label}:&nbsp;</td><td>{$T_REPORT_FORM.company_internal_phone.html}</td></tr>
						{/if} {* #cpp#endif *}

						<tr><td class = "labelCell">{$T_REPORT_FORM.email.label}:&nbsp;</td><td>{$T_REPORT_FORM.email.html}</td></tr>

						<tr><td class = "labelCell">{$T_REPORT_FORM.user_type.label}:&nbsp;</td><td>{$T_REPORT_FORM.user_type.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.active.label}:&nbsp;</td><td>{$T_REPORT_FORM.active.html}</td></tr>

						<script>
						// Comma separated fields that required special handling by Javascript search
						var customProfileSearchCriteria = "{$T_USER_PROFILE_FIELDS_CRITERIA}";
						var datesSearchCriteria = "{$T_DATES_SEARCH_CRITERIA}";
						</script>
						{foreach name = 'profile_fields' key = key item = item from = $T_USER_PROFILE_FIELDS }
							<tr><td class = "labelCell">{$T_REPORT_FORM.$item.label}:&nbsp;</td>
								<td class = "elementCell">{$T_REPORT_FORM.$item.html}</td></tr>
							{if $T_REPORT_FORM.$item.error}<tr><td></td><td class = "formError">{$T_REPORT_FORM.$item.error}</td></tr>{/if}

						{/foreach}
						{foreach name = 'profile_fields' key = key item = item from = $T_USER_PROFILE_DATES }
							<tr><td class = "labelCell">{$item.name}:&nbsp;</td>
								<td class = "elementCell" NOWRAP>{eF_template_html_select_date searchtype=1 prefix=$item.prefix emptyvalues="1" time=0 start_year="-45" end_year="+10" field_order = $T_DATE_FORMATGENERAL onChange="javascript:onDateUpdated(this)"}</td></tr>
						{/foreach}

						<tr><td class = "labelCell">{$T_REPORT_FORM.registration.label}:&nbsp;</td><td NOWRAP>{eF_template_html_select_date searchtype=1 prefix="timestamp" emptyvalues="1" time=0 start_year="-45" field_order = $T_DATE_FORMATGENERAL onChange="javascript:onDateUpdated(this)"}</td></tr>

					{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
					</table>
					</td>
					<td width = "15%">
					&nbsp;
					</td>
					<td width = "*">
					<table>
						<tr><td colspan=2>&nbsp;</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.address.label}:&nbsp;</td><td>{$T_REPORT_FORM.address.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.city.label}:&nbsp;</td><td>{$T_REPORT_FORM.city.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.country.label}:&nbsp;</td><td>{$T_REPORT_FORM.country.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.homephone.label}:&nbsp;</td><td>{$T_REPORT_FORM.homephone.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.mobilephone.label}:&nbsp;</td><td>{$T_REPORT_FORM.mobilephone.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.hired_on.label}:&nbsp;</td><td NOWRAP>{eF_template_html_select_date searchtype=1 prefix="hired_on" emptyvalues="1" time="" start_year="-45" field_order = $T_DATE_FORMATGENERAL onChange="javascript:onDateUpdated(this)"}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.left_on.label}:&nbsp;</td><td NOWRAP>{eF_template_html_select_date searchtype=1 prefix="left_on" emptyvalues="1" time="" start_year="-45" field_order = $T_DATE_FORMATGENERAL onChange="javascript:onDateUpdated(this)"}</td></tr>
						<tr><td colspan=2>&nbsp;</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.employement_type.label}:&nbsp;</td><td>{$T_REPORT_FORM.employement_type.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.way_of_working.label}:&nbsp;</td><td>{$T_REPORT_FORM.way_of_working.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.work_permission_data.label}:&nbsp;</td><td>{$T_REPORT_FORM.work_permission_data.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.police_id_number.label}:&nbsp;</td><td>{$T_REPORT_FORM.police_id_number.html}</td></tr>
						<tr><td colspan=2>&nbsp;</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.afm.label}:&nbsp;</td><td>{$T_REPORT_FORM.afm.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.doy.label}:&nbsp;</td><td>{$T_REPORT_FORM.doy.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.wage.label}:&nbsp;</td><td>{$T_REPORT_FORM.wage.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.bank.label}:&nbsp;</td><td>{$T_REPORT_FORM.bank.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.bank_account.label}:&nbsp;</td><td>{$T_REPORT_FORM.bank_account.html}</td></tr>
						<tr><td colspan=2>&nbsp;</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.driving_licence.label}:&nbsp;</td><td>{$T_REPORT_FORM.driving_licence.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.national_service_completed.label}:&nbsp;</td><td>{$T_REPORT_FORM.national_service_completed.html}</td></tr>
						<tr><td class = "labelCell">{$T_REPORT_FORM.transport.label}:&nbsp;</td><td>{$T_REPORT_FORM.transport.html}</td></tr>
					</table>
					{/if} {* #cpp#endif *}
					</td>
				</tr>
			</table> {* And of main table of class = formelements *}

			{* Print the new centrally aligned submit button - This table is closed </table> by the closing tab of the main table of the eFront normal interface *}
			<table width ="66%">
				<tr><td>&nbsp;</td></tr>
				<tr><td class = "submitCell" style = "text-align:center" align="center">{$T_REPORT_FORM.submit_personal_details.html}</td></tr>
		   </table>
	   {/capture}


		{*moduleShowemployees: Show employees*}
		{capture name = 't_employees_code'}
{*222222222222*}
		<div class = "headerTools" id = "groupUsersMsgId">
            <span>
                <img src = "images/16x16/add.png" title = "{$smarty.const._SETFOUNDEMPLOYEESINTOGROUP}" alt = "{$smarty.const._SETFOUNDEMPLOYEESINTOGROUP}">
                <a href = "javascript:void(0)" onClick = "eF_js_showDivPopup(event, '{$smarty.const._SETFOUNDEMPLOYEESINTOGROUP}', 0, 'insert_into_group')" target = 'POPUP_FRAME'>{$smarty.const._SETFOUNDEMPLOYEESINTOGROUP}</a>
            </span>
        </div>

{if !$T_SORTED_TABLE || $T_SORTED_TABLE == 'foundEmployees'}
<!--ajax:foundEmployees-->
		<table style = "width:100%" class = "sortedTable" size = "{$T_TABLE_SIZE}" sortBy = "0" id = "foundEmployees" useAjax = "1" rowsPerPage = "{$smarty.const.G_DEFAULT_TABLE_SIZE}" url = "{$smarty.session.s_type}.php?ctg=search_users&">
			<tr class = "topTitle">
				<td class = "topTitle" name="login">{$smarty.const._USER}</td>
				<td class = "topTitle" name="languages_NAME">{$smarty.const._LANGUAGE}</td>
				{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
					<td class = "topTitle" align="center" name="jobs_num">{$smarty.const._JOBSASSIGNED}</td>
				{/if}  {* #cpp#endif *}
				{if 'messages'|eF_template_isOptionVisible}
				<td class = "topTitle noSort" align="center">{$smarty.const._SENDMESSAGE}</td>
				{/if}
				<td class = "topTitle noSort" align="center">{$smarty.const._STATISTICS}</td>
				<td class = "topTitle noSort" align="center">{$smarty.const._OPERATIONS}</td>
			</tr>

	   {if $T_TABLE_SIZE > 0}
			{foreach name = 'users_list' key = 'key' item = 'user' from = $T_DATA_SOURCE}
			<tr class = "{cycle values = "oddRowColor, evenRowColor"} {if !$user.active}deactivatedTableElement{/if}">
			<td>
				{if ($user.pending == 1)}
				<a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink" style="color:red;">#filter:login-{$user.login}#</a>
				{elseif ($user.active == 1)}
				<a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink">#filter:login-{$user.login}#</a>
				{else}
				#filter:login-{$user.login}#
				{/if}
			</td>
			<td>{$user.languages_NAME}</td>

			{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
			{if $user.jobs}
				<td align="center"><a href="#" class = "info nonEmptyLesson" id="jobsDetails_{$user.login}" onmouseover="$('tooltipImg_{$user.login}').style.visibility = 'visible';" onmouseout="$('tooltipImg_{$user.login}').style.visibility = 'hidden';">{$user.jobs_num}<span class = 'tooltipSpan' id='userInfo_{$user.login}' style="font-size: 10px" >
				{foreach name = 'jobs_list' item = 'job' from = $user.jobs}
				{$job.description}&nbsp;{$smarty.const._ATBRANCH}&nbsp;{$job.name}<br>
				{/foreach}
				</span></a>
				</td>

				{literal}
				<script>
				user_login = '{/literal}{$user.login}{literal}';
				div_half_size = '{/literal}{$user.div_size}{literal}';
				$('userInfo_' + user_login).setStyle({left: -(div_half_size) + "px"});
				$('userInfo_' + user_login).setStyle({{/literal}{if $T_BROWSER == 'IE6'}width{else}minWidth{/if}{literal}: (2*div_half_size) + "px"});
				</script>
				{/literal}
			{else}
				<td align="center">{$user.jobs_num}</td>
			{/if}
			{/if} {* #cpp#endif *}
			{if 'messages'|eF_template_isOptionVisible}
			<td align="center"><a style="" href="{$smarty.server.PHP_SELF}?ctg=messages&add=1&recipient={$user.login}&popup=1" onclick='eF_js_showDivPopup(event, "{$smarty.const._SENDMESSAGE}", 2)' target="POPUP_FRAME"><img src="images/16x16/mail.png" border="0"></a></td>
			{/if}
			<td align="center"><a href="{$smarty.session.s_type}.php?ctg=statistics&option=user&sel_user={$user.login}"><img border = "0" src = "images/16x16/reports.png" title = "{$smarty.const._STATISTICS}" alt = "{$smarty.const._STATISTICS}" /></a></td>
			<td class="centerAlign">
				{if $_change_}
					<a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink"><img border = "0" src = "images/16x16/edit.png" title = "{$smarty.const._EDIT}" alt = "{$smarty.const._EDIT}" /></a>
                                                {if $smarty.session.s_login != $user.login}
                                                    {if ($smarty.const.G_VERSIONTYPE != 'community')} {* #cpp#ifndef COMMUNITY *}
                                                    	{if ($smarty.const.G_VERSIONTYPE != 'standard')} {* #cpp#ifndef STANDARD *}
                                                    	<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._ARCHIVEENTITY}" alt = "{$smarty.const._ARCHIVEENTITY}" onclick = "archiveUser(this, '{$user.login}')"/>
	                                                    {/if} {* #cpp#endif *}
                                                    {/if} {* #cpp#endif *}
                                                    {if ($smarty.const.G_VERSIONTYPE != 'enterprise')} {* #cpp#ifndef ENTERPRISE *}
                                                    	{if ($smarty.const.G_VERSIONTYPE != 'educational')} {* #cpp#ifndef EDUCATIONAL *}
                                                    	<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._DELETE}" alt = "{$smarty.const._DELETE}" onclick = "if (confirm('{$smarty.const._AREYOUSUREYOUWANTTODELETEUSER}')) deleteUser(this, '{$user.login}')"/>
	                                                    {/if} {* #cpp#endif *}
                                                    {/if} {* #cpp#endif *}
                                                {else}
                                                    <img class = "ajaxHandle inactiveImage" src = "images/16x16/error_delete.png" title = "{$smarty.const._DELETE}" alt = "{$smarty.const._DELETE}" />
                                                {/if}
				{/if}
			</td>


			</tr>
			{/foreach}

				 <tr style="display:none"><td><input type="hidden" id="usersFound" value="{$T_SENDALLMAIL_URL}" /></td></tr>
{*
				 {if $smarty.const.MSIE_BROWSER == 1}
					 <img style="display:none" src="images/16x16/question_type_free_text.png" onLoad="javascript:new Effect.Appear('sendToAllId');" />
				 {else}
					 <script>
					 new Effect.Appear('groupUsersId');
					 new Effect.Appear('groupUsersMsgId');
					 new Effect.Appear('sendToAllId');
					 </script>
				 {/if}
*}
		{else}
				 <tr><td colspan ="8" class = "emptyCategory">{$smarty.const._NOEMPLOYEESFULFILLTHESPECIFIEDCRITERIA}</td></tr>

				 <tr style="display:none"><td><input type="hidden" id="usersFound" value="{$T_SENDALLMAIL_URL}" /></td></tr>
{*
				 {if $smarty.const.MSIE_BROWSER == 1}
					<img style="display:none" src="images/16x16/question_type_free_text.png" onload="if (document.getElementById('sendToAllId')) {ldelim}document.getElementById('sendToAllId').style.display='none';{rdelim}" />
				 {else}
					 <script>
					 document.getElementById('groupUsersId').style.display = 'none';
					 document.getElementById('groupUsersMsgId').style.display = 'none';
					 document.getElementById('sendToAllId').style.display = 'none';
					 </script>
				 {/if}
*}
		{/if}

		</table>
<!--/ajax:foundEmployees-->

{/if}
		{/capture}



	{*moduleShowemployees: Show courses to be assigned*}
	{capture name = 't_courses_to_be_assigned_code'}
	{if !$T_SORTED_TABLE || $T_SORTED_TABLE == 'coursesTable'}
<!--ajax:coursesTable-->
	<table style = "width:100%" size = "{$T_TABLE_SIZE}" id = "coursesTable" class = "sortedTable" useAjax = "1" url = "{$smarty.server.PHP_SELF}?ctg=search_users&op={$smarty.get.op}&courses=1&">

		<tr class = "topTitle">
			<td class = "topTitle" name = "name" style = "width:{if $smarty.get.ctg == "users"}35%{else}60%{/if}">{$smarty.const._NAME}</td>
			<td class = "topTitle" name = "directions_ID" style = "width:30%">{$smarty.const._PARENTDIRECTIONS}</td>
			{if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
			<td class = "topTitle centerAlign noSort" style = "width:5%">{$smarty.const._ASSIGNMENT}</td>
			{/if}
		</tr>
		{foreach name = 'users_to_courses_list' key = 'key' item = 'course' from = $T_DATA_SOURCE}
		<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"} {if !$course.active}deactivatedTableElement{/if}">
			<td>{strip}
				{if $course.has_instances}
					<img src = "images/16x16/plus.png" class = "ajaxHandle" alt = "{$smarty.const._COURSEINSTANCES}" title = "{$smarty.const._COURSEINSTANCES}" onclick = "toggleSubSection(this, '{$course.id}', 'instancesTable')"/>
				{/if}
				{$course.name}
			{/strip}</td>
			<td>{$T_DIRECTION_PATHS[$course.directions_ID]}</td>

			<td class = "centerAlign">
				{if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
					<img class = "ajaxHandle" src = "images/16x16/goto_student.png" alt = "{$smarty.const._SELECTION}" title = "{$smarty.const._SELECTION}" onclick = "assignCourseToUsers(this, '{$course.id}')">
				{/if}
			</td>

		</tr>
		{foreachelse}
		<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "10">{$smarty.const._NODATAFOUND}</td></tr>
		{/foreach}
	</table>
<!--/ajax:coursesTable-->
	{/if}

	{if !$T_SORTED_TABLE || $T_SORTED_TABLE == 'instancesTable'}
<div id = "filemanager_div" style = "display:none;">
<!--ajax:instancesTable-->
	<table style = "width:100%" no_auto = "1" size = "{$T_TABLE_SIZE}" id = "instancesTable" class = "sortedTable subSection" useAjax = "1" url = "{$smarty.server.PHP_SELF}?ctg=search_users&op={$smarty.get.op}&courses=1&">
		<tr class = "topTitle">
			<td class = "topTitle" name = "name" style = "width:{if $smarty.get.ctg == "users"}35%{else}60%{/if}">{$smarty.const._NAME}</td>
			<td class = "topTitle" name = "directions_ID" style = "width:30%">{$smarty.const._PARENTDIRECTIONS}</td>
			{if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
			<td class = "topTitle centerAlign noSort" style = "width:5%">{$smarty.const._ASSIGNMENT}</td>
			{/if}
		</tr>
		{foreach name = 'users_to_courses_list' key = 'key' item = 'course' from = $T_DATA_SOURCE}
			<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"} {if !$course.active}deactivatedTableElement{/if}">
			<td>{$course.name}</td>
			<td>{$T_DIRECTION_PATHS[$course.directions_ID]}</td>

			<td class = "centerAlign">
				{if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
					<img class = "ajaxHandle" src = "images/16x16/goto_student.png" alt = "{$smarty.const._SELECTION}" title = "{$smarty.const._SELECTION}" onclick = "assignCourseToUsers(this, '{$course.id}')">
				{/if}
			</td>

		</tr>
		{foreachelse}
		<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "10">{$smarty.const._NODATAFOUND}</td></tr>
		{/foreach}
	</table>
<!--/ajax:instancesTable-->
</div>
	{/if}
	{/capture}


	{*moduleShowemployees: Show courses to be assigned*}
	{capture name = 't_custom_group_stats_code'}
	<div class = "statisticsDiv" id = "statsDivCustomGroup">


<!--ajax:customGroupStats-->


{if isset($T_USER_TRAFFIC)}
<span>
                <table class = "statisticsGeneralInfo">
                    <tr><td class = "topTitle" colspan = "2">{$smarty.const._GROUPUSERTRAFFIC}</td></tr>
                    <tr class = "oddRowColor">
                    	<td class = "labelCell">{$smarty.const._TOTALLOGINS}: </td>
                    	<td class = "elementCell">{$T_USER_TRAFFIC.total_logins}</td></tr>
                    <tr class = "evenRowColor">
                    	<td class = "labelCell">{$smarty.const._LESSONACCESS}: </td>
                    	<td class = "elementCell">{$T_USER_TRAFFIC.total_access}</td></tr>
                </table>

				<br/>
                <table class = "statisticsTools">
                    <tr><td>{$smarty.const._ACCESSPERLESSON}</td></tr>
                </table>
                <table class = "statisticsTools">
                    <tr>
                        <td class = "topTitle">{$smarty.const._LESSON}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._ACCESSNUMBER}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._TOTALACCESSTIME}</td>
                    </tr>
                    {foreach name = 'lesson_traffic_list' key = "id" item = "lesson" from = $T_USER_TRAFFIC.lessons}
                        <tr class = "{cycle name = 'lessontraffic' values = 'oddRowColor, evenRowColor'} {if !$lesson.active}deactivatedTableElement{/if}">
                            <td>{$lesson.name}</td>
                            <td class = "centerAlign">{$lesson.accesses}</td>
                            <td class = "centerAlign">
                            	<span style="display:none">{$lesson.total_seconds}</span>
                                {if $lesson.total_seconds}
                                	{if $lesson.hours}{$lesson.hours}{$smarty.const._HOURSSHORTHAND} {/if}
                                	{if $lesson.minutes}{$lesson.minutes}{$smarty.const._MINUTESSHORTHAND} {/if}
                                	{if $lesson.seconds}{$lesson.seconds}{$smarty.const._SECONDSSHORTHAND}{/if}
                                {else}
                                	{$smarty.const._NOACCESSDATA}
                                {/if}
                            </td>
                        </tr>
                    {foreachelse}
                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                    {/foreach}
                </table>
</span>
{else}
<span class = "emptyCategory">{$smarty.const._NOUSERSFOUND}</span>
{/if}


<!--/ajax:customGroupStats-->


</div>
{/capture}

			{*  **************************************************************
				DISPLAYING THE CAPTURED TABLES
				**************************************************************	*}
{capture name = 't_search_all'}

	{$T_REPORT_FORM.javascript}
		<form {$T_REPORT_FORM.attributes}>
			{$T_REPORT_FORM.hidden}
				{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
					<div class="tabber">
						<div class="tabbertab">
							<h3>{$smarty.const._BASICCRITERIA}</h3>
							{eF_template_printBlock title = $smarty.const._BASICSEARCHOPTIONS data = $smarty.capture.t_reports_code image = '32x32/search.png'}
						</div>
						<div class="tabbertab">
							<h3>{$smarty.const._ADVANCED}</h3>
							{eF_template_printBlock title = $smarty.const._ADVANCEDSEARCH data = $smarty.capture.t_reports_advanced_search image = '32x32/search.png'}
						</div>
					 </div>
				{else} {* #cpp#else *}
					{eF_template_printBlock title = $smarty.const._SEARCHCRITERIA data = $smarty.capture.t_reports_advanced_search image = '32x32/search.png'}
				{/if} {* #cpp#endif *}
				<br>
				<div class="tabber">
					<div class="tabbertab" title="{$smarty.const._SEARCHRESULTS}">
						{eF_template_printBlock title = $smarty.const._EMPLOYEESFULFILLINGCRITERIA data = $smarty.capture.t_employees_code image = '32x32/user.png' options = $T_SENDALLMAIL_LINK}
					</div>
					<div class="tabbertab" title="{$smarty.const._ASSIGNCOURSES}">
						{eF_template_printBlock title = $smarty.const._MASSCOURSESASSIGNMENTINFO data = $smarty.capture.t_courses_to_be_assigned_code image = '32x32/courses.png'}
					</div>
					<div class="tabbertab">
						<h3>{$smarty.const._USERSTRAFFIC}</h3>
						{eF_template_printBlock title = $smarty.const._USERSTRAFFIC data = $smarty.capture.t_custom_group_stats_code image = '32x32/search.png'}
					</div>
				</div>
		</form>
{/capture}
	   {eF_template_printBlock title = $smarty.const._FINDEMPLOYEES data = $smarty.capture.t_search_all image = '32x32/scorm.png' main_options = $T_TABLE_OPTIONS}


<div id='insert_into_group' style="display:none">
{capture name = 't_insert_into_group_code'}
	{$T_INSERT_INTO_GROUP_POPUP_FORM.javascript}
		<form {$T_INSERT_INTO_GROUP_POPUP_FORM.attributes}>
			{$T_INSERT_INTO_GROUP_POPUP_FORM.hidden}
			<table class = "formElements" width="100%">
			<tr><td class = "labelCell">{$T_INSERT_INTO_GROUP_POPUP_FORM.existing_group.label}:&nbsp;</td><td>{$T_INSERT_INTO_GROUP_POPUP_FORM.existing_group.html}</td></tr>
			<tr><td class = "labelCell">{$T_INSERT_INTO_GROUP_POPUP_FORM.new_group.label}:&nbsp;</td><td>{$T_INSERT_INTO_GROUP_POPUP_FORM.new_group.html}</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td></td><td><input type="button" class="flatButton" onclick="insertFoundUsersIntoGroup(this)" value="{$smarty.const._SUBMIT}" /><input type="button" class="flatButton" onclick="insertFoundUsersIntoGroupAndGotoIt(this)" value="{$smarty.const._SUBMITANDEDITGROUP}" /></td></tr>
			</table>
		</form>
{/capture}
{eF_template_printBlock title = $smarty.const._INSERTINTOGROUP data = $smarty.capture.t_insert_into_group_code image = '32x32/users.png'}
</div>
