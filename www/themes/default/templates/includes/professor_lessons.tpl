{if $smarty.get.add_lesson || $smarty.get.edit_lesson}
{*moduleNewLessonDirection: Create a new direction or lesson forms*}
	{capture name = "moduleNewLessonDirection"}
	<tr><td class = "moduleCell">
		{capture name = 't_lesson_code'}
		<script>var editLesson = '{$smarty.get.edit_lesson}';</script>
			{capture name = 't_edit_lesson_code'}
			<table width = "100%">
				<tr><td class = "topAlign" width = "50%">
					{$T_LESSON_FORM.javascript}
					<form {$T_LESSON_FORM.attributes}>
					{$T_LESSON_FORM.hidden}
					<table class = "formElements">
						<tr><td class = "labelCell">{$T_LESSON_FORM.name.label}:&nbsp;</td>
							<td>{$T_LESSON_FORM.name.html}</td></tr>
					 {if isset($T_LESSON_FORM.languages_NAME.label)}
						<tr><td class = "labelCell">{$T_LESSON_FORM.languages_NAME.label}:&nbsp;</td>
							<td>{$T_LESSON_FORM.languages_NAME.html}</td></tr>
					 {/if}
						<tr><td class = "labelCell">{$T_LESSON_FORM.directions_ID.label}:&nbsp;</td>
							<td>{$T_LESSON_FORM.directions_ID.html}</td></tr>
				{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
{*
						<tr><td class = "labelCell">{$T_LESSON_FORM.location.label}:&nbsp;</td>
							<td class = "elementCell">
							{$T_LESSON_FORM.location.html}
							{if $smarty.session.employee_type != _EMPLOYEE}
							<a href="{$smarty.session.s_type}.php?ctg=module_hcd&op=branches&add_branch=1&returntab=basic" title = "{$smarty.const._NEWBRANCH}" ><img src = "images/16x16/add.png" title = "{$smarty.const._NEWBRANCH}" alt = "{$smarty.const._NEWBRANCH}" ></a>
							{/if}
							</td></tr>
*}
				{/if} {* #cpp#endif *}
						<tr><td class = "labelCell">{$T_LESSON_FORM.course_only.1.label}:&nbsp;</td>
							<td>{$T_LESSON_FORM.course_only.1.html}</td></tr>
				{if $T_CONFIGURATION.lesson_enroll || (isset($smarty.get.edit_lesson) && !$T_EDIT_LESSON->lesson.course_only)}
						<tr><td class = "labelCell"></td>
							<td>{$T_LESSON_FORM.course_only.0.html}</td></tr>
				{/if}
						<tr><td class = "labelCell">{$T_LESSON_FORM.active.label}:&nbsp;</td>
							<td class = "elementCell">{$T_LESSON_FORM.active.html}</td></tr>
						<tr class = "only_lesson" {if !$T_EDIT_LESSON || $T_EDIT_LESSON->lesson.course_only}style = "display:none"{/if}><td class = "labelCell">{$T_LESSON_FORM.show_catalog.label}:&nbsp;</td>
							<td class = "elementCell">{$T_LESSON_FORM.show_catalog.html}</td></tr>
				{if 'payments'|eF_template_isOptionVisible}
						<tr id = "price_row" class = "only_lesson" {if !$T_EDIT_LESSON || $T_EDIT_LESSON->lesson.course_only}style = "display:none"{/if}><td class = "labelCell">{$T_LESSON_FORM.price.label}:&nbsp;</td>
							<td>{$T_LESSON_FORM.price.html} {$T_CURRENCYSYMBOLS[$T_CONFIGURATION.currency]}</td></tr>
				{/if}
				{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
				{if 'payments'|eF_template_isOptionVisible}
						<tr id = "recurring_row" class = "only_lesson" {if !$T_EDIT_LESSON || $T_EDIT_LESSON->lesson.course_only}style = "display:none"{/if}><td class = "labelCell">{$T_LESSON_FORM.recurring.label}:&nbsp;</td>
							<td>{$T_LESSON_FORM.recurring.html}</td></tr>
						<tr id = "duration_row" class = "only_lesson" {if !$T_EDIT_LESSON || !$T_EDIT_LESSON->options.recurring || $T_EDIT_LESSON->lesson.course_only}style = "display:none"{/if}>
							<td class = "labelCell">{$smarty.const._CHARGINGEACH}:&nbsp;</td>
							<td><span id = "D_duration" {if $T_EDIT_LESSON->options.recurring != 'D'}style = "display:none"{/if}>{$T_LESSON_FORM.D_duration.html} {$T_LESSON_FORM.D_duration.label}</span>
								<span id = "W_duration" {if $T_EDIT_LESSON->options.recurring != 'W'}style = "display:none"{/if}>{$T_LESSON_FORM.W_duration.html} {$T_LESSON_FORM.W_duration.label}</span>
								<span id = "M_duration" {if $T_EDIT_LESSON->options.recurring != 'M'}style = "display:none"{/if}>{$T_LESSON_FORM.M_duration.html} {$T_LESSON_FORM.M_duration.label}</span>
								<span id = "Y_duration" {if $T_EDIT_LESSON->options.recurring != 'Y'}style = "display:none"{/if}>{$T_LESSON_FORM.Y_duration.html} {$T_LESSON_FORM.Y_duration.label}</span></td></tr>
				{/if}

					{if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
						<tr><td colspan = "2">
								<fieldset class = "fieldsetSeparator"><legend>{$smarty.const._ADVANCEDSETTINGS}</legend></fieldset>
							</td></tr>
						<tr class = "only_lesson" {if !$T_EDIT_LESSON || $T_EDIT_LESSON->lesson.course_only}style = "display:none"{/if}><td class = "labelCell">{$T_LESSON_FORM.max_users.label}:&nbsp;</td>
							<td class = "elementCell">{$T_LESSON_FORM.max_users.html}</td></tr>
						<tr class = "only_lesson" {if !$T_EDIT_LESSON || $T_EDIT_LESSON->lesson.course_only}style = "display:none"{/if}><td class = "labelCell">{$T_LESSON_FORM.duration.label}:&nbsp;</td>
							<td class = "elementCell">{$T_LESSON_FORM.duration.html} {$smarty.const._DAYSAFTERREGISTRATION}</td></tr>
						<tr><td class = "labelCell">{$smarty.const._COPYPROPERTIESFROM}:</td>
		                    <td class = "elementCell">
		                        <input type = "text" id = "autocomplete_copy" class = "autoCompleteTextBox"/>
		                        <img id = "busy_copy" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
		                        <div id = "autocomplete_lessons_copy" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
		                    </td>
		                </tr>

					{if $smarty.get.add_lesson}
						<tr><td class = "labelCell">{$smarty.const._CLONELESSON}:</td>
		                    <td class = "elementCell">
		                        <input type = "text" id = "autocomplete_clone" class = "autoCompleteTextBox"/>
		                        <img id = "busy_clone" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
		                        <div id = "autocomplete_lessons_clone" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
		                    </td>
		                </tr>

					{/if}
						<tr><td class = "labelCell">{$smarty.const._SHAREFOLDERWITH}:</td>
		                    <td class = "elementCell">
		                        <input type = "text" name = "autocomplete_share" id = "autocomplete_share" class = "autoCompleteTextBox" value = "{$T_SHARE_FOLDER_WITH}"/>
		                        <img id = "busy_share" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
		                        <div id = "autocomplete_lessons_share" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
		                    </td>
		                </tr>

						<tr><td></td>
                		<td class = "infoCell">{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td></tr>
					{/if} {* #cpp#endif *}
				{/if} {* #cpp#endif *}

						<tr><td></td>
							<td class = "submitCell">{$T_LESSON_FORM.submit_lesson.html}</td></tr>
					</table>
					</form>
				</td></tr>
			</table>
			{/capture}

		<div class = "tabber">
			{eF_template_printBlock tabber="lessons" title = "`$smarty.const._EDITLESSON`" data = $smarty.capture.t_edit_lesson_code image = '32x32/lessons.png'}

			{capture name = 't_users_to_lessons_code'}
			<div class = "headerTools">
				<span>
					<img src = "images/16x16/success.png" title = "{$smarty.const._SETALLUSERSSTATUSCOMPLETED}" alt = "{$smarty.const._SETALLUSERSSTATUSCOMPLETED}"/>
					<a href = "javascript:void(0)" {if !$T_CURRENT_USER->coreAccess.lessons == 'change' || $T_CURRENT_USER->coreAccess.lessons == 'change'} onclick = "setAllUsersStatusCompleted(this)" {/if}>{$smarty.const._SETALLUSERSSTATUSCOMPLETED}</a>
				</span>
			</div>

<!--ajax:usersTable-->
			<table style = "width:100%" class = "sortedTable" size = "{$T_TABLE_SIZE}" sortBy = "4" order = "desc" id = "usersTable" useAjax = "1" rowsPerPage = "{$smarty.const.G_DEFAULT_TABLE_SIZE}" {if isset($T_BRANCHES_FILTER)}branchFilter="{$T_BRANCHES_FILTER}"{/if} {if isset($T_JOBS_FILTER)}jobFilter="{$T_JOBS_FILTER}"{/if} url = "{$smarty.server.PHP_SELF}?ctg=professor_lessons&edit_lesson={$smarty.get.edit_lesson}&">
				<tr class = "topTitle">
					<td class = "topTitle" name = "login">{$smarty.const._LOGIN}</td>
					<td class = "topTitle" name = "role">{$smarty.const._USERROLEINLESSON}</td>
					<td class = "topTitle centerAlign" name = "completed">{$smarty.const._COMPLETED}</td>
					<td class = "topTitle noSort centerAlign">{$smarty.const._OPERATIONS}</td>
					<td class = "topTitle centerAlign" name = "has_lesson">{$smarty.const._CHECK}</td>
				</tr>
				{foreach name = 'users_to_lessons_list' key = 'key' item = 'user' from = $T_DATA_SOURCE}
				<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"} {if !$user.active}deactivatedTableElement{/if}">
					<td>#filter:login-{$user.login}#</td>
					<td>
				{if !isset($T_CURRENT_USER->coreAccess.lessons) || $T_CURRENT_USER->coreAccess.lessons== 'change'}
						<select name="type_{$user.login}" id = "type_{$user.login}" onchange = "$('checked_{$user.login}').checked=true;ajaxPost('{$user.login}', this);">
				{foreach name = 'roles_list' key = 'role_key' item = 'role_item' from = $T_ROLES}
							<option value="{$role_key}" {if ($user.role == $role_key)}selected{/if} {if $user.basic_user_type == $role_key}style = "font-weight:bold"{/if}>{$role_item}</option>
				{/foreach}
						</select>
				{else}
						{$T_ROLES[$user.role]}
				{/if}
					</td>
					<td class = "centerAlign">{if $user.has_lesson}{if $user.completed}<img src = "images/16x16/success.png" alt = "{$smarty.const._COMPLETEDON} #filter:timestamp-{$user.timestamp_completed}#" title = "{$smarty.const._COMPLETEDON} #filter:timestamp-{$user.timestamp_completed}#"/>{else}<img src = "images/16x16/forbidden.png" alt = "{$smarty.const._NOTCOMPLETED}" title = "{$smarty.const._NOTCOMPLETED}"/>{/if}{/if}</td>
					<td class = "centerAlign">
				{if $user.basic_user_type == 'student' && ($user.has_lesson)}
							<img class = "ajaxHandle" src="images/16x16/refresh.png" title="{$smarty.const._RESETPROGRESSDATA}" alt="{$smarty.const._RESETPROGRESSDATA}" onclick = "if (confirm(translations['_IRREVERSIBLEACTIONAREYOUSURE'])) resetProgress(this, '{$user.login}');">
				{/if}

					</td>
					<td class = "centerAlign">
				{if !isset($T_CURRENT_USER->coreAccess.lessons) || $T_CURRENT_USER->coreAccess.lessons== 'change'}
						<input class = "inputCheckbox" type = "checkbox" name = "checked_{$user.login}" id = "checked_{$user.login}" onclick = "ajaxPost('{$user.login}', this);" {if $user.has_lesson}checked = "checked"{/if} />{if $user.has_lesson}<span style = "display:none">checked</span>{/if} {*Text for sorting*}
				{else}
						{if $user.has_lesson}<img src = "images/16x16/success.png" alt = "{$smarty.const._LESSONUSER}" title = "{$smarty.const._LESSONUSER}"><span style = "display:none">checked</span>{/if}
				{/if}
					</td>
				</tr>
				{foreachelse}
				<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "100%">{$smarty.const._NODATAFOUND}</td></tr>
				{/foreach}
			</table>
<!--/ajax:usersTable-->
		{/capture}

								{if $smarty.get.edit_lesson && !$T_EDIT_LESSON->lesson.course_only}
								<div class="tabbertab {if $smarty.get.tab=='users'}tabbertabdefault{/if}">
									<h3>{$smarty.const._EDITUSERSLESSON}</h3>
									{eF_template_printBlock title = $smarty.const._UPDATEUSERSTOLESSONS data = $smarty.capture.t_users_to_lessons_code image = '32x32/users.png'}
								</div>
								{/if}


								{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}

								{* ENTERPRISE EDITION: Create tab with all skills -  the skills offered by this lesson-seminar are to be selected *}

								{if $smarty.get.edit_lesson && $T_STANDALONE_LESSON}
								{*  ****************************************************
									This is the form that contains the skills offered by the seminar
									**************************************************** *}
									{capture name = 't_lesson_skills'}
											<div class = "headerTools">
												<span>
													<img src = "images/16x16/add.png" title = "{$smarty.const._NEWSKILL}" alt = "{$smarty.const._NEWSKILL}">
													<a href = "{$smarty.server.PHP_SELF}?ctg=module_hcd&op=skills&add_skill=1" title = "{$smarty.const._NEWSKILL}" >{$smarty.const._NEWSKILL}</a>
												</span>
											</div>
											{***Lesson skill table***}
<!--ajax:skillsTable-->
											<table style = "width:100%" class = "sortedTable" size = "{$T_SKILLS_SIZE}" sortBy = "0" id = "skillsTable" useAjax = "1" rowsPerPage = "{$smarty.const.G_DEFAULT_TABLE_SIZE}" url = "{$smarty.server.PHP_SELF}?ctg=professor_lessons&edit_lesson={$smarty.get.edit_lesson}&">
												<tr class = "topTitle">
													<td class = "topTitle" name = "description" style = "width:35%">{$smarty.const._SKILL}</td>
													<td class = "topTitle" name = "specification" >{$smarty.const._SPECIFICATION}</td>
													<td class = "topTitle centerAlign" name="lesson_ID" style = "width:5%">{$smarty.const._CHECK}</td>
												</tr>

											{foreach name = 'skill_list' key = 'key' item = 'skill' from = $T_SKILLS}
												<tr class = "{cycle values = "oddRowColor, evenRowColor"}">
													<td><a href="{$smarty.server.PHP_SELF}?ctg=module_hcd&op=skills&edit_skill={$skill.skill_ID}">{$skill.description}</a></td>
													<td><input class = "inputText" type="text" name="spec_skill_{$skill.skill_ID}"  id="spec_skill_{$skill.skill_ID}" onchange="ajaxLessonSkillUserPost(2,'{$skill.skill_ID}', this);" value="{$skill.specification}"{if $skill.lesson_ID != $smarty.get.edit_lesson} style="visibility:hidden" {/if}></td>
													<td class = "centerAlign"><input class = "inputCheckBox" type = "checkbox" name = "{$skill.skill_ID}" onclick="javascript:show_hide_spec('{$skill.skill_ID}');ajaxLessonSkillUserPost(1,'{$skill.skill_ID}', this);" {if $skill.lesson_ID == $smarty.get.edit_lesson} checked {/if} ></td>
												</tr>
											{foreachelse}
												<tr class = "defaultRowHeight oddRowColor"><td colspan = "3" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
											{/foreach}
											</table>
<!--/ajax:skillsTable-->
									{/capture}

									<script>var myform = "skills_to_lesson";</script>

									{*moduleAllBranches: Show branches *}
									{capture name = 't_lesson_branches_code'}
										{* Only supervisors and administrators may change branch data - currently all - TODO: selected *}
										{if $smarty.session.employee_type != _EMPLOYEE}
											<div class = "headerTools">
												<span>
													<img src = "images/16x16/add.png" title = "{$smarty.const._NEWBRANCH}" alt = "{$smarty.const._NEWBRANCH}" >
													<a href="{$smarty.session.s_type}.php?ctg=module_hcd&op=branches&add_branch=1" title = "{$smarty.const._NEWBRANCH}" >{$smarty.const._NEWBRANCH}</a>
												</span>
											</div>
										{/if}

<!--ajax:branchesTable-->
										<table style = "width:100%" class = "sortedTable" size = "{$T_BRANCHES_SIZE}"  sortBy = "0" id = "branchesTable" useAjax = "1" rowsPerPage = "{$smarty.const.G_DEFAULT_TABLE_SIZE}" url = "{$smarty.session.s_type}.php?ctg=professor_lessons&edit_lesson={$smarty.get.edit_lesson}&">
											<tr class = "topTitle">
												<td class = "topTitle" name = "name">{$smarty.const._BRANCHNAME}</td>
												<td class = "topTitle" name = "city">{$smarty.const._CITY}</td>
												<td class = "topTitle" name = "address">{$smarty.const._ADDRESS}</td>
												<td class = "topTitle" name = "employees" align="center">{$smarty.const._EMPLOYEES}</td>
												<td class = "topTitle" name = "father_ID">{$smarty.const._FATHERBRANCHNAME}</td>
												<td class = "topTitle" name="lessons_ID" align="center">{$smarty.const._CHECK}</td>
											</tr>

										{foreach name = 'branch_list' key = 'key' item = 'branch' from = $T_BRANCHES}
											<tr class = "{cycle values = "oddRowColor, evenRowColor"}">
												<td>
												{if $smarty.session.s_type == "administrator" || $branch.supervisor == 1}
													<a href = "{$smarty.server.PHP_SELF}?ctg=module_hcd&op=branches&edit_branch={$branch.branch_ID}" class = "editLink">{$branch.name}</a></td>
												{else}
													{$branch.name}
												{/if}
												<td>{$branch.city}</td>
												<td>{$branch.address}</td>
												<td class = "centerAlign">{$branch.employees}</td>
												<td> {if $smarty.session.s_type == "administrator" || $branch.father_supervisor == 1}<a href = "{$smarty.session.s_type}.php?ctg=module_hcd&op=branches&edit_branch={$branch.father_ID}" class = "editLink">{$branch.father}{else}{$branch.father}{/if}</a></td>
												<td class = "centerAlign">
												{if $smarty.session.s_type == "administrator" || $branch.supervisor == 1}
													<input class = "inputCheckBox" type = "checkbox" name = "{$branch.branch_ID}" onclick="javascript:ajaxLessonBranchPost('{$branch.branch_ID}', this);" {if $branch.lessons_ID == $smarty.get.edit_lesson} checked {/if} >
												{/if}
												</td>
											</tr>
										{foreachelse}
											<tr class = "defaultRowHeight oddRowColor"><td colspan = "6" class = "emptyCategory">{$smarty.const._NOBRANCHESHAVEBEENREGISTERED}</td></tr>
										{/foreach}
										</table>
<!--/ajax:branchesTable-->
									{/capture}
{*
									<div class="tabbertab {if ($smarty.get.tab == "subbranches")} tabbertabdefault {/if}">
										<h3>{$smarty.const._LESSONBRANCHES}</h3>
										{eF_template_printBlock title = $smarty.const._LESSONBRANCHESSELECTION data = $smarty.capture.t_lesson_branches_code image = '32x32/branch.png'}
									</div>
*}
									{/if}
								{/if} {* #cpp#endif *}
										</div>
							{/capture}
			{if $smarty.get.add_lesson}
					{eF_template_printBlock title = $smarty.const._NEWLESSONOPTIONS data = $smarty.capture.t_lesson_code image = '32x32/lessons.png'}
			{else}
					{eF_template_printBlock title = "`$smarty.const._LESSONOPTIONSFOR` <span class = 'innerTableName'>&quot;`$T_LESSON_FORM.name.value`&quot;</span>" data = $smarty.capture.t_lesson_code image = '32x32/lessons.png' options = $T_LESSON_OPTIONS}
			{/if}
							</td></tr>
	{/capture}



	{else}
	{*moduleLessons: The lessons list*}
		{capture name = "moduleLessons"}
					{if $smarty.get.lesson_info}
						{include file = "includes/lesson_information.tpl"}
						{$smarty.capture.moduleLessonInformation}
					{elseif $smarty.get.lesson_settings}
							<tr><td class = "moduleCell">
						{include file = "includes/lesson_settings.tpl"}
										</td></tr>
					{else}
							<tr><td class = "moduleCell">
							<script>var activate = '{$smarty.const._ACTIVATE}';var deactivate = '{$smarty.const._DEACTIVATE}';var courseonly = '{$smarty.const._COURSEONLY}';var directly = '{$smarty.const._DIRECTLY}';</script>
						{capture name = 't_lessons_code'}
							{if !isset($T_CURRENT_USER->coreAccess.lessons) || $T_CURRENT_USER->coreAccess.lessons== 'change'}
								<div class = "headerTools">
									<span>
										<img src = "images/16x16/add.png" title = "{$smarty.const._NEWLESSON}" alt = "{$smarty.const._NEWLESSON}">
										<a href = "{$smarty.server.PHP_SELF}?ctg=professor_lessons&add_lesson=1" title = "{$smarty.const._NEWLESSON}" >{$smarty.const._NEWLESSON}</a>
									</span>
									<span>
										<img src = "images/16x16/import.png" title = "{$smarty.const._IMPORTLESSON}" alt = "{$smarty.const._IMPORTLESSON}">
										<a href = "javascript:void(0)" title = "{$smarty.const._IMPORTLESSON}" onclick = "eF_js_showDivPopup(event, '{$smarty.const._IMPORTLESSON}', 0, 'import_lesson_popup')">{$smarty.const._IMPORTLESSON}</a></a>
									</span>
								</div>
								<div id = "import_lesson_popup" style = "display:none">
									{capture name = "t_import_lesson_code"}
										{$T_IMPORT_LESSON_FORM.javascript}
										<form {$T_IMPORT_LESSON_FORM.attributes}>
										{$T_IMPORT_LESSON_FORM.hidden}
										<table class = "formElements">
											<tr><td class = "labelCell">{$T_IMPORT_LESSON_FORM.import_content.label}:&nbsp;</td>
												<td class = "elementCell">{$T_IMPORT_LESSON_FORM.import_content.html}</td></tr>
											<tr><td></td>
												<td class = "infoCell">{$smarty.const._FILESIZEMUSTBESMALLERTHAN} <b>{$T_MAX_FILE_SIZE}</b> {$smarty.const._KB}</td></tr>
											<tr><td></td>
												<td class = "submitCell">{$T_IMPORT_LESSON_FORM.submit_lesson.html}</td></tr>
										</table>
										</form>
									{/capture}
									{eF_template_printBlock title = $smarty.const._IMPORTLESSON data = $smarty.capture.t_import_lesson_code image = '32x32/import.png'}
								</div>

								{assign var = "change_lessons" value = 1}
							{/if}
<!--ajax:lessonsTable-->
								<table style = "width:100%" class = "sortedTable" activeFilter = 1 size = "{$T_TABLE_SIZE}" sortBy = "0" useAjax = "1" id = "lessonsTable" rowsPerPage = "20" url = "{$smarty.server.PHP_SELF}?ctg=professor_lessons&">
									<tr class = "topTitle">
										<td class = "topTitle" name = "name">{$smarty.const._NAME} </td>
										<td class = "topTitle" name = "direction_name">{$smarty.const._CATEGORY}</td>
										{*<td class = "topTitle centerAlign" name = "students">{$smarty.const._PARTICIPATION}</td>*}
										<td class = "topTitle centerAlign" name = "course_only">{$smarty.const._AVAILABLE}</td>
									{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
										{*<td class = "topTitle centerAlign" name ="skills_offered">{$smarty.const._SKILLS}</td>*}
									{/if} {* #cpp#endif *}
									{if 'payments'|eF_template_isOptionVisible}
										<td class = "topTitle centerAlign" name = "price">{$smarty.const._PRICE}</td>
									{/if}
										<td class = "topTitle" name = "created">{$smarty.const._CREATED}</td>
										<td class = "topTitle centerAlign" name = "active">{$smarty.const._ACTIVE2}</td>
										<td class = "topTitle noSort centerAlign">{$smarty.const._OPERATIONS}</td>
									</tr>
					{foreach name = 'lessons_list2' key = 'key' item = 'lesson' from = $T_DATA_SOURCE}
									<tr id = "row_{$lesson.id}" class = "{cycle values = "oddRowColor, evenRowColor"} {if !$lesson.active}deactivatedTableElement{/if}">
										<td id = "column_{$lesson.id}" class = "editLink">
											<a class="editLink {if 'tooltip'|eF_template_isOptionVisible}info{/if}" url = "ask_information.php?lessons_ID={$lesson.id}&type=lesson" href= "{$smarty.server.PHP_SELF}?ctg=professor_lessons&edit_lesson={$lesson.id}">
												{$lesson.name}
											</a>
										<td>{$lesson.direction_name}</td>
										{*<td class = "centerAlign">{if !$lesson.course_only}{if $lesson.max_users}{$lesson.students}/{$lesson.max_users}{else}{$lesson.students}{/if}{else}-{/if}</td>*}
										<td class = "centerAlign">
									{if $lesson.course_only}
											<img class = "ajaxHandle" src = "images/16x16/courses.png"	 alt = "{$smarty.const._COURSEONLY}" title = "{$smarty.const._COURSEONLY}" {if $change_lessons}onclick = "setLessonAccess(this, '{$lesson.id}')"{/if}>
									{else}
											<img class = "ajaxHandle" src = "images/16x16/lessons.png" alt = "{$smarty.const._DIRECTLY}"   title = "{$smarty.const._DIRECTLY}"   {if $change_lessons}onclick = "setLessonAccess(this, '{$lesson.id}')"{/if}>
									{/if}
										</td>
									{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
										{*<td class = "centerAlign">{if $lesson.skills_offered == 0}-{else}{$lesson.skills_offered}{/if}</td>*}
									{/if} {* #cpp#endif *}
									{if 'payments'|eF_template_isOptionVisible}
										<td class = "centerAlign">{if !$lesson.course_only}{if $lesson.price == 0}-{else}{$lesson.price_string}{/if}{else}-{/if}</td>
									{/if}
										<td>#filter:timestamp-{$lesson.created}#</td>
										<td class = "centerAlign">
									{if $lesson.active == 1}
											<img class = "ajaxHandle" src = "images/16x16/trafficlight_green.png" alt = "{$smarty.const._DEACTIVATE}" title = "{$smarty.const._DEACTIVATE}" {if $change_lessons}onclick = "activateLesson(this, '{$lesson.id}');"{/if}>
									{else}
											<img class = "ajaxHandle" src = "images/16x16/trafficlight_red.png"   alt = "{$smarty.const._ACTIVATE}"   title = "{$smarty.const._ACTIVATE}"   {if $change_lessons}onclick = "activateLesson(this, '{$lesson.id}')"{/if}>
									{/if}
										</td>
										<td class = "centerAlign" style = "white-space:nowrap">
									{if $change_lessons}
											<a href = "{$smarty.server.PHP_SELF}?ctg=professor_lessons&edit_lesson={$lesson.id}"><img border = "0" src = "images/16x16/edit.png" title = "{$smarty.const._EDIT}" alt = "{$smarty.const._EDIT}" /></a>
										{if ($smarty.const.G_VERSIONTYPE != 'community')} {* #cpp#ifndef COMMUNITY *}
											{if ($smarty.const.G_VERSIONTYPE != 'standard')} {* #cpp#ifndef STANDARD *}
												<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._ARCHIVEENTITY}" alt = "{$smarty.const._ARCHIVEENTITY}" onclick = "archiveLesson(this, '{$lesson.id}')"/>
											{/if} {* #cpp#endif *}
										{/if} {* #cpp#endif *}
										{if ($smarty.const.G_VERSIONTYPE != 'enterprise')} {* #cpp#ifndef ENTERPRISE *}
											{if ($smarty.const.G_VERSIONTYPE != 'educational')} {* #cpp#ifndef EDUCATIONAL *}
												<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._DELETE}" alt = "{$smarty.const._DELETE}" onclick = "if (confirm('{$smarty.const._AREYOUSUREYOUWANTTODELETELESSON}')) deleteLesson(this, '{$lesson.id}')"/>
											{/if} {* #cpp#endif *}
										{/if} {* #cpp#endif *}
									{/if}
										</td>
									</tr>
					{foreachelse}
								<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "100%">{$smarty.const._NODATAFOUND}</td></tr>
					{/foreach}
								</table>
<!--/ajax:lessonsTable-->
									{/capture}
									{eF_template_printBlock title = $smarty.const._UPDATELESSONS data = $smarty.capture.t_lessons_code image = '32x32/lessons.png' help = 'Lessons'}
										</td></tr>
			{/if}
		{/capture}
	{/if}
