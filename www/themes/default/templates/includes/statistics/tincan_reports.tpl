{capture name = "t_reports_code"}
	{if !$T_HIDE_FILTERS}
		<table class = "statisticsSelectList">
            <tr><td class = "labelCell">{$smarty.const._FILTERBYLESSON}:</td>
                <td class = "elementCell" colspan = "4">
                    <input type = "text" id = "autocomplete_l" class = "autoCompleteTextBox"/>
                    <img id = "busy" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
                    <div id = "autocomplete_lessons" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
                </td>
            </tr>
			<tr><td class = "labelCell">{$smarty.const._FILTERBYUSER}:</td>
				<td class = "elementCell">
					<input type = "text" id = "autocomplete" class = "autoCompleteTextBox"/>
					<img id = "busy" src = "images/16x16/clock.png" style = "display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
					<div id = "autocomplete_users" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
				</td>
			</tr>
            <tr><td class = "labelCell">{$smarty.const._FILTERBYVERB}:</td>
                <td class = "elementCell" colspan = "4">
                	<select id = "tincan_verb" class = "autoCompleteTextBox" onchange = "document.location=document.location.toString().replace(/&sel_verb=\d*/, '')+'&sel_verb='+this.options[this.options.selectedIndex].value">
                		<option value = "">{$smarty.const._SELECTVERBFROMLIST}</option>
                	{foreach name = "verb_list" item = "item" key = "key" from = $T_VERBS}
                		<option value = "{$item.id}" {if $smarty.get.sel_verb==$item.id}selected{/if}>{$item.name}</option>
                	{/foreach}
                	</select>
                </td>
            </tr>
            <tr><td class = "labelCell">{$smarty.const._FILTERBYOBJECT}:</td>
                <td class = "elementCell" colspan = "4">
                	<select id = "tincan_object" class = "autoCompleteTextBox" onchange = "document.location=document.location.toString().replace(/&sel_object=\d+/, '')+'&sel_object='+this.options[this.options.selectedIndex].value">
                		<option value = "">{$smarty.const._SELECTOBJECTFROMLIST}</option>
                	{foreach name = "verb_list" item = "item" key = "key" from = $T_OBJECTS}
                		<option value = "{$item.id}" {if $smarty.get.sel_object==$item.id}selected{/if}>{$item.name} ({$item.type})</option>
                	{/foreach}
                	</select>
                </td>
            </tr>
		</table>
	{/if}
<!--ajax:reportsTable-->
						<table size = "{$T_TABLE_SIZE}" width = "100%" sortBy = "0" order = "asc" id = "reportsTable" class = "sortedTable" useAjax = "1" url = "{$smarty.server.PHP_SELF}?ctg=statistics&option=tincan&sel_verb={$smarty.get.sel_verb}&sel_object={$smarty.get.sel_object}&sel_user={$smarty.get.sel_user}&sel_lesson={$smarty.get.sel_lesson}&">
					        <tr class = "topTitle">
					            <td class = "topTitle" name = "timestamp">{$smarty.const._STATEMENT}</td>
					        </tr>

	                        {foreach name = 'statements_list' key = 'key' item = 'item' from = $T_DATA_SOURCE}
					        <tr class = "{cycle values = "oddRowColor, evenRowColor"}">
					            <td><span style = "display:none">{$item.timestamp}</span>
					            	{$item.statement}
					            </td>
					        </tr>
							{foreachelse}
							<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "100%">{$smarty.const._NODATAFOUND}</td></tr>
	                        {/foreach}
	                    </table>
<!--/ajax:reportsTable-->

{/capture}
{eF_template_printBlock title = $smarty.const._TINCANREPORTS data = $smarty.capture.t_reports_code image = '32x32/theory.png' help = 'Reports'}