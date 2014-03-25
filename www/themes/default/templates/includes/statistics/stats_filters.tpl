	{if $smarty.get.from_year}
		{assign var = "dates_url" value = "&from_year=`$smarty.get.from_year`&from_month=`$smarty.get.from_month`&from_day=`$smarty.get.from_day`&from_hour=`$smarty.get.from_hour`&from_min=`$smarty.get.from_min`"}
		{assign var = "dates_url" value = "`$dates_url`&to_year=`$smarty.get.to_year`&to_month=`$smarty.get.to_month`&to_day=`$smarty.get.to_day`&to_hour=`$smarty.get.to_hour`&to_min=`$smarty.get.to_min`"}
	{/if}	
	<td class = "filter">{$smarty.const._FILTERS}:
        <select style = "vertical-align:middle" id = "user_filter"  name = "user_filter">
                <option value = "1"{if !$smarty.get.user_filter || $smarty.get.user_filter == 1}selected{/if}>{$smarty.const._ACTIVEUSERS}</option>
                <option value = "2"{if $smarty.get.user_filter == 2}selected{/if}>{$smarty.const._INACTIVEUSERS}</option>
                <option value = "3"{if $smarty.get.user_filter == 3}selected{/if}>{$smarty.const._ALLUSERS}</option>
        </select>
    </td><td class = "filter">
        <select style = "vertical-align:middle" id = "group_filter" name = "group_filter" >
                <option value = "-1" class = "inactiveElement" {if !$smarty.get.group_filter}selected{/if}>{$smarty.const._SELECTGROUP}</option>
            {foreach name = "group_options" from = $T_GROUPS item = 'group' key='id'}
                <option value = "{$group.id}" {if $smarty.get.group_filter == $group.id}selected{/if}>{$group.name}</option>
            {/foreach}
        </select>
    </td>
 {if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
 {assign var="branch_array" value=","|explode:$smarty.get.branch_filter} 
 	<td class = "filter">
        <select  multiple = "multiple" SIZE = "5" class = "inputText" style = "vertical-align:middle" id = "group_branch" name = "group_branch" >
            {foreach name = "branch_options" from = $T_BRANCHES item = 'branch' key='id'}
                <option value = "{$id}" {if $id|in_array:$branch_array}selected{/if}>{$branch}</option>
            {/foreach}
        </select>
  {assign var="job_array" value=","|explode:$smarty.get.job_filter}        
        <select  multiple = "multiple" SIZE = "5" class = "inputText" style = "vertical-align:middle" id = "group_job" name = "group_job" >
            {foreach name = "job_options" from = $T_JOB_DES item = 'job' key='id'}
                <option value = "{$id}" {if $id|in_array:$job_array}selected{/if}>{$job}</option>
            {/foreach}
        </select>
        
        <input type = "checkbox" style = "vertical-align:middle" id = "includes_subbranches" name = "includes_subbranches" {if $smarty.get.subbranches}checked{/if} /><span style = "vertical-align:middle" >{$smarty.const._SUBBRANCHES}</span>
    </td>
     <td>
{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}     
 	<input class = "flatButton" type = "button" value="{$smarty.const._SUBMIT}" onclick = "document.location='{$smarty.server.PHP_SELF}?ctg=statistics&option={$smarty.get.option}{if (isset($smarty.get.tab))}&tab={$smarty.get.tab}{/if}&sel_{$smarty.get.option}={$T_STATS_ENTITY_ID}&branch_filter='+appendSelection('group_branch')+'&group_filter='+$('group_filter').options[$('group_filter').selectedIndex].value+'&job_filter='+appendSelection('group_job')+'&subbranches='+($('includes_subbranches').checked ? 1:0) +'&user_filter='+$('user_filter').options[$('user_filter').selectedIndex].value+'{$dates_url}';">
{else} {* #cpp#else *}
	<input class = "flatButton" type = "button" value="{$smarty.const._SUBMIT}" onclick = "document.location='{$smarty.server.PHP_SELF}?ctg=statistics&option={$smarty.get.option}{if (isset($smarty.get.tab))}&tab={$smarty.get.tab}{/if}&sel_{$smarty.get.option}={$T_STATS_ENTITY_ID}&group_filter='+$('group_filter').options[$('group_filter').selectedIndex].value+'&user_filter='+$('user_filter').options[$('user_filter').selectedIndex].value+'{$dates_url}';">
{/if} {* #cpp#endif *} 	
 </td>
 {else}{* #cpp#else *}   
    <td>
 		<input class = "flatButton" type = "button" value="{$smarty.const._SUBMIT}" onclick = "document.location='{$smarty.server.PHP_SELF}?ctg=statistics&option={$smarty.get.option}{if (isset($smarty.get.tab))}&tab={$smarty.get.tab}{/if}&sel_{$smarty.get.option}={$T_STATS_ENTITY_ID}&group_filter='+$('group_filter').options[$('group_filter').selectedIndex].value+'&user_filter='+$('user_filter').options[$('user_filter').selectedIndex].value+'{$dates_url}';">
 	</td>
 {/if} {* #cpp#endif *}

