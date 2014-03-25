{* #cpp#ifndef COMMUNITY *}
{* #cpp#ifndef STANDARD *}
		{capture name = 'participation_results'}		
			{$T_PARTICIPATION_REPORTS_FORM.javascript}
		    <form {$T_PARTICIPATION_REPORTS_FORM.attributes}>
		    {$T_PARTICIPATION_REPORTS_FORM.hidden}

            <table class = "statisticsTools statisticsSelectList">
				<tr>
					<td class = "filter">
					<a href = "javascript:void(0)" onclick = "showParticipationStats('day')">{$smarty.const._LAST24HOURS}</a> - <a href = "javascript:void(0)" onclick = "showParticipationStats('week')">{$smarty.const._LASTWEEK}</a> - <a href = "javascript:void(0)" onclick = "showParticipationStats('month')">{$smarty.const._LASTMONTH}</a>
					</td>
					
                	<td id = "right">
                        {$smarty.const._EXPORTSTATS}
                        <a href = "{$smarty.server.PHP_SELF}?ctg=statistics&option=participation&report_type={$T_REPORT_TYPE}&report_entity_id={$T_REPORT_ENTITY_ID}&from={$T_FROM_TIMESTAMP}&to={$T_TO_TIMESTAMP}&ajax=participationTable&excel=1" arget = "_new">
                            <img src = "images/file_types/xls.png" title = "{$smarty.const._XLSFORMAT}" alt = "{$smarty.const._XLSFORMAT}" />
                        </a>
                    </td></tr>
        	</table>
			<br/>
				<table class = "statisticsSelectList" style = "width:100%">				
					<tr><td class = "labelCell">{$smarty.const._DATE}:&nbsp;</td>
						<td class = "elementCell">
							{$smarty.const._FROM}&nbsp;{eF_template_html_select_date prefix="from_" time=$T_FROM_TIMESTAMP start_year="-5" end_year="+0" field_order = $T_DATE_FORMATGENERAL}&nbsp;
							{$smarty.const._TO}&nbsp;{eF_template_html_select_date prefix="to_"  time=$T_TO_TIMESTAMP start_year="-5" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
						</td>
					</tr>
					<tr><td class = "labelCell">{$T_PARTICIPATION_REPORTS_FORM.type.label}:&nbsp;</td>
						<td class = "elementCell">{$T_PARTICIPATION_REPORTS_FORM.type.html}</td>
					</tr>
	            	<tr {if isset($smarty.post.type) && $smarty.post.type != "lesson"} style="display:none"{/if} id="lesson_row">
	            		<td class = "labelCell">{$smarty.const._LESSON}:&nbsp;</td>
	            		<td class = "elementCell">
						<input type = "text" id = "autocomplete_lesson_participation" class = "autoCompleteTextBox" value="{if $T_SELECTED_LESSON}{$T_SELECTED_LESSON}{else}{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}{/if}" onClick="this.value='';" />
	                        <img id = "busy_lesson_participation" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "lesson_choices_participation" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
						<input type = "hidden" name="lesson_id_participation" id = "lesson_id_participation" value = "{$smarty.post.lesson_id_participation}"/>
						</td>
					</tr>
					<tr id="course_row" {if $smarty.post.type != "course"} style="display:none" {/if} >
						<td class = "labelCell">{$smarty.const._COURSE}:&nbsp;</td>
						<td class = "elementCell">
						<input type = "text" id = "autocomplete_course_participation" class = "autoCompleteTextBox" value="{if $T_SELECTED_COURSE}{$T_SELECTED_COURSE}{else}{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}{/if}" onClick="this.value='';" />
	                        <img id = "busy_course_participation" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "course_choices_participation" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
						<input type = "hidden" name="course_id_participation" id = "course_id_participation" value = "{$smarty.post.course_id_participation}"/>
						</td>
					</tr>

					<tr id="user_type_row" {if $smarty.post.type != "user_type"} style="display:none" {/if}><td class = "labelCell">{$T_PARTICIPATION_REPORTS_FORM.user_type.label}: </td>
						<td class = "elementCell">{$T_PARTICIPATION_REPORTS_FORM.user_type.html}</td>
					</tr>

					<tr id="group_row" {if $smarty.post.type != "group"} style="display:none" {/if}><td class = "labelCell">{$T_PARTICIPATION_REPORTS_FORM.group.label}: </td>
						<td class = "elementCell">{$T_PARTICIPATION_REPORTS_FORM.group.html}</td>
					</tr>

	            	<tr><td></td></tr>


						<tr><td></td><td align="left"><input type = "submit" value = "{$smarty.const._SUBMIT}"  class = "flatButton" /></td></tr>

					</tr>
				</table>
			</form>
	<br />
	
					{if !$T_SORTED_TABLE || $T_SORTED_TABLE == 'participationTable'}
<!--ajax:participationTable-->
						<table style = "width:100%" size = "{$T_TABLE_SIZE}" sortBy = "0" order = "{$T_DATASOURCE_SORT_ORDER}"  id = "participationTable" class = "sortedTable" useAjax = "1" url = "{$smarty.server.PHP_SELF}?ctg=statistics&option=participation&report_type={$T_REPORT_TYPE}&report_entity_id={$T_REPORT_ENTITY_ID}&from={$T_FROM_TIMESTAMP}&to={$T_TO_TIMESTAMP}&">
							<tr class = "topTitle">
	                            <td class = "topTitle" name = "login" >{$smarty.const._USER}</td>
								<td class = "topTitle" name = "lesson_name">{$smarty.const._LESSON}</td>
								<td class = "topTitle centerAlign" name = "content">{$smarty.const._CONTENT}</td>
								<td class = "topTitle centerAlign" name = "test">{$smarty.const._TEST}</td>
								<td class = "topTitle centerAlign" name = "project">{$smarty.const._PROJECT}</td>
								<td class = "topTitle centerAlign" name = "forum">{$smarty.const._FORUM}</td>
							</tr>
							{foreach name = 'participation_list' key = 'key' item = 'item' from = $T_DATA_SOURCE}
							<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"}">
								<td>#filter:login-{$item.login}#</td>
								<td>{$item.lesson_name}</td>
								<td class = "centerAlign">{$item.content}</td>
								<td class = "centerAlign">{$item.test}</td>
								<td class = "centerAlign">{$item.project}</td>
								<td class = "centerAlign">{$item.forum}</td>
							</tr>
							{foreachelse}
							<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "10">{$smarty.const._NODATAFOUND}</td></tr>
							{/foreach}
						</table>
<!--/ajax:participationTable-->
						{/if}
	
			{if isset($T_PARTICIPATION)}	
			<table class = "sortedTable" sortBy = "0" width="100%">
	                        <tr>
	                            <td class = "topTitle" style = "width:300px">{$smarty.const._USER}</td>
								<td class = "topTitle" style = "width:300px">{$smarty.const._LESSON}</td>
								<td class = "topTitle centerAlign" style = "width:300px">{$smarty.const._CONTENT}</td>
								<td class = "topTitle centerAlign" style = "width:300px">{$smarty.const._TEST}</td>
								<td class = "topTitle centerAlign" style = "width:300px">{$smarty.const._PROJECT}</td>
								<td class = "topTitle centerAlign" style = "width:300px">{$smarty.const._FORUM}</td>
	                        </tr>
	                    {foreach name = 'users_list' key = 'user_key' item = "login" from = $T_USERS}
							{foreach name = 'lessons_list' key = 'lesson_key' item = "lesson" from = $T_LESSONS}
						   <tr class = "{cycle name = 'lessons_list' values = 'oddRowColor, evenRowColor'}">
								<td style = "width:300px">#filter:login-{$login}#</td>
	                            <td style = "width:300px">{if $smarty.post.type != 'course'}{$lesson}{else}{$lesson->lesson.name}{/if}</td>
								<td class = "centerAlign">{if $T_PARTICIPATION[$login][$lesson_key].100.count != "" || $T_PARTICIPATION[$login][$lesson_key].101.count != "" || $T_PARTICIPATION[$login][$lesson_key].103.count != ""}{$smarty.const._YESABBREVIATION}{else}{$smarty.const._NOABBREVIATION}{/if}</td>
								<td class = "centerAlign">{if $T_PARTICIPATION[$login][$lesson_key].75.count != "" || $T_PARTICIPATION[$login][$lesson_key].77.count!= ""}{$smarty.const._YESABBREVIATION}{else}{$smarty.const._NOABBREVIATION}{/if}</td>
								<td class = "centerAlign">{if $T_PARTICIPATION[$login][$lesson_key].30.count != "" || $T_PARTICIPATION[$login][$lesson_key].31.count!= ""}{$smarty.const._YESABBREVIATION}{else}{$smarty.const._NOABBREVIATION}{/if}</td>
								<td class = "centerAlign">{if $T_PARTICIPATION[$login][$lesson_key].38.count != ""} {$smarty.const._YESABBREVIATION}{else} {$smarty.const._NOABBREVIATION}{/if}</td>
							</tr>
							{/foreach}
	                    {foreachelse}
	                    	<tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
	                    {/foreach}
	        </table>
			{/if}
			{/capture}
			{eF_template_printBlock title = "`$smarty.const._PARTICIPATIONSTATISTICS` [#filter:timestamp-`$T_FROM_TIMESTAMP`# - #filter:timestamp-`$T_TO_TIMESTAMP`#]" data = $smarty.capture.participation_results image = '32x32/reports.png' help = 'Reports'}
{* #cpp#endif *}
{* #cpp#endif *}