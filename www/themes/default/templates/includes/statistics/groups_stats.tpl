{* #cpp#ifndef COMMUNITY *}
{* #cpp#ifndef STANDARD *}
	    {capture name = 'group_statistics'}
	    	{if !$T_SINGLE_GROUP}
	            <table class = "statisticsSelectList">
	                <tr><td class = "labelCell">{$smarty.const._CHOOSEGROUP}:</td>
	                    <td class = "elementCell">
	                        <input type = "text" id = "autocomplete" class = "autoCompleteTextBox"/>
	                        <img id = "busy" src = "images/16x16/clock.png" style = "display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "autocomplete_groups" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
	                    </td>
	                </tr>
	                <tr><td></td>
	                    <td class = "infoCell">{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td>
	            	</tr>
	            </table>
	        {/if}

	        {if (isset($T_GROUP_INFO))}
			<script>
			var selGroup = "{$smarty.get.sel_group}";
			</script>

	            <table class = "statisticsTools">
	                <tr><td id = "right">
	                        {$smarty.const._EXPORTSTATS}

	                        <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=groups&sel_group={$smarty.get.sel_group}&excel=group{if isset($smarty.get.from_year)}&from_year={$smarty.get.from_year}&from_month={$smarty.get.from_month}&from_day={$smarty.get.from_day}&from_hour={$smarty.get.from_hour}&from_min={$smarty.get.from_min}{/if}{if isset($smarty.get.to_year)}&to_year={$smarty.get.to_year}&to_month={$smarty.get.to_month}&to_day={$smarty.get.to_day}&to_hour={$smarty.get.to_hour}&to_minute={$smarty.get.to_minute}{/if}{if $smarty.get.showOnlyGroupLessons == "true"}&showOnlyGroupLessons=true{/if}{if $smarty.get.showOnlyGroupUsers == "true"}&showOnlyGroupUsers=true{/if}">
	                            <img src = "images/file_types/xls.png" title = "{$smarty.const._XLSFORMAT}" alt = "{$smarty.const._XLSFORMAT}"/>
	                        </a>
	                        <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=groups&sel_group={$smarty.get.sel_group}&pdf=group{if isset($smarty.get.from_year)}&from_year={$smarty.get.from_year}&from_month={$smarty.get.from_month}&from_day={$smarty.get.from_day}&from_hour={$smarty.get.from_hour}&from_min={$smarty.get.from_min}{/if}{if isset($smarty.get.to_year)}&to_year={$smarty.get.to_year}&to_month={$smarty.get.to_month}&to_day={$smarty.get.to_day}&to_hour={$smarty.get.to_hour}&to_minute={$smarty.get.to_minute}{/if}{if $smarty.get.showOnlyGroupLessons == "true"}&showOnlyGroupLessons=true{/if}{if $smarty.get.showOnlyGroupUsers == "true"}&showOnlyGroupUsers=true{/if}">
	                            <img src = "images/file_types/pdf.png" title = "{$smarty.const._PDFFORMAT}" alt="{$smarty.const._PDFFORMAT}"/>
	                        </a>
	                    </td></tr>
	            </table>

	            <br/>
	            <table class = "statisticsGeneralInfo">
	            	<tr>
	            		<td>
	                        <table>
	                            <tr class = "{cycle name = 'common_group_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._GROUPNAME}:</td>
	                                <td class = "elementCell">{$T_GROUP_INFO.name} {if $T_GROUP_INFO.is_default}({$smarty.const._DEFAULT}){/if}</td>
	                            </tr>
	                            <tr class = "{cycle name = 'common_group_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._DESCRIPTION}:</td>
	                                <td class = "elementCell">{$T_GROUP_INFO.description}</td>
	                            </tr>
	                            <tr class = "{cycle name = 'common_group_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._GROUPUSERS}:</td>
	                                <td class = "elementCell">{$T_GROUP_INFO.users_count}</td>
	                            </tr>
	                            <tr class = "{cycle name = 'common_group_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._GROUPLESSONS}:</td>
	                                <td class = "elementCell">{$T_GROUP_INFO.lessons_count}</td>
	                            </tr>
							    <tr class = "{cycle name = 'common_group_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._GROUPCOURSES}:</td>
	                                <td class = "elementCell">{$T_GROUP_INFO.courses_count}</td>
	                            </tr>
	                        {if $T_GROUP_INFO.user_types_ID != 0}
	                            <tr class = "{cycle name = 'common_group_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._GROUPROLE}:</td>
	                                <td class = "elementCell">{$T_ROLES[$T_GROUP_INFO.user_types_ID]}</td>
	                            </tr>
	                        {/if}

	                        {if $T_GROUP_INFO.unique_key != ""}
	                            <tr class = "{cycle name = 'common_group_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._KEYUSAGE}:</td>
	                                <td class = "elementCell">{if $T_GROUP_INFO.key_max_usage}{if $T_GROUP_INFO.current_usage}{$T_GROUP_INFO.current_usage}{else}0{/if} / {$T_GROUP_INFO.key_max_usage}{else}{$smarty.const._UNLIMITED}{/if}</td>
	                            </tr>
	                        {/if}

	                        {if $T_GROUP_INFO.languages_NAME}
	                        	<tr class = "{cycle name = 'common_group_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._GROUPLANGUAGE}:</td>
	                                <td class = "elementCell">{$T_LANGUAGES[$T_GROUP_INFO.languages_NAME]}</td>
	                            </tr>
	                        {/if}

	                        </table>
	            	</td></tr>
	            </table>

	        {/if}


	        {if !empty($T_GROUP_INFO)}
	        <div class = "tabber">
	            <div class = "statisticsDiv tabbertab {if (isset($smarty.get.tab) &&  $smarty.get.tab == 'users')} tabbertabdefault{/if}" title = "{$smarty.const._GROUPUSERS}">
	                {if !empty($T_GROUP_USERS.student)}
	                    <table class = "statisticsTools">
							<tr><td>{$smarty.const._STUDENTROLE}:</td></tr>
						</table>
	                    <table class = "sortedTable">

					        <tr class = "topTitle">
					            <td class = "topTitle" name = "login" width="20%">{$smarty.const._USER}</td>
					            <td class = "topTitle" name = "languages_NAME">{$smarty.const._LANGUAGE}</td>
					            <td class = "topTitle" name = "timestamp">{$smarty.const._REGISTRATIONDATE}</td>
					            <td class = "topTitle" name = "last_login">{$smarty.const._LASTLOGIN}</td>
					            <td class = "topTitle centerAlign" name = "jobs_num">{$smarty.const._JOBSASSIGNED}</td>

					        {*
						    {if $smarty.session.s_type == "administrator"}
					            <td class = "topTitle centerAlign" name = "active">{$smarty.const._ACTIVE2}</td>
						    {/if}
						    *}
					            <td class = "topTitle noSort centerAlign">
					            	{$smarty.const._OPERATIONS}
					            </td>
					        </tr>

	                        {foreach name = 'lesson_list' key = 'users_login' item = 'user' from = $T_GROUP_USERS.student}
					        <tr id="row_{$user.login}" class = "{cycle values = "oddRowColor, evenRowColor"} {if !$user.active}deactivatedTableElement{/if}">
					            <td id="column_{$user.login}">
					                {if $user.active == 1}
					                    <a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink">#filter:login-{$user.login}#</a>
					                {else}
					                    #filter:login-{$user.login}#
					                {/if}
					            </td>
					            <td>{$T_LANGUAGES[$user.languages_NAME]}</td>
					            <td>#filter:timestamp-{$user.timestamp}#</td>
					            <td>{if $user.last_login}#filter:timestamp_time_nosec-{$user.last_login}#{else}{$smarty.const._NEVER}{/if}</td>
					            <td class = "centerAlign">{$user.jobs_num}</td>
					        {*
					        {if $smarty.session.s_type == "administrator"}
					            <td class = "centerAlign">
					            {if $user.login != $smarty.session.s_login}
					                {if $user.active == 1}
					                    <img class = "ajaxHandle" src = "images/16x16/trafficlight_green.png" alt = "{$smarty.const._DEACTIVATE}" title = "{$smarty.const._DEACTIVATE}" {if $_change_}onclick = "activateUser(this, '{$user.login}')"{/if}>
					                {else}
					                    <img class = "ajaxHandle" src = "images/16x16/trafficlight_red.png"   alt = "{$smarty.const._ACTIVATE}"   title = "{$smarty.const._ACTIVATE}"   {if $_change_}onclick = "activateUser(this, '{$user.login}')"{/if}>
					                {/if}
					            {/if}
					            </td>
					        {/if}
					        *}
						        <td class = "centerAlign">
					        {if $user.login != $smarty.session.s_login && $user.user_type != 'administrator'}
					                <a href="{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile&op=status&print_preview=1&popup=1" onclick = "eF_js_showDivPopup(event, '{$smarty.const._EMPLOYEEFORMPRINTPREVIEW}', 2)" target = "POPUP_FRAME"><img class = "handle" src='images/16x16/printer.png' title=  '{$smarty.const._PRINTPREVIEW}' alt = '{$smarty.const._PRINTPREVIEW}' /></a>
					        {/if}
							{if !isset($T_CURRENT_USER->coreAccess.statistics) || $T_CURRENT_USER->coreAccess.statistics != 'hidden'}
					        		<a href="{$smarty.session.s_type}.php?ctg=statistics&option=user&sel_user={$user.login}"><img class = "handle" src = "images/16x16/reports.png" title = "{$smarty.const._STATISTICS}" alt = "{$smarty.const._STATISTICS}" /></a>
							{/if}
					        {if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
					                <a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink"><img class = "handle" src = "images/16x16/edit.png" title = "{$smarty.const._EDIT}" alt = "{$smarty.const._EDIT}" /></a>
					                {if $smarty.session.s_login != $user.login}
					                	{*<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._ARCHIVEENTITY}" alt = "{$smarty.const._ARCHIVEENTITY}" onclick = "archiveUser(this, '{$user.login}')"/>*} {*DOES NOT WORK*}
										{*<a href = "{$smarty.session.s_type}.php?ctg=users&op=users_data&delete_user={$user.login}" onclick = "return confirm('{$smarty.const._AREYOUSUREYOUWANTTOFIREEMPLOYEE}')" class = "deleteLink"><img border = "0" src = "images/16x16/error_delete.png" title = "{$smarty.const._FIRE}" alt = "{$smarty.const._FIRE}" /></a>*}
									{/if}
							{/if}
					            </td>
					        </tr>


	                        {/foreach}
	                    </table>
	                    <br/>
	                {/if}


	                {if !empty($T_GROUP_USERS.professor)}
	                    <table class = "statisticsTools">
							<tr><td>{$smarty.const._PROFESSORROLE}:</td></tr>
						</table>
	                    <table class = "sortedTable">

					        <tr class = "topTitle">
					            <td class = "topTitle" name = "login" width="20%">{$smarty.const._USER}</td>
					            <td class = "topTitle" name = "languages_NAME">{$smarty.const._LANGUAGE}</td>
					            <td class = "topTitle" name = "timestamp">{$smarty.const._REGISTRATIONDATE}</td>
					            <td class = "topTitle" name = "last_login">{$smarty.const._LASTLOGIN}</td>
					            <td class = "topTitle centerAlign" name = "jobs_num">{$smarty.const._JOBSASSIGNED}</td>
					        {*
						    {if $smarty.session.s_type == "administrator"}
					            <td class = "topTitle centerAlign" name = "active">{$smarty.const._ACTIVE2}</td>
						    {/if}
						    *}
					            <td class = "topTitle noSort centerAlign">
					            	{$smarty.const._OPERATIONS}
					            </td>
					        </tr>

	                        {foreach name = 'lesson_list' key = 'users_login' item = 'user' from = $T_GROUP_USERS.professor}
					        <tr id="row_{$user.login}" class = "{cycle values = "oddRowColor, evenRowColor"} {if !$user.active}deactivatedTableElement{/if}">
					            <td id="column_{$user.login}">
					                {if $user.active == 1}
					                    <a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink">#filter:login-{$user.login}#</a>
					                {else}
					                    #filter:login-{$user.login}#
					                {/if}
					            </td>
					            <td>{$T_LANGUAGES[$user.languages_NAME]}</td>
					            <td>#filter:timestamp-{$user.timestamp}#</td>
					            <td>{if $user.last_login}#filter:timestamp_time_nosec-{$user.last_login}#{else}{$smarty.const._NEVER}{/if}</td>
					            <td class = "centerAlign">{$user.jobs_num}</td>
					        {*
					        {if $smarty.session.s_type == "administrator"}
					            <td class = "centerAlign">
					            {if $user.login != $smarty.session.s_login}
					                {if $user.active == 1}
					                    <img class = "ajaxHandle" src = "images/16x16/trafficlight_green.png" alt = "{$smarty.const._DEACTIVATE}" title = "{$smarty.const._DEACTIVATE}" {if $_change_}onclick = "activateUser(this, '{$user.login}')"{/if}>
					                {else}
					                    <img class = "ajaxHandle" src = "images/16x16/trafficlight_red.png"   alt = "{$smarty.const._ACTIVATE}"   title = "{$smarty.const._ACTIVATE}"   {if $_change_}onclick = "activateUser(this, '{$user.login}')"{/if}>
					                {/if}
					            {/if}
					            </td>
					        {/if}
					        *}
						        <td class = "centerAlign">
					        {if $user.login != $smarty.session.s_login && $user.user_type != 'administrator'}
					                <a href="{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile&op=status&print_preview=1&popup=1" onclick = "eF_js_showDivPopup(event, '{$smarty.const._EMPLOYEEFORMPRINTPREVIEW}', 2)" target = "POPUP_FRAME"><img class = "handle" src='images/16x16/printer.png' title=  '{$smarty.const._PRINTPREVIEW}' alt = '{$smarty.const._PRINTPREVIEW}' /></a>
					        {/if}
							{if !isset($T_CURRENT_USER->coreAccess.statistics) || $T_CURRENT_USER->coreAccess.statistics != 'hidden'}
					        		<a href="{$smarty.session.s_type}.php?ctg=statistics&option=user&sel_user={$user.login}"><img class = "handle" src = "images/16x16/reports.png" title = "{$smarty.const._STATISTICS}" alt = "{$smarty.const._STATISTICS}" /></a>
							{/if}
					        {if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
					                <a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink"><img class = "handle" src = "images/16x16/edit.png" title = "{$smarty.const._EDIT}" alt = "{$smarty.const._EDIT}" /></a>
					                {if $smarty.session.s_login != $user.login}
					                	{*<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._ARCHIVEENTITY}" alt = "{$smarty.const._ARCHIVEENTITY}" onclick = "archiveUser(this, '{$user.login}')"/>*} {*DOES NOT WORK*}
										{*<a href = "{$smarty.session.s_type}.php?ctg=users&op=users_data&delete_user={$user.login}" onclick = "return confirm('{$smarty.const._AREYOUSUREYOUWANTTOFIREEMPLOYEE}')" class = "deleteLink"><img border = "0" src = "images/16x16/error_delete.png" title = "{$smarty.const._FIRE}" alt = "{$smarty.const._FIRE}" /></a>*}
									{/if}
							{/if}
					            </td>
					        </tr>


	                        {/foreach}
	                    </table>
	                    <br/>
	                {/if}

	            	<table width="100%">
	            		<tr>
	            			<td align="left" nowrap><table class = "statisticsTools"><tr><td>{$smarty.const._PARTICIPATIONOFGROUPUSERSINCOURSES}</td></tr></table></td>
	            			{*
	            			<td width="100%" align="right">{$smarty.const._COUNTONLYGROUPUSERS}</td>
	            			<td align="right">
	            				<input class = "inputCheckbox" type = "checkbox" onClick="showOnlyForUsers(this, 'coursesTable', 'groups');"/>
	            			</td>
	            			*}
	            		</tr>
	            	</table>
					{assign var = "courses_url" value = "`$smarty.server.PHP_SELF`?ctg=statistics&option=groups&sel_group=`$smarty.get.sel_group`&courses_to_group_users=1&"}
					{assign var = "_change_handles_" value = false}
					{if !$T_SORTED_TABLE || $T_SORTED_TABLE == 'groupUsersCourseTable'}

<!--ajax:groupUsersCourseTable-->
						<table size = "{$T_TABLE_SIZE}" sortBy = "{$T_DATASOURCE_SORT_BY}" activeFilter = "1" order = "{$T_DATASOURCE_SORT_ORDER}"  id = "groupUsersCourseTable" class = "sortedTable" useAjax = "1" url = "{$courses_url}">
							<tr class = "topTitle">
								<td class = "topTitle" name = "name" style = "width:{if $smarty.get.ctg == "users"}35%{else}60%{/if}">{$smarty.const._NAME}</td>
								<td class = "topTitle" name = "directions_ID">{$smarty.const._PARENTDIRECTIONS}</td>
								<td class = "topTitle centerAlign" name = "num_assigned">{$smarty.const._PARTICIPATION}</td>
								<td class = "topTitle centerAlign" name = "num_completed">{$smarty.const._COMPLETED}</td>
							</tr>
							{foreach name = 'users_to_courses_list' key = 'key' item = 'course' from = $T_DATA_SOURCE}
							<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"} {if !$course.active}deactivatedTableElement{/if}">
								<td>{strip}
									{if $course.has_instances}
										<img src = "images/16x16/plus.png" class = "ajaxHandle" alt = "{$smarty.const._COURSEINSTANCES}" title = "{$smarty.const._COURSEINSTANCES}" onclick = "toggleSubSection(this, '{$course.id}', 'groupUsersInstancesTable')"/>
									{/if}
									{$course.name}
								{/strip}</td>
								<td>{$T_DIRECTION_PATHS[$course.directions_ID]}</td>
								<td class="centerAlign">{$course.num_assigned}</td>
								<td class="centerAlign">{$course.num_completed}</td>
							</tr>
							{foreachelse}
							<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "10">{$smarty.const._NODATAFOUND}</td></tr>
							{/foreach}
						</table>

<!--/ajax:groupUsersCourseTable-->

						{/if}

						{if !$T_SORTED_TABLE || $T_SORTED_TABLE == 'groupUsersInstancesTable'}
					<div id = "filemanager_div" style = "display:none;">
<!--ajax:groupUsersInstancesTable-->
						<table size = "{$T_TABLE_SIZE}" sortBy = "{$T_DATASOURCE_SORT_BY}"  order = "{$T_DATASOURCE_SORT_ORDER}"  id = "groupUsersInstancesTable" class = "sortedTable subSection" no_auto = "1" useAjax = "1" url = "{$courses_url}">
							<tr class = "topTitle">
								<td class = "topTitle" name = "name" style = "width:{if $smarty.get.ctg == "users"}35%{else}60%{/if}">{$smarty.const._NAME}</td>
								<td class = "topTitle" name = "directions_ID" style = "width:30%">{$smarty.const._PARENTDIRECTIONS}</td>
								<td class = "topTitle centerAlign" name = "num_assigned">{$smarty.const._PARTICIPATION}</td>
								<td class = "topTitle centerAlign" name = "num_completed">{$smarty.const._COMPLETED}</td>
							</tr>
							{foreach name = 'users_to_courses_list' key = 'key' item = 'course' from = $T_DATA_SOURCE}
								<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"} {if !$course.active}deactivatedTableElement{/if}">
								<td>{$course.name}</td>
								<td>{$T_DIRECTION_PATHS[$course.directions_ID]}</td>
								<td class="centerAlign">{$course.num_assigned}</td>
								<td class="centerAlign">{$course.num_completed}</td>
							</tr>
							{foreachelse}
							<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "10">{$smarty.const._NODATAFOUND}</td></tr>
							{/foreach}
						</table>
<!--/ajax:groupUsersInstancesTable-->
					</div>
						{/if}


	            </div>

			{if $T_GROUP_INFO.lessons_count || $T_LESSONS_SIZE}
	            <div class = "statisticsDiv tabbertab {if (isset($smarty.get.tab) &&  $smarty.get.tab == 'lessons')} tabbertabdefault{/if}" title = "{$smarty.const._GROUPLESSONS}">
	            	<table width="100%">
	            		<tr>
	            			<td width="100%" align="right">{$smarty.const._COUNTONLYGROUPUSERS}</td>
	            			<td align="right">
	            				<input class = "inputCheckbox" type = "checkbox" onClick="showOnlyForUsers(this, 'lessonsTable', 'groups');"/>
	            			</td>
	            		</tr>
	            	</table>
<!--ajax:lessonsTable-->
	                <table style = "width:100%" class = "sortedTable" size = "{$T_LESSONS_SIZE}" sortBy = "0" useAjax = "1" id = "lessonsTable" rowsPerPage = "20" url = "{$smarty.session.s_type}.php?ctg=statistics&option=groups&sel_group={$smarty.get.sel_group}&">
	                    <tr class = "topTitle">
	                        <td class = "topTitle" name = "name">{$smarty.const._NAME} </td>
	                        <td class = "topTitle" name = "direction_name">{$smarty.const._CATEGORY}</td>
	                        <td class = "topTitle" name = "languages_NAME">{$smarty.const._LANGUAGE}</td>
	                        <td class = "topTitle centerAlign" name = "students">{$smarty.const._PARTICIPATION}</td>
	                        <td class = "topTitle centerAlign" name = "course_only">{$smarty.const._AVAILABLE}</td>
	                    {if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
	                        <td class = "topTitle centerAlign" name ="skills_offered">{$smarty.const._SKILLS}</td>
	                    {/if} {* #cpp#endif *}
	                        <td class = "topTitle centerAlign" name = "price">{$smarty.const._PRICE}</td>
	                        <td class = "topTitle" name = "created">{$smarty.const._CREATED}</td>
	                        {*
	                        <td class = "topTitle centerAlign" name = "active">{$smarty.const._ACTIVE2}</td>
	                        *}
	                        <td class = "topTitle noSort centerAlign">{$smarty.const._OPERATIONS}</td>
	                    </tr>
	    {foreach name = 'lessons_list2' key = 'key' item = 'lesson' from = $T_LESSONS}
	                    <tr id = "row_{$lesson.id}" class = "{cycle values = "oddRowColor, evenRowColor"} {if !$lesson.active}deactivatedTableElement{/if}">
	                        <td id = "column_{$lesson.id}" class = "editLink">
									<a href = "{$smarty.server.PHP_SELF}?ctg=lessons&edit_lesson={$lesson.id}" class = "info" url = "ask_information.php?lessons_ID={$lesson.id}&type=lesson">{$lesson.name}
	            						<span class = "tooltipSpan"></span>
	            					</a>
	                        </td>
	                        <td>{$lesson.direction_name}</td>
	                        <td>{$lesson.languages_NAME}</td>
	                        <td class = "centerAlign">{if $lesson.max_users}{$lesson.students}/{$lesson.max_users}{else}{$lesson.students}{/if}</td>
	                        <td class = "centerAlign">
	                    {if $lesson.course_only}
	                            <img class = "handle" src = "images/16x16/courses.png"     alt = "{$smarty.const._COURSEONLY}" title = "{$smarty.const._COURSEONLY}" {if $change_lessons}onclick = "setLessonAccess(this, '{$lesson.id}')"{/if}>
	                    {else}
	                            <img class = "handle" src = "images/16x16/lessons.png" alt = "{$smarty.const._DIRECTLY}"   title = "{$smarty.const._DIRECTLY}"   {if $change_lessons}onclick = "setLessonAccess(this, '{$lesson.id}')"{/if}>
	                    {/if}
	                        </td>
	                    {if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
	                        <td class = "centerAlign">{if $lesson.skills_offered == 0}{$smarty.const._NONESKILL}{else}{$lesson.skills_offered}{/if}</td>
	                    {/if} {* #cpp#endif *}
	                        <td class = "centerAlign">{$lesson.price_string}</td>
	                        <td>#filter:timestamp-{$lesson.created}#</td>
	                        {*
	                        <td class = "centerAlign">

	                    {if $lesson.active == 1}
	                            <img class = "ajaxHandle" src = "images/16x16/trafficlight_green.png" alt = "{$smarty.const._DEACTIVATE}" title = "{$smarty.const._DEACTIVATE}" {if $change_lessons}onclick = "activateLesson(this, '{$lesson.id}');"{/if}>
	                    {else}
	                            <img class = "ajaxHandle" src = "images/16x16/trafficlight_red.png"   alt = "{$smarty.const._ACTIVATE}"   title = "{$smarty.const._ACTIVATE}"   {if $change_lessons}onclick = "activateLesson(this, '{$lesson.id}')"{/if}>
	                    {/if}
	                        </td>
	                        *}
	                        <td class = "centerAlign" style = "white-space:nowrap">
	                    {if !isset($T_CURRENT_USER->coreAccess.statistics) || $T_CURRENT_USER->coreAccess.statistics != 'hidden'}
	                            <a href="administrator.php?ctg=statistics&option=lesson&tab=overall&sel_lesson={$lesson.id}"><img src = "images/16x16/reports.png" alt = "{$smarty.const._STATISTICS}" title = "{$smarty.const._STATISTICS}" border = "0"></a>
	                    {/if}
	                            <a href = "administrator.php?ctg=lessons&lesson_settings={$lesson.id}"><img border = "0" src = "images/16x16/generic.png" title = "{$smarty.const._LESSONSETTINGS}" alt = "{$smarty.const._LESSONSETTINGS}" /></a>
	                            <a href = "administrator.php?ctg=lessons&lesson_info={$lesson.id}"><img border = "0" src = "images/16x16/information.png" title = "{$smarty.const._LESSONINFORMATION}" alt = "{$smarty.const._LESSONINFORMATION}" /></a>
	                    {if $change_lessons}
	                            <a href = "administrator.php?ctg=lessons&edit_lesson={$lesson.id}"><img border = "0" src = "images/16x16/edit.png" title = "{$smarty.const._EDIT}" alt = "{$smarty.const._EDIT}" /></a>
	                            <img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._DELETE}" alt = "{$smarty.const._DELETE}" onclick = "if (confirm('{$smarty.const._AREYOUSUREYOUWANTTODELETELESSON}')) deleteLesson(this, '{$lesson.id}')"/>
	                    {/if}
	                        </td>
	                    </tr>
	    		{/foreach}
	                </table>
<!--/ajax:lessonsTable-->

	            </div>
			{/if}


	            <div class = "statisticsDiv tabbertab {if (isset($smarty.get.tab) &&  $smarty.get.tab == 'courses')} tabbertabdefault{/if}" title = "{$smarty.const._GROUPCOURSES}">
	            	<table width="100%">
	            		<tr>
	            			<td align="left" nowrap>{$smarty.const._PARTICIPATIONINTHECOURSESOFTHEGROUP}</td>
	            			<td width="100%" align="right">{$smarty.const._COUNTONLYGROUPUSERS}</td>
	            			<td align="right">
	            				<input class = "inputCheckbox" type = "checkbox" onClick="showOnlyForUsers(this, 'coursesTable', 'groups');"/>
	            			</td>
	            		</tr>
	            	</table>


					{assign var = "courses_url" value = "`$smarty.server.PHP_SELF`?ctg=statistics&option=groups&sel_group=`$smarty.get.sel_group`&"}
					{assign var = "_change_handles_" value = false}

					{if !isset($smarty.get.courses_to_group_users)}
					{include file = "includes/common/courses_list.tpl"}
					{/if}
	            </div>


	                <div class = "statisticsDiv tabbertab {if (isset($smarty.get.tab) &&  $smarty.get.tab == 'grouptraffic')} tabbertabdefault{/if}" title = "{$smarty.const._USERSTRAFFIC}">
	                    <form name = "period">
	                    <table class = "statisticsSelectDate">
	                    <!--      <tr><td class = "labelCell">{$smarty.const._SETPERIOD}:&nbsp;</td>
	                        	<td class = "elementCell">
	                        		<select id="predefined_periods" onChange="setPeriod(this)">
	                        			{foreach name = 'predefined_periods' key = "id" item = "period" from = $T_PREDEFINED_PERIODS}
	                        				<option value = "{$period.value}" {if $smarty.get.predefined !="" && $smarty.get.predefined == $period.value}selected{/if}>{$period.name}</option>
	                        			{/foreach}
	                        		</select>
	                        		</td></tr>  -->
	                        <tr><td class = "labelCell">{$smarty.const._FROM}:&nbsp;</td>
	                            <td class = "elementCell">{eF_template_html_select_date prefix = "from_" time = $T_FROM_TIMESTAMP start_year = "-5" end_year = "+0" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="from_" time = $T_FROM_TIMESTAMP display_seconds = false}</td></tr>
	                        <tr><td class = "labelCell">{$smarty.const._TO}:&nbsp;</td>
	                            <td class = "elementCell">{eF_template_html_select_date prefix = "to_"   time = $T_TO_TIMESTAMP   start_year = "-5" end_year = "+0" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="to_"   time = $T_TO_TIMESTAMP   display_seconds = false}</td></tr>
	                        {*<tr><td class = "labelCell">{$smarty.const._ANALYTICLOG}:</td>
	                        	<td class = "elementCell"><input class = "inputCheckbox" type = checkbox id = "showLog" {if (isset($T_USER_LOG))}checked{/if}></td></tr>*}
	                        <tr><td class = "labelCell">{$smarty.const._ONLYFORGROUPLESSONS}:</td>
	                        	<td class = "elementCell"><input class = "inputCheckbox" type = checkbox id = "showOnlyGroupLessons" {if $smarty.get.showOnlyGroupLessons == "true"}checked{/if}></td></tr>
	                        <tr><td class = "labelCell"></td>
	                            <td class = "elementCell"><a href = "javascript:void(0)" onclick = "showStats('day')">{$smarty.const._LAST24HOURS}</a> - <a href = "javascript:void(0)" onclick = "showStats('week')">{$smarty.const._LASTWEEK}</a> - <a href = "javascript:void(0)" onclick = "showStats('month')">{$smarty.const._LASTMONTH}</a></td></tr>

							<tr><td></td>
	                            <td class = "elementCell"><input type = "button" class = "flatButton" value = "{$smarty.const._SHOW}" onclick = "document.location='{$T_BASIC_TYPE}.php?ctg=statistics&option=groups&sel_group={$smarty.get.sel_group}&tab=grouptraffic&from_year='+document.period.from_Year.value+'&from_month='+document.period.from_Month.value+'&from_day='+document.period.from_Day.value+'&from_hour='+document.period.from_Hour.value+'&from_min='+document.period.from_Minute.value+'&to_year='+document.period.to_Year.value+'&to_month='+document.period.to_Month.value+'&to_day='+document.period.to_Day.value+'&to_hour='+document.period.to_Hour.value+'&to_min='+document.period.to_Minute.value+'&showOnlyGroupLessons='+document.period.showOnlyGroupLessons.checked"></td>
	                        </tr>
						</table>
	                    </form>


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
	                    <table class = "sortedTable">
	                        <tr>
	                            <td class = "topTitle">{$smarty.const._LESSON}</td>
	                            <td class = "topTitle centerAlign">{$smarty.const._ACCESSNUMBER}</td>
	                            <td class = "topTitle centerAlign">{$smarty.const._TOTALACCESSTIME}</td>
	                            {*<td class = "topTitle noSort centerAlign">{$smarty.const._OPTIONS}</td>*}
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
	                    <br/>
	                  {if isset($T_USER_LOG)}
	                    <table class = "statisticsTools">
	                        <tr><td>{$smarty.const._ANALYTICLOG}</td></tr>
	                    </table>
	                    <table>
	                    	<tr>
	                            <td class = "topTitle">{$smarty.const._LESSON}</td>
	                            <td class = "topTitle">{$smarty.const._UNIT}</td>
	                            <td class = "topTitle">{$smarty.const._ACTION}</td>
	                            <td class = "topTitle">{$smarty.const._TIME}</td>
	                            <td class = "topTitle">{$smarty.const._IPADDRESS}</td>
	                        </tr>
	                    {foreach name = 'user_log_loop' key = "key" item = "info" from = $T_USER_LOG}
	                        <tr class = "{cycle name = 'user_log_list' values = 'oddRowColor, evenRowColor'}">
	                            <td>{$info.lesson_name}</td>
	                            <td>{$info.content_name}</td>
	                            <td>{$T_ACTIONS[$info.action]}</td>
	                            <td>#filter:timestamp_time-{$info.timestamp}#</td>
	                            <td>{$info.session_ip|eF_decodeIp}</td>
	                        </tr>
						{foreachelse}
							<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	                    </table>
	                {/if}
	                </div>



	                <div class = "statisticsDiv tabbertab {if (isset($smarty.get.tab) &&  $smarty.get.tab == 'grouplessontraffic')} tabbertabdefault{/if}" title = "{$smarty.const._LESSONSTRAFFIC}">
	                    <form name = "systemperiod">
	                    <table class = "statisticsSelectDate">
	                    <!--      <tr><td class = "labelCell">{$smarty.const._SETPERIOD}:&nbsp;</td>
	                        	<td class = "elementCell">
	                        		<select id="predefined_periods" onChange="setPeriod(this)">
	                        			{foreach name = 'predefined_periods' key = "id" item = "period" from = $T_PREDEFINED_PERIODS}
	                        				<option value = "{$period.value}" {if $smarty.get.predefined !="" && $smarty.get.predefined == $period.value}selected{/if}>{$period.name}</option>
	                        			{/foreach}
	                        		</select>
	                        		</td></tr>  -->
	                        <tr><td class = "labelCell">{$smarty.const._FROM}:&nbsp;</td>
	                            <td class = "elementCell">{eF_template_html_select_date prefix = "from_" time = $T_FROM_TIMESTAMP start_year = "-5" end_year = "+0" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="from_" time = $T_FROM_TIMESTAMP display_seconds = false}</td></tr>
	                        <tr><td class = "labelCell">{$smarty.const._TO}:&nbsp;</td>
	                            <td class = "elementCell">{eF_template_html_select_date prefix = "to_"   time = $T_TO_TIMESTAMP   start_year = "-5" end_year = "+0" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="to_"   time = $T_TO_TIMESTAMP   display_seconds = false}</td></tr>
	                        {*<tr><td class = "labelCell">{$smarty.const._ANALYTICLOG}:</td>
	                        	<td class = "elementCell"><input class = "inputCheckbox" type = checkbox id = "showLog" {if (isset($T_USER_LOG))}checked{/if}></td></tr>*}
	                        <tr><td class = "labelCell">{$smarty.const._ONLYFORGROUPUSERS}:</td>
	                        	<td class = "elementCell"><input class = "inputCheckbox" type = checkbox id = "showOnlyGroupUsers" {if $smarty.get.showOnlyGroupUsers == "true"}checked{/if}></td></tr>
	                        <tr><td class = "labelCell"></td>
	                            <td class = "elementCell"><a href = "javascript:void(0)" onclick = "showSystemStats('day')">{$smarty.const._LAST24HOURS}</a> - <a href = "javascript:void(0)" onclick = "showSystemStats('week')">{$smarty.const._LASTWEEK}</a> - <a href = "javascript:void(0)" onclick = "showSystemStats('month')">{$smarty.const._LASTMONTH}</a></td></tr>

							<tr><td></td>
	                            <td class = "elementCell"><input type = "button" class = "flatButton" value = "{$smarty.const._SHOW}" onclick = "document.location='{$T_BASIC_TYPE}.php?ctg=statistics&option=groups&sel_group={$smarty.get.sel_group}&tab=grouplessontraffic&from_year='+document.systemperiod.from_Year.value+'&from_month='+document.systemperiod.from_Month.value+'&from_day='+document.systemperiod.from_Day.value+'&from_hour='+document.systemperiod.from_Hour.value+'&from_min='+document.systemperiod.from_Minute.value+'&to_year='+document.systemperiod.to_Year.value+'&to_month='+document.systemperiod.to_Month.value+'&to_day='+document.systemperiod.to_Day.value+'&to_hour='+document.systemperiod.to_Hour.value+'&to_min='+document.systemperiod.to_Minute.value+'&showOnlyGroupUsers='+document.systemperiod.showOnlyGroupUsers.checked"></td>
	                        </tr>
						</table>
	                    </form>



	                    {if $T_LESSON_TRAFFIC.total_access > 0}

	                    <table class = "statisticsGeneralInfo">
	                        <tr><td class = "topTitle" colspan = "2">{$smarty.const._GROUPLESSONTRAFFIC}</td></tr>
	                        <tr class = "oddRowColor">
	                            <td class = "labelCell">{$smarty.const._TOTALACCESS}:</td>
	                            <td class = "elementCell">{$T_LESSON_TRAFFIC.total_access}</td>
	                        </tr>
	                        <tr class = "evenRowColor">
	                            <td class = "labelCell">{$smarty.const._TOTALACCESSTIME}: </td>
	                            <td class = "elementCell">
	                                {if $T_LESSON_TRAFFIC.total_seconds}
	                                	{if $T_LESSON_TRAFFIC.total_time.hours}{$T_LESSON_TRAFFIC.total_time.hours}{$smarty.const._HOURSSHORTHAND} {/if}
	                                	{if $T_LESSON_TRAFFIC.total_time.minutes}{$T_LESSON_TRAFFIC.total_time.minutes}{$smarty.const._MINUTESSHORTHAND} {/if}
	                                	{if $T_LESSON_TRAFFIC.total_time.seconds}{$T_LESSON_TRAFFIC.total_time.seconds}{$smarty.const._SECONDSSHORTHAND}{/if}
	                                {else}
	                                	{$smarty.const._NOACCESSDATA}
	                                {/if}
	                            </td>
	                        </tr>
	                    </table>
	                    {/if}

						<br/>
	                    <table class = "statisticsTools">
	                        <tr><td>{$smarty.const._ACCESSNUMBER}</td>
	                    {if $T_LESSON_TRAFFIC.total_seconds > 0 }

	                    {/if}
	                    	</tr>
	                    </table>
	                    <table class = "sortedTable">
	                        <tr>
	                            <td class = "topTitle">{$smarty.const._LOGIN}</td>
	                            <td class = "topTitle centerAlign">{$smarty.const._ACCESSNUMBER}</td>
	                            <td class = "topTitle centerAlign">{$smarty.const._TOTALACCESSTIME}</td>
	                            {*<td class = "topTitle noSort centerAlign">{$smarty.const._OPTIONS}</td>*}
	                        </tr>
	                        {foreach name = 'user_traffic_list' key = "login" item = "info" from = $T_LESSON_TRAFFIC.users}
	                        	{if $info.accesses}
	                            <tr class = "{cycle name = 'usertraffic' values = 'oddRowColor, evenRowColor'} {if !$info.active}deactivatedTableElement{/if}">
	                                <td><a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$login}">#filter:login-{$login}#</a></td>
	                                <td class = "centerAlign">{$info.accesses}</td>
	                                <td class = "centerAlign">{strip}<span style = "display:none">{$info.total_seconds}&nbsp;</span>
	                                    {if $info.total_seconds}
		                                	{if $info.hours}{$info.hours}{$smarty.const._HOURSSHORTHAND} {/if}
		                                	{if $info.minutes}{$info.minutes}{$smarty.const._MINUTESSHORTHAND} {/if}
		                                	{if $info.seconds}{$info.seconds}{$smarty.const._SECONDSSHORTHAND}{/if}
	                                    {else}
	                                    	{$smarty.const._NOACCESSDATA}
	                                    {/if}
	                                {/strip}</td>

	                            </tr>
	                            {/if}
	                        {foreachelse}
	                        	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                        {/foreach}
	                    </table>
	                </div>

			</div>
	    	{/if}
	    {/capture}

	    {if $T_GROUP_INFO != ""}
	    	{eF_template_printBlock title = "`$smarty.const._REPORTSFORGROUP` <span class='innerTableName'>&quot;`$T_GROUP_NAME`&quot;</span>" data = $smarty.capture.group_statistics image = '32x32/users.png' help = 'Reports'}
	    {else}
	    	{eF_template_printBlock title = $smarty.const._GROUPSTATISTICS data = $smarty.capture.group_statistics image = '32x32/users.png' help = 'Reports'}
		{/if}

{* #cpp#endif *}
{* #cpp#endif *}