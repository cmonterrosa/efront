    {capture name='course_statistics'}
            <table class = "statisticsTools statisticsSelectList">
                <tr><td class = "labelCell">{$smarty.const._CHOOSECOURSE}:</td>
                    <td class = "elementCell" colspan = "4">
                        <input type = "text" id = "autocomplete" class = "autoCompleteTextBox"/>
                        <img id = "busy" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
                        <div id = "autocomplete_courses" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
                <tr><td></td>
                	<td class = "infoCell" colspan = "4">{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td></tr>
    {if !isset($T_CURRENT_COURSE)}
    		</table>
	{else}
				<tr>
                 	{include file = "includes/statistics/stats_filters.tpl"}

                	<td id = "right">
                        {$smarty.const._EXPORTSTATS}
                        <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=course&sel_course={$smarty.get.sel_course}&excel=1&group_filter={$smarty.get.group_filter}&branch_filter={$smarty.get.branch_filter}&job_filter={$smarty.get.job_filter}&subbranches={$smarty.get.subbranches}&user_filter={$smarty.get.user_filter}">
                            <img src = "images/file_types/xls.png" title = "{$smarty.const._XLSFORMAT}" alt = "{$smarty.const._XLSFORMAT}" />
                        </a>
                        <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=course&sel_course={$smarty.get.sel_course}&pdf=1&group_filter={$smarty.get.group_filter}&branch_filter={$smarty.get.branch_filter}&job_filter={$smarty.get.job_filter}&subbranches={$smarty.get.subbranches}&user_filter={$smarty.get.user_filter}">
                            <img src = "images/file_types/pdf.png" title = "{$smarty.const._PDFFORMAT}" alt = "{$smarty.const._PDFFORMAT}" />
                        </a>
                    </td></tr>
        	</table>

    		<br/>
            <table class = "statisticsGeneralInfo">
                <tr class = "{cycle name = 'course_common_info' values = 'oddRowColor, evenRowColor'}">
                    <td class = "labelCell">{$smarty.const._NAME}:</td>
                    <td class = "elementCell">{$T_CURRENT_COURSE->course.name}</td></tr>
                </tr>
                <tr class = "{cycle name = 'course_common_info' values = 'oddRowColor, evenRowColor'}">
                    <td class = "labelCell">{$smarty.const._CATEGORY}:</td>
                    <td class = "elementCell">{$T_CURRENT_COURSE->course.category_path}</td></tr>
                </tr>
                <tr class = "{cycle name = 'course_common_info' values = 'oddRowColor, evenRowColor'}">
                    <td class = "labelCell">{$smarty.const._LESSONS}:</td>
                    <td class = "elementCell">{$T_CURRENT_COURSE->course.num_lessons}</td></tr>
                </tr>
                <tr class = "{cycle name = 'course_common_info' values = 'oddRowColor, evenRowColor'}">
                    <td class = "labelCell">{$smarty.const._USERS}:</td>
                    <td class = "elementCell">{if $T_CURRENT_COURSE->course.num_users}{$T_CURRENT_COURSE->course.num_users} ({foreach name = "user_types_list" item ="item" key = "key" from = $T_CURRENT_COURSE->course.users_per_role}{$T_ROLES_ARRAY[$key]}: {$item}{if !$smarty.foreach.user_types_list.last}, {/if}{/foreach}){else}0{/if}</td>
                </tr>
                <tr class = "{cycle name = 'course_common_info' values = 'oddRowColor, evenRowColor'}">
                    <td class = "labelCell">{$smarty.const._PRICE}:</td>
                    <td class = "elementCell">{$T_CURRENT_COURSE->course.price_string}</td></tr>
                </tr>
                {if $T_CURRENT_COURSE->options.training_hours}
                <tr class = "{cycle name = 'course_common_info' values = 'oddRowColor, evenRowColor'}">
                    <td class = "labelCell">{$smarty.const._TRAININGHOURS}:</td>
                    <td class = "elementCell">{$T_CURRENT_COURSE->options.training_hours}</td></tr>
                </tr>
                {/if}
                {if $T_AVERAGE_COMPLETION_TIME}
                <tr class = "{cycle name = 'course_common_info' values = 'oddRowColor, evenRowColor'}">
                    <td class = "labelCell">{$smarty.const._AVERAGECOMPLETIONTIME}:</td>
                    <td class = "elementCell">{$T_AVERAGE_COMPLETION_TIME.time_string}</td></tr>
                </tr>
                {/if}
			</table>

			{assign var = "courseUsers_url" value = "`$smarty.server.PHP_SELF`?ctg=statistics&option=course&sel_course=`$smarty.get.sel_course``$T_STATS_FILTERS_URL`&"}
			{assign var = "courses_url" value = "`$smarty.server.PHP_SELF`?ctg=statistics&option=course&sel_course=`$smarty.get.sel_course``$T_STATS_FILTERS_URL`&"}
			{assign var = "_change_handles_" value = false}
			{capture name = "t_course_users_list_code"}
				{include file = "includes/common/course_users_list.tpl"}
			{/capture}
			{capture name = "t_courses_list_code"}
				{include file = "includes/common/courses_list.tpl"}
			{/capture}

	        <div class = "tabber">
			{eF_template_printBlock tabber = "users" title=$smarty.const._USERS data = $smarty.capture.t_course_users_list_code image = '32x32/users.png'}
			{eF_template_printBlock tabber = "instances" title=$smarty.const._COURSEINSTANCES data = $smarty.capture.t_courses_list_code image = '32x32/courses.png'}
			</div>


    {/if}

{*
    {if $T_COURSE_USERS_STATS|@sizeof > 0 || $T_COURSE_PROFESSORS_STATS|@sizeof > 0 || $T_COURSE_LESSON_STATS|@sizeof > 0}
        <div class = "tabber">
        {if $T_COURSE_USERS_STATS|@sizeof > 0 || $T_COURSE_PROFESSORS_STATS|@sizeof > 0}
            <div class = "statisticsDiv tabbertab {if (isset($smarty.get.tab) &&  $smarty.get.tab == 'users')} tabbertabdefault{/if}" title = "{$smarty.const._USERS}">
			{if $T_COURSE_USERS_STATS|@sizeof > 0}
                <table class = "statisticsTools">
                    <tr><td>{$smarty.const._STUDENTS}:</td></tr>
                </table>
                <table class = "sortedTable">
                    <tr>
                        <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
                        <td class = "topTitle">{$smarty.const._COURSEROLE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._COMPLETED}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._SCORE}</td>
                        <td class = "topTitle noSort centerAlign">{$smarty.const._OPTIONS}</td>
                    </tr>
                {foreach name = 'student_list' key = "login" item = "info" from = $T_COURSE_USERS_STATS}
                    <tr class = "{cycle values = "oddRowColor, evenRowColor"} {if !$info.active}deactivatedTableElement{/if}">
                        <td><a href = "{$smarty.server.PHP_SELF}?ctg=statistics&option=user&sel_user={$login}">#filter:login-{$login}#</a></td>
                        <td>{$T_ROLES[$info.role]}</td>
                        <td class = "centerAlign">
                            {if $info.completed}
                                <img src = "images/16x16/success.png" alt = "{$smarty.const._YES}" title = "{$smarty.const._YES}" border = "0" />
                            {else}
                                <img src = "images/16x16/forbidden.png" alt = "{$smarty.const._NO}" title = "{$smarty.const._NO}" border = "0" />
                            {/if}
                        </td>
                        <td class = "progressCell">
                            <span style = "display:none">{$info.score*1000}</span>
                            <span class = "progressNumber">#filter:score-{$info.score}#%</span>
                            <span class = "progressBar" style = "width:{$info.score}px;">&nbsp;</span>&nbsp;
                        </td>
                        <td class = "centerAlign">
							<a href = "{$smarty.server.PHP_SELF}?ctg=statistics&option=user&sel_user={$login}&specific_course_info=1&course={$T_COURSE_ID}&popup=1" onclick = "eF_js_showDivPopup(event, '{$smarty.const._DETAILS}', 2)" target = "POPUP_FRAME">
								<img src = "images/16x16/information.png" title = "{$smarty.const._DETAILS}" alt = "{$smarty.const._DETAILS}"></a>
                        </td>
                    </tr>
                {/foreach}
        		</table>
	            <br/>
            {/if}
			{if $T_COURSE_PROFESSORS_STATS|@sizeof > 0}
                <table class = "statisticsTools">
                    <tr><td>{$smarty.const._PROFESSORS}:</td></tr>
                </table>
				<table class = "sortedTable">
                    <tr>
                        <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
                        <td class = "topTitle">{$smarty.const._COURSEROLE}</td>
                    </tr>
                    {foreach name = 'professor_list' item = 'info' key = "login" from = $T_COURSE_PROFESSORS_STATS}
                        <tr class = "{cycle name = 'cprofessor_list' values = 'oddRowColor, evenRowColor'} {if !$info.active}deactivatedTableElement{/if}">
                            <td> <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=user&sel_user={$login}">#filter:login-{$login}#</a></td>
                            <td>{$T_ROLES[$info.role]}</td>
                        </tr>
                    {/foreach}
				</table>
			{/if}
            </div>
        {/if}

        {if $T_COURSE_LESSON_STATS|@sizeof > 0}
            <div class = "statisticsDiv tabbertab {if (isset($smarty.get.tab) &&  $smarty.get.tab == 'lessons')} tabbertabdefault{/if}" title = "{$smarty.const._LESSONS}">
                <table class = "sortedTable">
                    <tr>
                        <td class = "topTitle">{$smarty.const._LESSON}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._CONTENT}</td>
                        {if 'tests'|eF_template_isOptionVisible}
							<td class = "topTitle centerAlign">{$smarty.const._TESTS}</td>
                        {/if}
						{if 'projects'|eF_template_isOptionVisible}
							<td class = "topTitle centerAlign">{$smarty.const._PROJECTS}</td>
						{/if}
                    </tr>
                {foreach name = 'lesson_list' key ="id" item = "info" from = $T_COURSE_LESSON_STATS}
                    <tr class = "{cycle values = "oddRowColor, evenRowColor"} {if !$info.active}deactivatedTableElement{/if}" >
                        <td><a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=lesson&sel_lesson={$id}">{$info.name}</a></td>
                        <td class = "centerAlign">{$info.content}</td>
						{if 'tests'|eF_template_isOptionVisible}
							<td class = "centerAlign">{$info.tests}</td>
						{/if}
						{if 'projects'|eF_template_isOptionVisible}
							<td class = "centerAlign">{$info.projects}</td>
						{/if}
                    </tr>
                {/foreach}
				</table>
			</div>
        {/if}
        </div>
    {/if}
*}
    {/capture}

    {if $T_CURRENT_COURSE}
		{eF_template_printBlock title = "`$smarty.const._REPORTSFORCOURSE` <span class='innerTableName'>&quot;`$T_CURRENT_COURSE->course.name`&quot;</span>" data = $smarty.capture.course_statistics image = '32x32/courses.png' help = 'Reports'}
    {else}
    	{eF_template_printBlock title = $smarty.const._COURSESTATISTICS data = $smarty.capture.course_statistics image = '32x32/courses.png' help = 'Reports'}
	{/if}
