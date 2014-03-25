{* #cpp#ifndef COMMUNITY *}
{* #cpp#ifndef STANDARD *}

	    {capture name = 'events_statistics'}
			{$T_EVENT_REPORTS_FORM.javascript}
		    <form {$T_EVENT_REPORTS_FORM.attributes}>
		    {$T_EVENT_REPORTS_FORM.hidden}
	        <table class = "statisticsSelectList">
				<tr><td>
				<a href = "javascript:void(0)" onclick = "showEventStats('day')">{$smarty.const._LAST24HOURS}</a> - <a href = "javascript:void(0)" onclick = "showEventStats('week')">{$smarty.const._LASTWEEK}</a> - <a href = "javascript:void(0)" onclick = "showEventStats('month')">{$smarty.const._LASTMONTH}</a><br /><br />
				</td>
				</tr>

				<tr><td>
	            <table class = "statisticsSelectList">
					<tr><td class = "labelCell">{$smarty.const._EVENTDATE}:</td>
						<td class = "elementCell">
							<table width="100%">
								<tr><td width="1%">
						{$smarty.const._FROM} </td><td width="20%">{eF_template_html_select_date onChange="" prefix="from_" time=$T_FROM_TIMESTAMP start_year="-50" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
						 </td><td width="1%">
						{$smarty.const._TO}</td><td width="20%">{eF_template_html_select_date prefix="to_" onChange="" time=$T_TO_TIMESTAMP start_year="-5" end_year="+0" field_order = $T_DATE_FORMATGENERAL}
						</td>

						<td align="right" width="*">
				            <table class = "statisticsTools">
				                <tr><td id = "right">
				                        {$smarty.const._EXPORTSTATS}
				                        <a href = "javascript:void(0)" onClick="refreshEventResults('excel');">
				                            <img src = "images/file_types/xls.png" title = "{$smarty.const._XLSFORMAT}" alt = "{$smarty.const._XLSFORMAT}"/>
				                        </a>
				                        <a href = "javascript:void(0)" onClick="refreshEventResults('pdf');">
				                            <img src = "images/file_types/pdf.png" title = "{$smarty.const._PDFFORMAT}" alt="{$smarty.const._PDFFORMAT}"/>
				                        </a>
				                    </td></tr>
				            </table>
				        </td>

						</tr></table>
						</td>
					</tr>

	                <tr><td class = "labelCell">{$smarty.const._CHOOSEUSER}:</td>
	                    <td class = "elementCell">
	                        <input type = "text" id = "autocomplete" class = "autoCompleteTextBox" onClick="this.value='';$('event_user_login').value='';" onChange="$('event_user_login').value=this.value; "/>
	                        <img id = "busy" src = "images/16x16/clock.png" style = "display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "autocomplete_event_users" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
	                        <input type = "hidden" name="event_user_login" id = "event_user_login" />
	                    </td>
	                </tr>

	            	<tr><td class = "labelCell">{$smarty.const._LESSON}: </td>
	            		<td class = "elementCell">
							<input type = "text" id = "autocomplete_lessons_ev" class = "autoCompleteTextBox" value="" onClick="this.value='';$('event_course_id').value='';" onChange="$('event_lesson_id').value=this.value; " />
	                        <img id = "busy_event_lesson" src = "images/16x16/hourglass.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "autocomplete_event_lessons" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
							<input type = "hidden" name="event_lesson_id" id = "event_lesson_id" />
						</td>
					</tr>

					<tr><td class = "labelCell">{$smarty.const._COURSE}: </td>
						<td class = "elementCell">
							<input type = "text" id = "autocomplete_courses_ev" class = "autoCompleteTextBox" value="" onClick="this.value='';$('event_course_id').value='';" onChange="$('event_course_id').value=this.value; "/>
	                        <img id = "busy_event_course" src = "images/16x16/hourglass.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "autocomplete_event_courses" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
							<input type = "hidden" name="event_course_id" id = "event_course_id" />
						</td>
					</tr>


					<tr><td></td>
	                    <td class = "infoCell" ><table width = "100%"><tr><td>{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td>

	                    	</tr>
	                    	</table>
	                    </td>
	            	</tr>

	            	<tr><td></td>

			        </tr>

					<tr><td class = "labelCell">{$T_EVENT_REPORTS_FORM.event_types.label}: </td>
						{*<td class = "elementCell">{$T_EVENT_REPORTS_FORM.event_types.html}</td>*}

						<td class = "elementCell">
						<table id ="eventTypesTable" class = "sortedTable" size = "{$T_EVENT_TYPES_SIZE}" sortBy = "0" rowsPerPage = "5">
					        <tr class = "topTitle">
					            <td class = "topTitle" name="type">{$smarty.const._EVENTTYPE}</td>
					            <td class = "topTitle" name="selection">{$smarty.const._SELECT}</td>
					        </tr>
				            {foreach name = 'events_list' key = 'key' item = 'event' from = $T_EVENT_TYPES}
				            <tr class = "{cycle values = "oddRowColor, evenRowColor"}">
					            <td>{$event.text}</td>
					            <td id="column_{$event.type}"><span style="display:none"></span><input class = "inputCheckbox" type = "checkbox"  id = "event_{$event.type}" onChange="if (this.checked) this.previous().innerHTML = '1'; else this.previous().innerHTML = '';" ></td>
				            </tr>
				            {/foreach}
						</table>
						</td>

						<tr><td>&nbsp</td></tr>
						<tr><td></td><td align="left"><input type = "button" value = "{$smarty.const._SUBMIT}"  class = "flatButton" onClick="refreshEventResults();return false;"/></td></tr>

					</tr>
				</table>
			</td></tr>
			</table>
			</form>
		{/capture}
		{eF_template_printBlock title = $smarty.const._EVENTSTATISTICS data = $smarty.capture.events_statistics image = '32x32/social.png' help = 'Reports'}

	    {capture name = 't_found_events_code'}
<!--ajax:foundEvents-->
	        <table style = "width:100%" class = "sortedTable" size = "{$T_EVENTS_SIZE}" sortBy = "0" id = "foundEvents" useAjax = "1" rowsPerPage = "20" url = "{$smarty.session.s_type}.php?ctg=statistics&option=events&">
	            <tr class = "topTitle">
	                <td class = "topTitle" name="timestamp" width="15%">{$smarty.const._DATE}</td>
	                <td class = "topTitle" name="message">{$smarty.const._EVENT}</td>
	            </tr>

	       {if $T_EVENTS_SIZE > 0}
	            {foreach name = 'events_list' key = 'key' item = 'event' from = $T_EVENTS}
	            <tr class = "{cycle values = "oddRowColor, evenRowColor"}">
	            <td>#filter:timestamp_time_nosec-{$event.timestamp}#</td>
	            <td>{$event.message}</td>
	            </tr>
	            {/foreach}
	        {else}
	            <tr><td colspan ="2" class = "emptyCategory">{$smarty.const._NOEVENTSFOUND}</td></tr>
	        {/if}

	        </table>
<!--/ajax:foundEvents-->
	    {/capture}
		{eF_template_printBlock title = $smarty.const._EVENTSMATCHINGTHECRITERIA data = $smarty.capture.t_found_events_code image = '32x32/reports.png' help = 'Reports'}
{* #cpp#endif *}
{* #cpp#endif *}