{* #cpp#ifdef ENTERPRISE *}
	    {capture name = 'branch_statistics'}
	    	{if !$T_SINGLE_BRANCH}
	            <table class = "statisticsSelectList">
	                <tr><td class = "labelCell">{$smarty.const._CHOOSEBRANCH}:</td>
	                    <td class = "elementCell">
	                        <input type = "text" id = "autocomplete" class = "autoCompleteTextBox"/>
	                        <img id = "busy" src = "images/16x16/clock.png" style = "display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
	                        <div id = "autocomplete_branches" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
	                    </td>
	                </tr>
	                <tr><td></td>
	                    <td class = "infoCell">{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td>
	            	</tr>
	            </table>
	        {/if}

	        {if (isset($T_BRANCH_INFO))}
			<script>
			var selGroup = "{$smarty.get.sel_branch}";
			</script>

	            <table class = "statisticsTools">
	            	<tr>
						<td class = "labelCell">{$smarty.const._FILTERS}:</td>
					 	<td class = "filter">
        					<select style = "vertical-align:middle" name = "user_filter"  onchange = "location = location.toString().replace(/&user_filter=\d/, '')+'&user_filter='+this.value">
                				<option value = "1"{if !$smarty.get.user_filter || $smarty.get.user_filter == 1}selected{/if}>{$smarty.const._ACTIVEUSERS}</option>
                				<option value = "2"{if $smarty.get.user_filter == 2}selected{/if}>{$smarty.const._INACTIVEUSERS}</option>
                				<option value = "3"{if $smarty.get.user_filter == 3}selected{/if}>{$smarty.const._ALLUSERS}</option>
        					</select>
    
					        <input type = "checkbox" style = "vertical-align:middle" name = "includes_subbranches" {if $smarty.get.subbranches}checked{/if} onclick = "location = location.toString().replace(/&subbranches=\d/, '')+'&subbranches='+(this.checked ? 1 : 0)"/><span style = "vertical-align:middle" >{$smarty.const._SUBBRANCHES}</span>
					    </td>
	                <td id = "right">
	                        {$smarty.const._EXPORTSTATS}

	                        <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=branches&sel_branch={$smarty.get.sel_branch}{if $smarty.get.subbranches}&subbranches=1{/if}{if $smarty.get.user_filter}&user_filter={$smarty.get.user_filter}{/if}&excel=branch{if isset($smarty.get.from_year)}&from_year={$smarty.get.from_year}&from_month={$smarty.get.from_month}&from_day={$smarty.get.from_day}&from_hour={$smarty.get.from_hour}&from_min={$smarty.get.from_min}{/if}{if isset($smarty.get.to_year)}&to_year={$smarty.get.to_year}&to_month={$smarty.get.to_month}&to_day={$smarty.get.to_day}&to_hour={$smarty.get.to_hour}&to_minute={$smarty.get.to_minute}{/if}{if $smarty.get.showOnlyGroupLessons == "true"}&showOnlyGroupLessons=true{/if}{if $smarty.get.showOnlyGroupUsers == "true"}&showOnlyGroupUsers=true{/if}">
	                            <img src = "images/file_types/xls.png" title = "{$smarty.const._XLSFORMAT}" alt = "{$smarty.const._XLSFORMAT}"/>
	                        </a>
	                        <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=branches&sel_branch={$smarty.get.sel_branch}{if $smarty.get.subbranches}&subbranches=1{/if}{if $smarty.get.user_filter}&user_filter={$smarty.get.user_filter}{/if}&pdf=branch{if isset($smarty.get.from_year)}&from_year={$smarty.get.from_year}&from_month={$smarty.get.from_month}&from_day={$smarty.get.from_day}&from_hour={$smarty.get.from_hour}&from_min={$smarty.get.from_min}{/if}{if isset($smarty.get.to_year)}&to_year={$smarty.get.to_year}&to_month={$smarty.get.to_month}&to_day={$smarty.get.to_day}&to_hour={$smarty.get.to_hour}&to_minute={$smarty.get.to_minute}{/if}{if $smarty.get.showOnlyGroupLessons == "true"}&showOnlyGroupLessons=true{/if}{if $smarty.get.showOnlyGroupUsers == "true"}&showOnlyGroupUsers=true{/if}">
	                            <img src = "images/file_types/pdf.png" title = "{$smarty.const._PDFFORMAT}" alt="{$smarty.const._PDFFORMAT}"/>
	                        </a>
	                    </td></tr>
	            </table>

	            <br/>
	            <table class = "statisticsGeneralInfo">
	            	<tr>
	            		<td>
	                        <table>
	                            <tr class = "{cycle name = 'common_branch_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._BRANCHNAME}:</td>
	                                <td class = "elementCell">{$T_BRANCH_PATH} {if $T_BRANCH_INFO.is_default}({$smarty.const._DEFAULT}){/if}</td>
	                            </tr>
	                            <tr class = "{cycle name = 'common_branch_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._ADDRESS}:</td>
	                                <td class = "elementCell">{$T_BRANCH_INFO.address}{if $T_BRANCH_INFO.city}, {$T_BRANCH_INFO.city}{/if}{if $T_BRANCH_INFO.country}, {$T_BRANCH_INFO.country}{/if}</td>
	                            </tr>

							    <tr class = "{cycle name = 'common_branch_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._TELEPHONE}:</td>
	                                <td class = "elementCell">{$T_BRANCH_INFO.telephone}</td>
	                            </tr>

	                            <tr class = "{cycle name = 'common_branch_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._EMAIL}:</td>
	                                <td class = "elementCell">{$T_BRANCH_INFO.email}</td>
	                            </tr>

							    <tr class = "{cycle name = 'common_branch_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._FATHERBRANCH}:</td>
	                                <td class = "elementCell">{$T_BRANCH_INFO.father_name}</td>
	                            </tr>

							    <tr class = "{cycle name = 'common_branch_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._BRANCHUSERS}:</td>
	                                <td class = "elementCell">{$T_BRANCH_INFO.users_count}</td>
	                            </tr>
							    <tr class = "{cycle name = 'common_branch_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._JOBDESCRIPTIONS}:</td>
	                                <td class = "elementCell">{$T_BRANCH_INFO.jobs_count}</td>
	                            </tr>

							    <tr class = "{cycle name = 'common_branch_info' values = 'oddRowColor, evenRowColor'}">
	                                <td class = "labelCell">{$smarty.const._SUBBRANCHES}:</td>
	                                <td class = "elementCell">{$T_BRANCH_INFO.subbranches_count}</td>
	                            </tr>



	                        </table>
	            	</td></tr>
	            </table>

	        {/if}


	        {if $T_BRANCH_INFO.users_count}
	        <div class = "tabber">
	            {if !empty($T_BRANCH_INFO)}
	            <div class = "statisticsDiv tabbertab {if (isset($smarty.get.tab) &&  $smarty.get.tab == 'users')} tabbertabdefault{/if}" title = "{$smarty.const._BRANCHUSERS}">
	                    <table class = "statisticsTools">
							<tr><td>{$smarty.const._STUDENTROLE}:</td></tr>
						</table>
					{if !$T_SORTED_TABLE || $T_SORTED_TABLE == 'studentsTable'}

<!--ajax:studentsTable-->
						<table size = "{$T_TABLE_SIZE}" sortBy = "0" order = "desc" id = "studentsTable" class = "sortedTable" useAjax = "1" url = "{$smarty.server.PHP_SELF}?ctg=statistics&option=branches&sel_branch={$smarty.get.sel_branch}&subbranches={$smarty.get.subbranches}&user_filter={$smarty.get.user_filter}&">
					        <tr class = "topTitle">
					            <td class = "topTitle" name = "login" width="20%">{$smarty.const._USER}</td>
					            <td class = "topTitle" name = "languages_NAME">{$smarty.const._LANGUAGE}</td>
					            <td class = "topTitle" name = "timestamp">{$smarty.const._REGISTRATIONDATE}</td>
					            <td class = "topTitle" name = "last_login">{$smarty.const._LASTLOGIN}</td>
					            <td class = "topTitle" name = "bname">{$smarty.const._BRANCH}</td>
					            <td class = "topTitle centerAlign" name = "jobs_num">{$smarty.const._JOBSASSIGNED}</td>
					            <td class = "topTitle noSort centerAlign">
					            	{$smarty.const._OPERATIONS}
					            </td>
					        </tr>

	                        {foreach name = 'lesson_list' key = 'users_login' item = 'user' from = $T_DATA_SOURCE}
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
					            <td>{$user.bname}</td>
					            <td class = "centerAlign">{$user.jobs_num}</td>
						        <td class = "centerAlign">
					        {if $user.login != $smarty.session.s_login && $user.user_type != 'administrator'}
					                <a href="{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile&op=status&tab=user_form&popup=1&printable=1" onclick = "eF_js_showDivPopup(event, '{$smarty.const._EMPLOYEEFORMPRINTPREVIEW}', 2)" target = "POPUP_FRAME"><img class = "handle" src='images/16x16/printer.png' title=  '{$smarty.const._PRINTPREVIEW}' alt = '{$smarty.const._PRINTPREVIEW}' /></a>
					        {/if}
							{if !isset($T_CURRENT_USER->coreAccess.statistics) || $T_CURRENT_USER->coreAccess.statistics != 'hidden'}
					        		<a href="{$smarty.session.s_type}.php?ctg=statistics&option=user&sel_user={$user.login}"><img class = "handle" src = "images/16x16/reports.png" title = "{$smarty.const._STATISTICS}" alt = "{$smarty.const._STATISTICS}" /></a>
							{/if}
					        {if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
					                <a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink"><img class = "handle" src = "images/16x16/edit.png" title = "{$smarty.const._EDIT}" alt = "{$smarty.const._EDIT}" /></a>
					                {if $smarty.session.s_login != $user.login}
					                	<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._ARCHIVEENTITY}" alt = "{$smarty.const._ARCHIVEENTITY}" onclick = "archiveUser(this, '{$user.login}')"/>
										{*<a href = "{$smarty.session.s_type}.php?ctg=users&op=users_data&delete_user={$user.login}" onclick = "return confirm('{$smarty.const._AREYOUSUREYOUWANTTOFIREEMPLOYEE}')" class = "deleteLink"><img border = "0" src = "images/16x16/error_delete.png" title = "{$smarty.const._FIRE}" alt = "{$smarty.const._FIRE}" /></a>*}
									{/if}
							{/if}
					            </td>
					        </tr>
							{foreachelse}
							<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "100%">{$smarty.const._NODATAFOUND}</td></tr>
	                        {/foreach}
	                    </table>
<!--/ajax:studentsTable-->
	                    {/if}
	                    <br/>
	                    <table class = "statisticsTools">
							<tr><td>{$smarty.const._PROFESSORROLE}:</td></tr>
						</table>
					{if !$T_SORTED_TABLE || $T_SORTED_TABLE == 'professorsTable'}

<!--ajax:professorsTable-->
						<table size = "{$T_TABLE_SIZE}" sortBy = "0" order = "desc" id = "professorsTable" class = "sortedTable" useAjax = "1" url = "{$smarty.server.PHP_SELF}?ctg=statistics&option=branches&sel_branch={$smarty.get.sel_branch}&subbranches={$smarty.get.subbranches}&user_filter={$smarty.get.user_filter}&">
					        <tr class = "topTitle">
					            <td class = "topTitle" name = "login" width="20%">{$smarty.const._USER}</td>
					            <td class = "topTitle" name = "languages_NAME">{$smarty.const._LANGUAGE}</td>
					            <td class = "topTitle" name = "timestamp">{$smarty.const._REGISTRATIONDATE}</td>
					            <td class = "topTitle" name = "last_login">{$smarty.const._LASTLOGIN}</td>
					            <td class = "topTitle" name = "bname">{$smarty.const._BRANCH}</td>
					            <td class = "topTitle centerAlign" name = "jobs_num">{$smarty.const._JOBSASSIGNED}</td>
					            <td class = "topTitle noSort centerAlign">
					            	{$smarty.const._OPERATIONS}
					            </td>
					        </tr>

	                        {foreach name = 'lesson_list' key = 'users_login' item = 'user' from = $T_DATA_SOURCE}
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
					            <td>{$user.bname}</td>
					            <td class = "centerAlign">{$user.jobs_num}</td>
						        <td class = "centerAlign">
					        {if $user.login != $smarty.session.s_login && $user.user_type != 'administrator'}
					                <a href="{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile&op=status&tab=user_form&popup=1&printable=1" onclick = "eF_js_showDivPopup(event, '{$smarty.const._EMPLOYEEFORMPRINTPREVIEW}', 2)" target = "POPUP_FRAME"><img class = "handle" src='images/16x16/printer.png' title=  '{$smarty.const._PRINTPREVIEW}' alt = '{$smarty.const._PRINTPREVIEW}' /></a>
					        {/if}
							{if !isset($T_CURRENT_USER->coreAccess.statistics) || $T_CURRENT_USER->coreAccess.statistics != 'hidden'}
					        		<a href="{$smarty.session.s_type}.php?ctg=statistics&option=user&sel_user={$user.login}"><img class = "handle" src = "images/16x16/reports.png" title = "{$smarty.const._STATISTICS}" alt = "{$smarty.const._STATISTICS}" /></a>
							{/if}
					        {if !isset($T_CURRENT_USER->coreAccess.users) || $T_CURRENT_USER->coreAccess.users == 'change'}
					                <a href = "{$smarty.session.s_type}.php?ctg=personal&user={$user.login}&op=profile" class = "editLink"><img class = "handle" src = "images/16x16/edit.png" title = "{$smarty.const._EDIT}" alt = "{$smarty.const._EDIT}" /></a>
					                {if $smarty.session.s_login != $user.login}
					                	<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._ARCHIVEENTITY}" alt = "{$smarty.const._ARCHIVEENTITY}" onclick = "archiveUser(this, '{$user.login}')"/>
										{*<a href = "{$smarty.session.s_type}.php?ctg=users&op=users_data&delete_user={$user.login}" onclick = "return confirm('{$smarty.const._AREYOUSUREYOUWANTTOFIREEMPLOYEE}')" class = "deleteLink"><img border = "0" src = "images/16x16/error_delete.png" title = "{$smarty.const._FIRE}" alt = "{$smarty.const._FIRE}" /></a>*}
									{/if}
							{/if}
					            </td>
					        </tr>
							{foreachelse}
							<tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "100%">{$smarty.const._NODATAFOUND}</td></tr>
	                        {/foreach}
	                    </table>

<!--/ajax:professorsTable-->
	                    {/if}
	                    <br/>
	            </div>
	            {/if}
	       </div>
	       {/if}
	    {/capture}

	    {if $T_BRANCH_INFO != ""}
	    	{eF_template_printBlock title = "`$smarty.const._REPORTSFORBRANCH` <span class='innerTableName'>&quot;`$T_BRANCH_NAME`&quot;</span>" data = $smarty.capture.branch_statistics image = '32x32/users.png' help = 'Reports'  options = $T_TABLE_OPTIONS}
	    {else}
	    	{eF_template_printBlock title = $smarty.const._BRANCHSTATISTICS data = $smarty.capture.branch_statistics image = '32x32/users.png' help = 'Reports'}
		{/if}
{* #cpp#endif *}