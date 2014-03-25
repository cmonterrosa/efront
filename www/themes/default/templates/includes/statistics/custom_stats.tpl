{* #cpp#ifndef COMMUNITY *}
{* #cpp#ifndef STANDARD *}
		{if $smarty.get.query == "system_registered"}
			{capture name = 'system_registered'}
			<table class = "sortedTable" sortBy = "0" width="100%">
	                        <tr>
	                            <td class = "topTitle">{$smarty.const._LOGIN}</td>
								<td class = "topTitle">{$smarty.const._LASTNAME}</td>
								<td class = "topTitle">{$smarty.const._FIRSTNAME}</td>
								<td class = "topTitle">{$smarty.const._EMAIL}</td>
	                            <td class = "topTitle">{$smarty.const._REGISTERED}</td>
	                        </tr>
	                    {foreach name = 'user_list' key = 'key' item = "item" from = $T_SYSTEM_REGISTERED}
	                        <tr class = "{cycle name = 'user_list' values = 'oddRowColor, evenRowColor'}">
	                            <td><a class="editLink" href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$item.login}">#filter:login-{$item.login}#</a></td>
								<td>{$item.surname}</td>
								<td>{$item.name}</td>
								<td>{$item.email}</td>
	                            <td>#filter:timestamp_time-{$item.timestamp}#</td>
	                        </tr>
	                    {foreachelse}
	                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	        </table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTUSERSREGISTEREDSYSTEM` [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.system_registered image = '32x32/custom_reports.png' options=$T_SYSTEM_REGISTERED_OPTIONS}
		{elseif $smarty.get.query == "test_completed"}
			{capture name = 'test_completed'}
			{foreach name = 'question_list' key = "login" item = "test" from = $T_TEST_COMPLETED}
				{if $login != "average_score"}
	                <table width="100%" class = "sortedTable" style = "margin-bottom:10px">
	                    <tr>
	                        <td class = "topTitle" style = "width:30%">{$smarty.const._USER}</td>
	                        <td class = "topTitle centerAlign" style = "width:20%">{$smarty.const._DATE}</td>
	                        <td class = "topTitle centerAlign" style = "width:20%">{$smarty.const._STATUS}</td>
	                        <td class = "topTitle centerAlign" style = "width:20%">{$smarty.const._SCORE}</td>
						</tr>
					{foreach name = 'question_list' key = "id" item = "completed_test" from = $test}

							{if $id|@is_numeric}
							<tr class = "{cycle name = 'test_questions' values = 'oddRowColor, evenRowColor'}">
								<td>#filter:login-{$completed_test.users_LOGIN}#</td>
								<td class = "centerAlign">#filter:timestamp_time-{$completed_test.timestamp}#</td>
								<td class = "centerAlign">{if $completed_test.status == 'failed'}<img src = "images/16x16/close.png" alt = "{$smarty.const._FAILED}" title = "{$smarty.const._FAILED}" style = "vertical-align:middle">{else if $completed_test == 'passed' || $completed_test == 'completed'}<img src = "images/16x16/success.png" alt = "{$smarty.const._PASSED}" title = "{$smarty.const._PASSED}" style = "vertical-align:middle">{/if}</td>
								<td class = "centerAlign">#filter:score-{$T_TEST_COMPLETED.$login.scores[$id]}#%</td>
							</tr>
							{/if}

	                {foreachelse}
	                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                {/foreach}
	           {/if}
					</table>
			{foreachelse}
	             <table width="100%" class = "sortedTable" style = "margin-bottom:10px">
				 <tr>
	                        <td class = "topTitle" style = "width:30%">{$smarty.const._USER}</td>
	                        <td class = "topTitle centerAlign" style = "width:20%">{$smarty.const._DATE}</td>
	                        <td class = "topTitle centerAlign" style = "width:20%">{$smarty.const._STATUS}</td>
	                        <td class = "topTitle centerAlign" style = "width:20%">{$smarty.const._SCORE}</td>
				</tr>
				<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	            </table>
			{/foreach}
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTUSERSCOMPLETEDTEST` <span class=\"innerTableName\"> &quot;`$T_TEST_NAME`&quot;</span> [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.test_completed image = '32x32/custom_reports.png' options=$T_TEST_COMPLETED_OPTIONS}
		{elseif $smarty.get.query == "project_submitted"}
			{capture name = 'project_submitted'}
			<table class = "sortedTable" sortBy = "0" width="100%">
	                        <tr>
	                            <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._DATE}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._GRADE}</td>
	                        </tr>
	                    {foreach name = 'project_list' key = 'key' item = "item" from = $T_PROJECT_SUBMITTED}
						   <tr class = "{cycle name = 'project_list' values = 'oddRowColor, evenRowColor'}">
	                            <td><a class="editLink" href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$item.users_LOGIN}">#filter:login-{$item.users_LOGIN}#</a></td>
								<td align="center">#filter:timestamp_time-{$item.upload_timestamp}#</td>
								<td align="center">{$item.grade}</td>
	                        </tr>
	                    {foreachelse}
	                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	        </table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTUSERSSUBMITTEDPROJECT` <span class=\"innerTableName\"> &quot;`$T_PROJECT_NAME`&quot;</span> [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.project_submitted image = '32x32/custom_reports.png' options=$T_PROJECT_SUBMITTED_OPTIONS}


		{elseif $smarty.get.query == "course_certificated"}
			{capture name = 'course_certificated'}
			<table class = "sortedTable" sortBy = "0" width="100%">
	                        <tr>
	                            <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._DATE}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._GRADE}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._CERTIFICATEEXPIRESON}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._CERTIFICATEKEY}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._RESET}</td>
								<td class = "topTitle" align="center" style = "width:100px">{$smarty.const._PREVIEW}</td>
	                        </tr>
	                    {foreach name = 'users_list' key = 'key' item = "item" from = $T_COURSE_CERTIFICATED}
						   <tr class = "{cycle name = 'users_list' values = 'oddRowColor, evenRowColor'}">
	                            <td><a class="editLink" href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$item.login}">#filter:login-{$item.login}#</a></td>
								<td align="center">#filter:timestamp_time-{$item.date}#</td>
								<td align="center">{$item.grade}</td>
								<td align="center">#filter:timestamp_time_nosec-{$item.expire_certificate}#</td>
								<td align="center">{$item.serial_number}</td>
								<td align="center">{if $item.reset == 1} {$smarty.const._YES}{else}{$smarty.const._NO} {/if}</td>
								<td align="center"> <a href = "{$smarty.server.PHP_SELF}?ctg=courses&op=course_certificates&export=rtf&user={$item.login}&course={$smarty.post.course_id_certificated}" target="_blank" title = "{$smarty.const._VIEWCERTIFICATE}">
	                                <img src = "images/16x16/search.png" title = "{$smarty.const._VIEWCERTIFICATE}" alt = "{$smarty.const._VIEWCERTIFICATE}" border = "0"/></a>
								</td>
	 					    </tr>
	                    {foreachelse}
	                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	        </table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTUSERSCERTIFICATEDCOURSE` <span class=\"innerTableName\"> &quot;`$T_COURSE_NAME`&quot;</span> [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.course_certificated image = '32x32/custom_reports.png' options=$T_COURSE_CERTIFICATED_OPTIONS}

		{elseif $smarty.get.query == "course_completed"}
			{capture name = 'course_completed'}
			<table class = "sortedTable" sortBy = "0" width="100%">
	                        <tr>
	                            <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._DATE}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._GRADE}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._COMMENTS}</td>
	                        </tr>
	                    {foreach name = 'users_list' key = 'key' item = "item" from = $T_COURSE_COMPLETED}
						   <tr class = "{cycle name = 'users_list' values = 'oddRowColor, evenRowColor'}">
	                            <td><a class="editLink" href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$item.users_LOGIN}">#filter:login-{$item.users_LOGIN}#</a></td>
								<td align="center">#filter:timestamp_time-{$item.to_timestamp}#</td>
								<td align="center">{$item.score}</td>
								<td align="center">{$item.comments}</td>
	                        </tr>
	                    {foreachelse}
	                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	        </table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTUSERSCOMPLETEDCOURSE` <span class=\"innerTableName\"> &quot;`$T_COURSE_NAME`&quot;</span> [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.course_completed image = '32x32/custom_reports.png' options=$T_COURSE_COMPLETED_OPTIONS}

		{elseif $smarty.get.query == "lesson_completed"}
			{capture name = 'lesson_completed'}
			<table class = "sortedTable" sortBy = "0" width="100%">
	                        <tr>
	                            <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._DATE}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._GRADE}</td>
	                        </tr>
	                    {foreach name = 'users_list' key = 'key' item = "item" from = $T_LESSON_COMPLETED}
						   <tr class = "{cycle name = 'users_list' values = 'oddRowColor, evenRowColor'}">
	                            <td><a class="editLink" href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$item.users_LOGIN}">#filter:login-{$item.users_LOGIN}#</a></td>
								<td align="center">#filter:timestamp_time-{$item.to_timestamp}#</td>
								<td align="center">{$item.score}</td>
	                        </tr>
	                    {foreachelse}
	                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	        </table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTUSERSCOMPLETEDLESSON` <span class=\"innerTableName\"> &quot;`$T_LESSON_NAME`&quot;</span> [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.lesson_completed image = '32x32/custom_reports.png' options=$T_LESSON_COMPLETED_OPTIONS}
		{elseif $smarty.get.query == "course_enrolled"}
			{capture name = 'course_enrolled'}
			<table class = "sortedTable" sortBy = "0" width="100%">
	                        <tr>
	                            <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._TYPE}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._DATE}</td>
	                        </tr>
	                    {foreach name = 'users_list' key = 'key' item = "item" from = $T_COURSE_ENROLLED}
						   <tr class = "{cycle name = 'users_list' values = 'oddRowColor, evenRowColor'}">
	                            <td><a class="editLink" href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$item.login}">#filter:login-{$item.login}#</a></td>
								<td align="center">{$T_ROLES[$item.user_type]}</td>
								<td align="center">#filter:timestamp_time-{$item.active_in_course}#</td>
	                        </tr>
	                    {foreachelse}
	                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	        </table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTUSERSENROLLEDCOURSE` <span class=\"innerTableName\"> &quot;`$T_COURSE_NAME`&quot;</span> [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.course_enrolled image = '32x32/custom_reports.png' options=$T_COURSE_ENROLLED_OPTIONS}

		{elseif $smarty.get.query == "lesson_enrolled"}
			{capture name = 'lesson_enrolled'}
			<table class = "sortedTable" sortBy = "0" width="100%">
	                        <tr>
	                            <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._TYPE}</td>
								<td class = "topTitle" align="center" style = "width:300px">{$smarty.const._DATE}</td>
	                        </tr>
	                    {foreach name = 'users_list' key = 'key' item = "item" from = $T_LESSON_ENROLLED}
						   <tr class = "{cycle name = 'users_list' values = 'oddRowColor, evenRowColor'}">
	                            <td><a class="editLink" href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$item.login}">#filter:login-{$item.login}#</a></td>
								<td align="center">{$T_ROLES[$item.role]}</td>
								<td align="center">#filter:timestamp_time-{$item.from_timestamp}#</td>
	                        </tr>
	                    {foreachelse}
	                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	        </table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTUSERSENROLLEDLESSON` <span class=\"innerTableName\"> &quot;`$T_LESSON_NAME`&quot;</span> [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.lesson_enrolled image = '32x32/custom_reports.png' options=$T_LESSON_ENROLLED_OPTIONS}

		{elseif $smarty.get.query == "active_users"}
			{capture name = 'active_users'}
				<table class = "sortedTable" width="100%">
	                        <tr>
	                            <td style = "width:40%;" class = "topTitle">{$smarty.const._USER}</td>
	                            <td style = "width:30%;" class = "topTitle centerAlign">{$smarty.const._ACCESSNUMBER}</td>
	                            <td style = "width:30%;" class = "topTitle centerAlign">{$smarty.const._TOTALACCESSTIME}</td>
	                         </tr>
	                        {foreach name='active_users'  key = "login" item = "info" from=$T_ACTIVE_USERS}
	                            <tr class = "{cycle name = 'active_users' values = 'oddRowColor, evenRowColor'} {if !$info.active}deactivatedTableElement{/if}">
	                                <td><a class="editLink" href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$login}">#filter:login-{$login}#</a></td>
	                                <td class = "centerAlign">{$info.accesses}</td>
	                                <td class = "centerAlign">{strip}
	                                	<span style = "display:none">{$info.seconds}&nbsp;</span>
	                                    {if $info.seconds}
	                                    	{if $info.time.hours}{$info.time.hours}{$smarty.const._HOURSSHORTHAND} {/if}
	                                    	{if $info.time.minutes}{$info.time.minutes}{$smarty.const._MINUTESSHORTHAND} {/if}
	                                    	{if $info.time.seconds}{$info.time.seconds}{$smarty.const._SECONDSSHORTHAND}{/if}
	                                    {else}
	                                    	{$smarty.const._NOACCESSDATA}
	                                    {/if}
	                                {/strip}</td>
	                            </tr>
	                        {/foreach}
				</table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTMOSTACTIVEUSERS` [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.active_users image = '32x32/custom_reports.png' options=$T_ACTIVE_USERS_OPTIONS}
		{elseif $smarty.get.query == "active_lessons"}
			{capture name = 'active_lessons'}
				<table class = "sortedTable" width="100%">
	                        <tr>
	                            <td style = "width:40%;" class = "topTitle">{$smarty.const._LESSON}</td>
	                            <td style = "width:30%;" class = "topTitle centerAlign">{$smarty.const._ACCESSNUMBER}</td>
	                            <td style = "width:30%;" class = "topTitle centerAlign">{$smarty.const._TOTALACCESSTIME}</td>
	                         </tr>
	                        {foreach name='active_lessons' key = "id" item = "info" from=$T_ACTIVE_LESSONS}
	                            <tr class = "{cycle name = 'active_lessons' values = 'oddRowColor, evenRowColor'} {if !$info.active}deactivatedTableElement{/if}">
	                                <td>{$info.name}</td>
	                                <td class = "centerAlign">{$info.accesses}</td>
	                                <td class = "centerAlign">{strip}
	                                	<span style = "display:none">{$info.seconds}&nbsp;</span>
	                                    {if $info.seconds}
	                                    	{if $info.time.hours}{$info.time.hours}{$smarty.const._HOURSSHORTHAND} {/if}
	                                    	{if $info.time.minutes}{$info.time.minutes}{$smarty.const._MINUTESSHORTHAND} {/if}
	                                    	{if $info.time.seconds}{$info.time.seconds}{$smarty.const._SECONDSSHORTHAND}{/if}
	                                    {else}
	                                    	{$smarty.const._NOACCESSDATA}
	                                    {/if}
	                                {/strip}</td>
	                            </tr>
	                        {foreachelse}
	                        	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                        {/foreach}
	            </table>
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._WANTMOSTACTIVELESSONS` [#filter:timestamp-`$T_FROM_DATE`# - #filter:timestamp-`$T_TO_DATE`#]" data = $smarty.capture.active_lessons image = '32x32/custom_reports.png' options=$T_ACTIVE_LESSONS_OPTIONS}

		{else}
	    {capture name = 'custom_statistics'}

	        <table class = "statisticsSelectList">
				<tr><td>
				<a href = "javascript:void(0)" onclick = "showCustomStats('day')">{$smarty.const._LAST24HOURS}</a> - <a href = "javascript:void(0)" onclick = "showCustomStats('week')">{$smarty.const._LASTWEEK}</a> - <a href = "javascript:void(0)" onclick = "showCustomStats('month')">{$smarty.const._LASTMONTH}</a><br /><br />

				</td></tr>
		<!--	<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSAGEREPOSTLESSON}</legend>
					<form name="lesson_usage" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=lesson_usage">
					<table width="100%"><tr>
					<td align="left">
					{$smarty.const._LESSON}
					<input type = "text" id = "autocomplete_lesson_usage" class = "autoCompleteTextBox"/>
	                        <img id = "busy" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "lesson_choices" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
	                            {literal}
	                                <script language = "JavaScript" type = "text/javascript">
	                                    new Ajax.Autocompleter("autocomplete_lesson_usage", "lesson_choices", "ask.php?ask_type=lessons", {paramName: "preffix", afterUpdateElement :  getLessonId, indicator : "busy"});
	                                    function getLessonId(text, li) {
	                                    	$('lesson_id').value=li.id;
	                                    }
	                                </script>
	                            {/literal}
					<input type = "hidden" name="lesson_id" id = "lesson_id" />
					</td>
					</tr><tr>
					<td>
					{$smarty.const._FROM} {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					<input type = "submit" value = "&rarr;" />
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>

				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSAGEREPOSTCOURSE}</legend>
					<form name="course_usage" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=course_usage">
					<table width="100%"><tr>
					<td align="left">
					{$smarty.const._COURSE}
					<input type = "text" id = "autocomplete_course_usage" class = "autoCompleteTextBox"/>
	                        <img id = "busy_course_usage" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "course_choices" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
	                            {literal}
	                                <script language = "JavaScript" type = "text/javascript">
	                                    new Ajax.Autocompleter("autocomplete_course_usage", "course_choices", "ask.php?ask_type=courses", {paramName: "preffix", afterUpdateElement : getCourseId, indicator : "busy_course_usage"});
	                                    function getCourseId(text, li) {
	                                    	$('course_id').value=li.id;
	                                    }
	                                </script>
	                            {/literal}
					<input type = "hidden" name="course_id" id = "course_id" />
					</td>
					</tr><tr>
					<td>
					{$smarty.const._FROM} {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					<input type = "submit" value = "&rarr;" />
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>
		-->

				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSERSENROLLEDCOURSE}</legend>
					<form name="course_enrolled" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=course_enrolled">
					<table width="100%"><tr>
					<td align="left" width="50px">
					{$smarty.const._COURSE}
					</td><td>
					<input type = "text" id = "autocomplete_course_enrolled" class = "autoCompleteTextBox" value="{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}" onClick="this.value='';" />
	                        <img id = "busy_course_enrolled" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "course_choices_enrolled" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
					<input type = "hidden" name="course_id_enrolled" id = "course_id_enrolled" />
					</td>
					</tr><tr>
					<td>
					{$smarty.const._FROM} </td><td>{eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/>
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>

				{if $T_CONFIGURATION.lesson_enroll}
				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSERSENROLLEDLESSON}</legend>
					<form name="lesson_enrolled" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=lesson_enrolled">
					<table width="100%"><tr>
					<td align="left" width="50px">
					{$smarty.const._LESSON}
					</td><td>
					<input type = "text" id = "autocomplete_lesson_enrolled" class = "autoCompleteTextBox" value="{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}" onClick="this.value='';" />
	                        <img id = "busy_lesson_enrolled" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "lesson_choices_enrolled" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
					<input type = "hidden" name="lesson_id_enrolled" id = "lesson_id_enrolled" />
					</td>
					</tr><tr>
					<td>
						{$smarty.const._FROM} </td><td>{eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
						{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/>
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>
				{/if}

				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSERSCOMPLETEDLESSON}</legend>
					<form name="lesson_completed" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=lesson_completed">
					<table width="100%"><tr>
					<td align="left" width="50px">
					{$smarty.const._LESSON}
					</td><td>
					<input type = "text" id = "autocomplete_lesson_completed" class = "autoCompleteTextBox" value="{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}" onClick="this.value='';" />
	                        <img id = "busy_lesson_completed" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "lesson_choices_completed" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
					<input type = "hidden" name="lesson_id_completed" id = "lesson_id_completed" />
					</td>
					</tr><tr>
					<td>
					{$smarty.const._FROM}</td><td> {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit"value = "{$smarty.const._SUBMIT}"  class = "flatButton"/>
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>

				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSERSCOMPLETEDCOURSE}</legend>
					<form name="course_completed" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=course_completed">
					<table width="100%"><tr>
					<td align="left" width="50px">
					{$smarty.const._COURSE}
					</td><td>
					<input type = "text" id = "autocomplete_course_completed" class = "autoCompleteTextBox" value="{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}" onClick="this.value='';" />
	                        <img id = "busy_course_completed" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "course_choices_completed" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
					<input type = "hidden" name="course_id_completed" id = "course_id_completed" />
					</td>
					</tr><tr>
					<td>
					{$smarty.const._FROM}</td><td> {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/>
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>

				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSERSCERTIFICATEDCOURSE}</legend>
					<form name="course_certificated" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=course_certificated">
					<table width="100%"><tr>
					<td align="left" width="50px">
					{$smarty.const._COURSE}
					</td><td>
					<input type = "text" id = "autocomplete_course_certificated" class = "autoCompleteTextBox" value="{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}" onClick="this.value='';"/>
	                        <img id = "busy_course_certificated" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "course_choices_certificated" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
					<input type = "hidden" name="course_id_certificated" id = "course_id_certificated" />
					</td>
					</tr><tr>
					<td>
					{$smarty.const._FROM}</td><td> {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/>
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>
				{if 'projects'|eF_template_isOptionVisible}
					<tr><td>
					<fieldset class = "fieldsetSeparator">
						<legend>{$smarty.const._WANTUSERSSUBMITTEDPROJECT}</legend>
						<form name="project_submitted" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=project_submitted">
						<table width="100%"><tr>
						<td align="left" width="50px">
						{$smarty.const._PROJECT}
						</td><td>
						<input type = "text" id = "autocomplete_project_submitted" class = "autoCompleteTextBox" value="{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}" onClick="this.value='';"/>
								<img id = "busy_project_submitted" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
								<div id = "project_choices_submitted" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
						<input type = "hidden" name="project_id_submitted" id = "project_id_submitted" />
						</td>
						</tr><tr>
						<td>
						{$smarty.const._FROM}</td><td> {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
						{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
						</td>
						</tr><tr>
						<td></td><td>
						<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/>
						</td></tr>
						</table>
						</form>
					</fieldset>
					</td></tr>
				{/if}
				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSERSCOMPLETEDTEST}</legend>
					<form name="test_completed" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=test_completed">
					<table width="100%"><tr>
					<td align="left" width="50px">
					{$smarty.const._TEST}
					</td><td>
					<input type = "text" id = "autocomplete_test_completed" class = "autoCompleteTextBox" value="{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}" onClick="this.value='';"/>
	                        <img id = "busy_test_completed" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "test_choices_completed" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
					<input type = "hidden" name="test_id_completed" id = "test_id_completed" />
					</td>
					</tr><tr>
					<td>
					{$smarty.const._FROM}</td><td> {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/>
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>

				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTMOSTACTIVEUSERS}</legend>
					<form name="active_users" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=active_users">
					<table width="100%"><tr>
					<td align="left" width="50px">{$smarty.const._FROM} </td><td>{eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/></td>
					<td align="left">&nbsp;
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>
{*
				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTMOSTACTIVELESSONS}</legend>
					<form name="active_lessons" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=active_lessons">
					<table width="100%"><tr>
					<td align="left" width="50px">{$smarty.const._FROM}</td><td> {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/></td>
					<td align="left">&nbsp;
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>

		<!--	<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTMOSTACTIVECOURSES}</legend>
					<form name="active_courses" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=active_courses">
					<table width="100%"><tr>
					<td align="left" width="55%">{$smarty.const._FROM} {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/></td>
					<td align="left" width="45%">&nbsp;
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>
		-->
*}
				<tr><td>
				<fieldset class = "fieldsetSeparator">
					<legend>{$smarty.const._WANTUSERSREGISTEREDSYSTEM}</legend>
					<form name="system_registered" method="post" action="{$smarty.server.PHP_SELF}?ctg=statistics&option=custom&query=system_registered">
					<table width="100%"><tr>
					<td align="left" width="50px">{$smarty.const._FROM}</td><td> {eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					{$smarty.const._TO}	{eF_template_html_select_date prefix="to_" time=$T_TO_TIMESTAMP start_year="-45" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
					</td>
					</tr><tr>
					<td></td><td>
					<input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton"/></td>
					<td align="left">&nbsp;
					</td></tr>
					</table>
					</form>
				</fieldset>
				</td></tr>
			</table>
		{/capture}
		{eF_template_printBlock title = $smarty.const._CUSTOMSTATISTICS data = $smarty.capture.custom_statistics image = '32x32/custom_reports.png' help = 'Reports'}
		{/if}

{* #cpp#endif *}
{* #cpp#endif *}