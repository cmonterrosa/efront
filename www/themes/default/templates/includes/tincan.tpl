{capture name = "moduleTincanOptions"}
	<tr><td class = "moduleCell">
	{if $smarty.get.tincan_import}
	        {assign var = "title" value = "`$title`&nbsp;&raquo;&nbsp;<a class = 'titleLink'  href = '`$smarty.server.PHP_SELF`?ctg=scorm&tincan_import=1'>`$smarty.const._TINCANIMPORT`</a>"}
	
	        {capture name = 'tincan_import_code'}
	        	{eF_template_printForm form = $T_UPLOAD_TINCAN_FORM}
	        {/capture}
	        {eF_template_printBlock title = $smarty.const._TINCANIMPORT data = $smarty.capture.tincan_import_code image = '32x32/theory.png' main_options = $T_TABLE_OPTIONS}
	{elseif $smarty.get.tincan_review}
		{capture name = "t_reports_code"}	
<!--ajax:reportsTable-->
						<table size = "{$T_TABLE_SIZE}" width = "100%" sortBy = "0" order = "asc" id = "reportsTable" class = "sortedTable" useAjax = "1" url = "{$smarty.server.PHP_SELF}?ctg=tincan&tincan_review=1&">
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
	{else}                                
			{capture name = 't_tree_code'}                               
                <table>
                    <tr><td>
                        {$T_TINCAN_TREE}
                    </td></tr>
                </table>
            {/capture}
            {eF_template_printBlock title = $smarty.const._TINCAN data = $smarty.capture.t_tree_code image = '32x32/theory.png' main_options = $T_TABLE_OPTIONS}                                
	{/if}
    </td></tr>
{/capture}
