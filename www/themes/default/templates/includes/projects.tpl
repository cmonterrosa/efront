{if $smarty.session.s_lesson_user_type == 'student'}
	{assign var = "_student_" value = 1}
{elseif $smarty.session.s_lesson_user_type == 'professor'}
	{assign var = "_professor_" value = 1}
{/if}
{if !isset($T_CURRENT_USER->coreAccess.content) || $T_CURRENT_USER->coreAccess.content == 'change'}
	{assign var = "_change_" value = 1}
{/if}

{capture name = "moduleProjects"}
	<tr>
		<td class = "moduleCell">
			{if $_professor_ && ($smarty.get.add_project || $smarty.get.edit_project)}
				{capture name = 't_add_project_code'}
					<div class = "tabber">
						{capture name = "t_projects_code"}
							{$T_ADD_PROJECT_FORM.javascript}
							<form {$T_ADD_PROJECT_FORM.attributes}>
								{$T_ADD_PROJECT_FORM.hidden}
								<table class = "formElements">
									<tr>
										<td class = "labelCell">{$smarty.const._TITLE}:&nbsp;</td>
										<td class = "elementCell">{$T_ADD_PROJECT_FORM.title.html}</td>
									</tr>
									
									{if $T_ADD_PROJECT_FORM.title.error}
										<tr>
											<td></td>
											<td class = "formError">{$T_ADD_PROJECT_FORM.title.error}</td>
										</tr>
									{/if}
                                    
									<tr>
										<td class = "labelCell">{$smarty.const._AUTOASSIGNTONEWUSERS}:&nbsp;</td>
										<td class = "elementCell">{$T_ADD_PROJECT_FORM.auto_assign.html}</td>
									</tr>
									
                                    <tr>
										<td class = "labelCell">{$smarty.const._DEADLINE}:&nbsp;</td>
										
										{if $_change_}
											<td class = "elementCell">{eF_template_html_select_date prefix="deadline_" time=$T_DEADLINE_TIMESTAMP start_year="-1" end_year="+5" field_order = $T_DATE_FORMATGENERAL} {$smarty.const._TIME}: {html_select_time prefix="deadline_" time = $T_DEADLINE_TIMESTAMP display_seconds = false}</td>
                                		{else}
											<td class = "elementCell">#filter:timestamp_time-{$T_DEADLINE_TIMESTAMP}#</td>
										{/if}
									</tr>
									<tr>
										<td></td>
										<td id = "toggleeditor_cell1">
											<div class = "headerTools">
												<span>
													<img onclick = "toggleFileManager(Element.extend(this).next());" class = "ajaxHandle" id = "arrow_down" src = "images/16x16/navigate_down.png" alt = "{$smarty.const._OPENCLOSEFILEMANAGER}" title = "{$smarty.const._OPENCLOSEFILEMANAGER}"/>&nbsp;
													<a href = "javascript:void(0)" onclick = "toggleFileManager(this);">{$smarty.const._TOGGLEFILEMANAGER}</a>
												</span>
												<span>
													<img onclick = "toggledInstanceEditor = 'editor_project_data';javascript:toggleEditor('editor_project_data','mceEditor');" class = "ajaxHandle" src = "images/16x16/order.png" title = "{$smarty.const._TOGGLEHTMLEDITORMODE}" alt = "{$smarty.const._TOGGLEHTMLEDITORMODE}" />&nbsp;
													<a href = "javascript:void(0)" onclick = "toggledInstanceEditor = 'editor_project_data';javascript:toggleEditor('editor_project_data','mceEditor');" id = "toggleeditor_link">{$smarty.const._TOGGLEHTMLEDITORMODE}</a>
												</span>
											</div>
										</td>
									</tr>
									<tr>
										<td colspan = "2" id = "filemanager_cell"></td>
									</tr>
									<tr>
										<td class = "labelCell">{$smarty.const._PROJECTDESCRIPTION}:&nbsp;</td>
										<td class = "elementCell">{$T_ADD_PROJECT_FORM.data.html}</td>
									</tr>
									
									{if $T_ADD_PROJECT_FORM.data.error}
									<tr>
											<td></td>
											<td class = "formError">{$T_ADD_PROJECT_FORM.data.error}</td>
									</tr>
									{/if}
                                    
									<tr>
										<td></td>
										<td class = "submitCell">{$T_ADD_PROJECT_FORM.submit_add_project.html}</td>
									</tr>
								</table>
							</form>
							<div id = "fmInitial">
								<div id = "filemanager_div" style = "display:none;">{$T_FILE_MANAGER}</div>
							</div>
						{/capture}
						{eF_template_printBlock tabber = "projects" title=$smarty.const._PROJECT data=$smarty.capture.t_projects_code image='32x32/projects.png'}

						{if $smarty.get.edit_project}
							<script>var editProject = '{$smarty.get.edit_project}';</script>
							
							{capture name = "t_project_users_code"}

<!--ajax:usersTable-->
							<table style = "width:100%" class = "sortedTable" size = "{$T_USERS_SIZE}" sortBy = "0" id = "usersTable" useAjax = "1" rowsPerPage = "{$smarty.const.G_DEFAULT_TABLE_SIZE}" url = "{$smarty.server.PHP_SELF}?ctg=projects&edit_project={$smarty.get.edit_project}&tab=project_users&">
								<tr>
									<td class = "topTitle" name = "login">{$smarty.const._USER}</td>
									<td class = "topTitle centerAlign" name = "checked">{$smarty.const._CHECK}</td>
								</tr>
								{foreach name = 'users_to_projects_list' key = 'key' item = 'user' from = $T_ALL_USERS}
								
								<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"} {if !$user.active}deactivatedTableElement{/if}">
									<td>#filter:login-{$user.login}#</td>
									
									<td class = "centerAlign">
									{if $_change_}
										<input class = "inputCheckbox" type = "checkbox" name = "checked_{$user.login}" id = "checked_{$user.login}" onclick = "usersAjaxPost('{$user.login}', this);" {if $user.checked}checked = "checked"{/if}/>
                                    {else}
										{if $user.checked}<img src = "images/16x16/success.png" alt = "{$smarty.const._PROJECTUSER}" title = "{$smarty.const._PROJECTUSER}">{/if}
									{/if}
									</td>
								</tr>
                                
                                {foreachelse}
                                
								<tr class = "defaultRowHeight oddRowColor"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NOUSERSFOUND}</td></tr>
                                
                                {/foreach}
                                
							</table>
<!--/ajax:usersTable-->
							
							{/capture}
							
							{eF_template_printBlock tabber = "project_users" title=$smarty.const._USERS data=$smarty.capture.t_project_users_code image='32x32/users.png'}

						{/if}
					</div>
				{/capture}
				
				{if $smarty.get.add_project}{assign var = "innerTableTitle" value = $smarty.const._ADDPROJECT}{else}{assign var = "innerTableTitle" value = "`$smarty.const._OPTIONSFORPROJECT`<span class='innerTableName'> &quot;`$T_CURRENT_PROJECT->project.title`&quot;</span>"}{/if}
				
				{eF_template_printBlock title=$innerTableTitle data=$smarty.capture.t_add_project_code image='32x32/projects.png'}

			{elseif $_professor_ && $smarty.get.project_results}
				
				<script>var editProject = '{$smarty.get.project_results}';</script>
				
				{capture name = "t_project_results_code"}
					
					{if isset($smarty.get.login)}

						{if isset($smarty.get.upload)}
						
							{$T_PROJECT_UPLOAD_FORM.javascript}
							<form {$T_PROJECT_UPLOAD_FORM.attributes}>
							{$T_PROJECT_UPLOAD_FORM.hidden}
							<table class = "formElements">
								<tr>
									<td class = "labelCell">{$smarty.const._UPLOAD}:&nbsp;</td>
									<td class = "elementCell">{$T_PROJECT_UPLOAD_FORM.filename.html}</td>
								</tr>
								<tr>
									<td></td>
									<td class = "submitCell">{$T_PROJECT_UPLOAD_FORM.submit.html}</td>
								</tr>
							</table>
	
							{if $T_MESSAGE_TYPE == 'success'}
								<script>parent.location = parent.location;</script>
							{/if}
						
						{else}						

							{$T_PROJECT_COMMENT_FORM.javascript}
							<form {$T_PROJECT_COMMENT_FORM.attributes}>
								{$T_PROJECT_COMMENT_FORM.hidden}
								<table class = "formElements">
									<tr>
										<td class = "labelCell">{$smarty.const._COMMENT}:&nbsp;</td>
										<td class = "elementCell">{$T_PROJECT_COMMENT_FORM.comments.html}</td>
									</tr>
									<tr>
										<td></td>
										<td class = "submitCell">{$T_PROJECT_COMMENT_FORM.submit.html}</td>
									</tr>
								</table>
	
								<hr>
						
								<table  style = "width:100%" >
									
								{foreach name = 'comments_list' key = 'index' item = 'entry' from = $T_COMMENTS}
									{foreach name = 'commentlist' key = 'login' item = 'comment' from = $entry}
								
									<tr class = "{cycle values = "oddRowColor, evenRowColor"}">
										<td>
											<div style = "float:right">
												{if $login == $T_CURRENT_USER->user.login}
												
												<img class = "ajaxHandle" src = "images/16x16/error_delete.png" alt = "{$smarty.const._DELETE}" title = "{$smarty.const._DELETE}" onclick = "if (confirm ('{$smarty.const._IRREVERSIBLEACTIONAREYOUSURE}')) deleteComment(this, '{$index}', '{$login}')"/>
												
												{/if}
											</div>
										
											{if !$login|@is_numeric} #filter:login-{$login}#:{/if} {$comment}
										</td>
									</tr>
										
									{/foreach}    
								{/foreach}
							
								</table>
								          
								{if $T_MESSAGE_TYPE == 'success'}
									<script>parent.location = parent.location;</script>
								{/if}
							
						{/if}	
					{else}

<!--ajax:resultsTable-->
						
						<table style = "width:100%" class = "sortedTable" size = "{$T_USERS_SIZE}" sortBy = "2" id = "resultsTable" useAjax = "1" rowsPerPage = "{$smarty.const.G_DEFAULT_TABLE_SIZE}" url = "professor.php?ctg=projects&project_results={$smarty.get.project_results}&">
							<tr>
								<td class = "topTitle" name = "users_LOGIN">{$smarty.const._STUDENT}</td>
								<td class = "topTitle" name = "file">{$smarty.const._FILENAME}</td>
								<td class = "topTitle" name = "upload_timestamp">{$smarty.const._UPLOADEDON}</td>
								<td class = "topTitle" name = "comments">{$smarty.const._COMMENTS}</td>
								<td class = "topTitle" name = "grade">{$smarty.const._SCORE}</td>
								<td class = "topTitle" name = "text_grade">{$smarty.const._GRADE}</td>
								<td class = "topTitle" name = "professor_upload_filename">{$smarty.const._PROFESSORSFEEDBACK}</td>								
								
								{if $_change_}
								<td class = "topTitle centerAlign">{$smarty.const._OPERATIONS}</td>
								{/if}
							</tr>
							
							{foreach name = 'users_to_projects_list' key = 'key' item = 'user' from = $T_ALL_USERS}
							
							<tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"} {if !$user.active}deactivatedTableElement{/if}">
								<td>#filter:login-{$user.users_LOGIN}#</td>
								<td>
									{if $user.filename}
									
									<a href = "view_file.php?file={$user.filename}"    target = "POPUP_FRAME" onclick = "eF_js_showDivPopup(event, '{$user.file}', 2)">{$user.file}</a>
									<a href = "view_file.php?file={$user.filename}&action=download" target = "POPUP_FRAME"><img src = "images/16x16/import.png" alt = "{$smarty.const._DOWNLOADFILE} {$user.file}" title = "{$smarty.const._DOWNLOADFILE} {$user.file}" border = "0" style = "vertical-align:middle"></a>
									<a href = "professor.php?ctg=projects&compress_user={$user.users_LOGIN}"><img border = "0" src = "images/file_types/zip.png"    alt = "{$smarty.const._USERPROJECTSCOMPRESSED}" title = "{$smarty.const._USERPROJECTSCOMPRESSED}"/></a>   
									
									{/if}
								</td>
								<td>
									{if $user.upload_timestamp != 'empty'}#filter:timestamp_time-{$user.upload_timestamp}#{/if}
									{*'empty' is set inside the php file, so that the sorting can be done correctly*}
								</td>    
								
								{if $_change_}

									<td>
										<span id = "comments_{$user.users_LOGIN}">{$user.comments|@strip_tags|eF_truncate:30}</span>&nbsp;<a href = "{$smarty.server.PHP_SELF}?ctg=projects&project_results={$smarty.get.project_results}&login={$user.users_LOGIN}&popup=1"  onclick = "eF_js_showDivPopup(event, '{$smarty.const._EDITCOMMENT}', 3)"  target = "POPUP_FRAME" ><img style="vertical-align:middle"  src = "images/16x16/edit.png" title = "{$smarty.const._EDIT}" alt = "{$smarty.const._EDIT}" /></a>
									</td>
	
									<td>
										<input type = "text" id = "grade_{$user.users_LOGIN}"    value = "{$user.grade|formatScore}"      size = "5" maxlength = "5" />
									</td>
									
									<td>
                                    	<input type = "text" id = "text_grade_{$user.users_LOGIN}"    value = "{$user.text_grade}"      size = "5" maxlength = "5" />
									</td>
                                        	
                                    <td>
                                    	{if $user.professor_upload_filename}
                                        	<a href = "view_file.php?file={$user.professor_upload_filename}"    target = "POPUP_FRAME" onclick = "eF_js_showDivPopup(event, '{$user.professor_upload_file}', 2)">{$user.professor_upload_file}</a>
                                            <a href = "view_file.php?file={$user.professor_upload_filename}&action=download" target = "POPUP_FRAME"><img src = "images/16x16/import.png" alt = "{$smarty.const._DOWNLOADFILE} {$user.file}" title = "{$smarty.const._DOWNLOADFILE} {$user.professor_upload_file}" border = "0" style = "vertical-align:middle"></a>
										{/if}
									</td>									
									
									<td class = "centerAlign">
										<img class = "ajaxHandle" src = "images/16x16/success.png" title = "{$smarty.const._SAVE}" alt = "{$smarty.const._SAVE}" onclick = "resultsAjaxPost('{$user.users_LOGIN}', this)"/>
										<img class = "ajaxHandle" src = "images/16x16/error_delete.png" title = "{$smarty.const._DELETE}" alt = "{$smarty.const._DELETE}" onclick = "resetUser('{$user.users_LOGIN}', this)"/>
									
										<a href = "{$smarty.server.PHP_SELF}?ctg=projects&project_results={$smarty.get.project_results}&login={$user.users_LOGIN}&popup=1&upload=1"  onclick = "eF_js_showDivPopup(event, '{$smarty.const._UPLOAD}', 3)"  target = "POPUP_FRAME" >
											<img class = "ajaxHandle" src = "images/16x16/folder_add.png" title = "{$smarty.const._UPLOAD}" alt = "{$smarty.const._UPLOAD}" />
										</a>									
									</td>

								{else}
									
									<td>{$user.comments}</td>
									<td>{$user.grade}</td>
								{/if}
							</tr>
							
							{foreachelse}
							
							<tr class = "defaultRowHeight oddRowColor"><td colspan = "100%" class = "centerAlign emptyCategory">{$smarty.const._NOUSERSFOUND}</td></tr>
                                
							{/foreach}
						</table>
<!--/ajax:resultsTable-->
				
					{/if}
				
				{/capture}
				{eF_template_printBlock title=$smarty.const._RESULTSFORPROJECT|cat:' &quot;'|cat:$T_CURRENT_PROJECT->project.title|cat:'&quot;' data=$smarty.capture.t_project_results_code image='32x32/projects.png'}

			{elseif $smarty.get.view_project}
                {capture name = "t_view_project_code"}
                	{if isset($smarty.get.add_comment)}

						{$T_PROJECT_COMMENT_FORM.javascript}
						<form {$T_PROJECT_COMMENT_FORM.attributes}>
							{$T_PROJECT_COMMENT_FORM.hidden}
							<table class = "formElements">
								<tr>
									<td class = "labelCell">{$smarty.const._COMMENT}:&nbsp;</td>
									<td class = "elementCell">{$T_PROJECT_COMMENT_FORM.comments.html}</td>
								</tr>
								<tr>
									<td></td>
									<td class = "submitCell">{$T_PROJECT_COMMENT_FORM.submit.html}</td>
								</tr>
							</table>
							
							<hr>
						
							<table  style = "width:100%" >
							
								{foreach name = 'comments_list' key = 'index' item = 'entry' from = $T_PROJECT_USER_INFO.comments}
									{foreach name = 'commentlist' key = 'login' item = 'comment' from = $entry}
							 		
									<tr class = "{cycle values = "oddRowColor, evenRowColor"}">
										<td>
											<div style = "float:right">
												{if $login == $T_CURRENT_USER->user.login}
												
												<img class = "ajaxHandle" src = "images/16x16/error_delete.png" alt = "{$smarty.const._DELETE}" title = "{$smarty.const._DELETE}" onclick = "if (confirm ('{$smarty.const._IRREVERSIBLEACTIONAREYOUSURE}')) deleteComment(this, '{$index}', '{$login}')"/>
												
												{/if}
											</div>
											{if !$login|@is_numeric} #filter:login-{$login}#:{/if} {$comment}
										</td>
									</tr>
							 	
									{/foreach}    
								{/foreach}
							
							</table>          
							
							{if $T_MESSAGE_TYPE == 'success'}
								<script>parent.location = parent.location;</script>
							{/if}
					
					{else}
					
						<table>
                        	<tr>
                        		<td>{$smarty.const._TITLE}:</td>
								<td>&nbsp;{$T_CURRENT_PROJECT->project.title}</td>
							</tr>
							<tr>
								<td>{$smarty.const._DEADLINE}:</td>
								<td>&nbsp;#filter:timestamp_time_nosec-{$T_CURRENT_PROJECT->project.deadline}#</td>
							</tr>
							<tr>
								<td>{$smarty.const._REMAINING}:</td>
								<td>&nbsp;{$T_CURRENT_PROJECT->timeRemaining}</td>
							</tr>
							<tr>
								<td colspan = "100%">&nbsp;</td>
							</tr>
							<tr>
								<td colspan = "2" style = "font-style:italic">{$T_CURRENT_PROJECT->project.data}</td>
							</tr>
						</table>
						
						<br/>

						<table width = "100%" class = "formElements">
                        
                        {if $T_PROJECT_FILE}
							<tr>
								<td>
									{$smarty.const._YOUHAVEALREADYUPLOADEDAFILE}:&nbsp;<a target = "_blank" href="view_file.php?file={$T_PROJECT_FILE.id}&action=download">{$T_PROJECT_FILE.name}</a>
                            		{if !$T_CURRENT_PROJECT->expired && $T_PROJECT_USER_INFO.grade == ''}
                                        &nbsp;&nbsp;<a href = "student.php?ctg=projects&view_project={$smarty.get.view_project}&delete_file=1"><img style = "vertical-align:middle" src = "images/16x16/error_delete.png" border = "0" alt="{$smarty.const._DELETE}" title="{$smarty.const._DELETE}" onclick = "return confirm ('{$smarty.const._IRREVERSIBLEACTIONAREYOUSURE}')"/></a>
                                        &nbsp;<span class = "infoCell">({$smarty.const._DELETETHEFILETOUPLOADANOTHER})</span>
									{/if}
                            	</td>
							</tr>
                        {/if}
						{if $T_PROFESSOR_FILE}
                        	<tr>
                        		{$smarty.const._PROFESSORSFEEDBACKANDRESPONSE}:&nbsp;<a target = "_blank" href="view_file.php?file={$T_PROFESSOR_FILE.id}&action=download">{$T_PROFESSOR_FILE.name}</a>
							</tr>
						{/if}                        
                    
                        {if ($T_PROJECT_USER_INFO.grade != '')}
							<tr>
                            	<td style = "color:red;">{$smarty.const._YOURPROJECTSCOREIS}:&nbsp;{$T_PROJECT_USER_INFO.grade|formatScore} 
		                        {if $T_PROJECT_USER_INFO.text_grade}
	    	                    	({$T_PROJECT_USER_INFO.text_grade})
								{/if}
	                            </td>
							</tr>
                        {/if}      
                        
						{if ($T_PROJECT_USER_INFO.comments)}
                           <tr><td><fieldset class = "fieldsetSeparator"><legend>{$smarty.const._COMMENTS}</legend></fieldset></td></tr>	
						{foreach name = 'comments_list' key = 'index' item = 'entry' from = $T_PROJECT_USER_INFO.comments}
							 	{foreach name = 'commentlist' key = 'login' item = 'comment' from = $entry}
							 		<tr width = "100%" class = "{cycle values = "oddRowColor, evenRowColor"}">
									<td>
										
										
										<div style = "float:right">
										{if $login === $T_CURRENT_USER->user.login}
											<img class = "ajaxHandle" src = "images/16x16/error_delete.png" alt = "{$smarty.const._DELETE}" title = "{$smarty.const._DELETE}" onclick = "if (confirm ('{$smarty.const._IRREVERSIBLEACTIONAREYOUSURE}')) deleteComment(this, '{$index}', '{$login}')"/>
										{/if}
										</div>
										{if !$login|@is_numeric} #filter:login-{$login}#:{/if} {$comment}
								
								</td></tr>
		                     	{/foreach}    
							 {/foreach}
                               
                        {/if}
                            
                       
                         <tr><td>
                         <a href = "{$smarty.server.PHP_SELF}?ctg=projects&view_project={$smarty.get.view_project}&add_comment=1&popup=1"  onclick = "eF_js_showDivPopup(event, '{$smarty.const._ADDCOMMENT}', 3)"  target = "POPUP_FRAME" ><img style="vertical-align:middle"  src = "images/16x16/edit.png" title = "{$smarty.const._COMMENT}" alt = "{$smarty.const._COMMENT}" />&nbsp;{$smarty.const._COMMENT}</a>
                          </td></tr>       
                        </table>

                        {if $T_PROJECT_USER_INFO.grade == '' && !$T_PROJECT_FILE}
                            {if !$T_CURRENT_PROJECT->expired}
                                {$T_UPLOAD_PROJECT_FORM.javascript}
                                <form {$T_UPLOAD_PROJECT_FORM.attributes}>
                                    {$T_UPLOAD_PROJECT_FORM.hidden}
                                    <table class = "formElements" style = "margin-left:0px">
                                        <tr><td class = "labelCell">{$smarty.const._FILE}:&nbsp;</td>
                                            <td>{$T_UPLOAD_PROJECT_FORM.filename.html}</td></tr>
                                        <tr><td></td><td class = "infoCell">{$smarty.const._FILESIZEMUSTBESMALLERTHAN} <b>{$T_MAX_FILE_SIZE}</b> {$smarty.const._KB}. {$smarty.const._YOUCANONLYUPLOADONEFILE}</td></tr>
                                        {if $T_UPLOAD_PROJECT_FORM.filename.error}<tr><td></td><td class = "formError">{$T_UPLOAD_PROJECT_FORM.filename.error}</td></tr>{/if}
                                        <tr><td colspan = "100%">&nbsp;</td></tr>
								{if $_student_}
                                        <tr><td></td><td>{$T_UPLOAD_PROJECT_FORM.submit_upload_project.html}</td></tr>
                                {/if}
                                    </table>
                                </form>
                            {else}
                                <img style = "vertical-align:middle;margin-right:5px;" src = "images/16x16/warning.png"/>
                                {$smarty.const._DEADLINEPASSEDYOUCANNOLONGERUPLOADFILES}
                            {/if}
                        {/if}

					{/if}
                {/capture}
                {eF_template_printBlock title="`$smarty.const._VIEWPROJECT`: `$T_CURRENT_PROJECT->project.title`" data=$smarty.capture.t_view_project_code image='32x32/projects.png'}

            {else}
                {capture name = "t_print_projects_code"}
                            {if $_change_ && $_professor_}
                                <div class = "headerTools">
                                    <span>
                                        <img src = "images/16x16/add.png" title = "{$smarty.const._ADDPROJECT}" alt = "{$smarty.const._ADDPROJECT}">
                                        <a href = "{$smarty.server.PHP_SELF}?ctg=projects&add_project=1" title = "{$smarty.const._ADDPROJECT}" >{$smarty.const._ADDPROJECT}</a>
                                    </span>
                                </div>
                            {/if}
                                <div class = "tabber">
								{capture name = "t_active_projects_code"}
									<table class = "sortedTable" width = "100%">
                                        <tr>
                                        	<td class = "topTitle">{$smarty.const._TITLE}</td>
                                            <td class = "topTitle">{$smarty.const._DEADLINE}</td>
                                            <td class = "topTitle">{$smarty.const._TIMEREMAIN}</td>
                                            
                        					{if $_student_}
                                            <td class = "topTitle centerAlign">{$smarty.const._SCORE}</td>
                                            <td class = "topTitle centerAlign">{$smarty.const._GRADE}</td>
                                            <td class = "topTitle centerAlign noSort">{$smarty.const._STATUS}</td>
                        				
                        					{else}
                                            
                                            <td class = "topTitle centerAlign">{$smarty.const._STUDENTS}</td>
                                            <td class = "topTitle centerAlign noSort">{$smarty.const._FUNCTIONS}</td>
                        					
                        					{/if}
                                        </tr>
                                        
                        				{foreach name = 'projects_list' key = 'key' item = 'project' from = $T_CURRENT_PROJECTS}
                                        
                                        <tr class = "defaultRowHeight {cycle values = "oddRowColor, evenRowColor"}">
                                            <td><a href = "{$smarty.server.PHP_SELF}?ctg=projects&{if $_student_}view_project{else}edit_project{/if}={$project->project.id}">{$project->project.title}</a></td>
                                            <td><span style = "display:none">{$project->project.deadline}</span>#filter:timestamp_time_nosec-{$project->project.deadline}#</td>
                                            <td><span style = "display:none">{$project->project.deadline}</span>{$project->timeRemaining}</td>
                                            
                        {if $_student_}
                                            <td class = "centerAlign">{$project->project.grade}</td>
                                            <td class = "centerAlign">{$project->project.text_grade}</td>
                                            <td class = "centerAlign">
                            {if $project->project.filename && $project->project.grade}
                                                <img src = "images/16x16/success.png" title = "{$smarty.const._PROJECTFINISHED}" alt = "{$smarty.const._PROJECTFINISHED}" />
                            {elseif $project->project.filename}
                                                <img src = "images/16x16/success.png" title = "{$smarty.const._FILEUPLOADEDAWAITINGGRADE}" alt = "{$smarty.const._FILEUPLOADEDAWAITINGGRADE}" />
                            {else}
                                                <img src = "images/16x16/clock.png" title = "{$smarty.const._PENDING}" alt = "{$smarty.const._PENDING}" />
                            {/if}
                        {else}
                                            <td class = "centerAlign">{$project->doneUsers}/{$project->doneUsers+$project->pendingUsers}</td>
                                            <td class = "centerAlign">
                                            	<a href = "professor.php?ctg=projects&view_project={$project->project.id}"> <img src = "images/16x16/search.png" alt = "{$smarty.const._PREVIEW}" title = "{$smarty.const._PREVIEW}"/></a>
                            {if $_change_}
                                                <a href = "professor.php?ctg=projects&edit_project={$project->project.id}"> <img border = "0" src = "images/16x16/edit.png"           alt = "{$smarty.const._EDIT}"               title = "{$smarty.const._EDIT}"/></a>
                            {/if}
                                                <a href = "professor.php?ctg=projects&project_results={$project->project.id}"> <img border = "0" src = "images/16x16/unit.png" alt = "{$smarty.const._SCORE}"              title = "{$smarty.const._SCORE}"/></a>
                            {if $project->doneUsers > 0}<a href = "professor.php?ctg=projects&compress_data={$project->project.id}"><img border = "0" src = "images/file_types/zip.png"    alt = "{$smarty.const._PROJECTSCOMPRESSED}" title = "{$smarty.const._PROJECTSCOMPRESSED}"/></a>{/if}
                            {if $_change_}
                                                <img class = "ajaxHandle" src = "images/16x16/error_delete.png" alt = "{$smarty.const._DELETE}" title = "{$smarty.const._DELETE}" onclick = "if (confirm('{$smarty.const._AREYOUSUREYOUWANTTODELETEPROJECT}')) deleteProject(this, '{$project->project.id}');"/>
                            {/if}
                                            </td>
                        {/if}
                                        </tr>
                        {foreachelse}
                                        <tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "100%">{$smarty.const._NODATAFOUND}</td></tr>
                        {/foreach}
                                    </table>
								{/capture}
								{eF_template_printBlock tabber = "active" title="`$smarty.const._ACTIVE_PROJECTS` (`$T_ACTIVE_COUNT`)" data=$smarty.capture.t_active_projects_code image='32x32/projects.png'}

                                {capture name = "t_inactive_projects_code"}
									<table class = "sortedTable" width = "100%">
                                        <tr><td class = "topTitle">{$smarty.const._TITLE}</td>
                                            <td class = "topTitle">{$smarty.const._DEADLINE}</td>
                                            
                        {if $_student_}
                                            <td class = "topTitle centerAlign">{$smarty.const._GRADE}</td>
                                            <td class = "topTitle centerAlign noSort">{$smarty.const._STATUS}</td>
						{else}
                                            <td class = "topTitle centerAlign">{$smarty.const._STUDENTS}</td>
                                            <td class = "topTitle centerAlign noSort">{$smarty.const._FUNCTIONS}</td>
						{/if}
                                        </tr>
                        {foreach name = 'projects_list' key = 'key' item = 'project' from = $T_EXPIRED_PROJECTS}
                                        <tr class = "{cycle values = "oddRowColor, evenRowColor"}">
                                            <td><a href = "{$smarty.server.PHP_SELF}?ctg=projects&{if $_student_}view_project{else}edit_project{/if}={$project->project.id}">{$project->project.title}</a></td>
                                            <td><span style = "display:none">{$project->project.deadline}</span>#filter:timestamp_time_nosec-{$project->project.deadline}#</td>
                                            
                        {if $_student_}
                                            <td class = "centerAlign">{$project->project.grade}</td>
                                            <td class = "centerAlign">{$project->project.text_grade}</td>
                                            <td class = "centerAlign">
                            {if $project->project.filename && $project->project.grade}
                                                <img src = "images/16x16/success.png" title = "{$smarty.const._PROJECTFINISHED}" alt = "{$smarty.const._PROJECTFINISHED}" />
                            {elseif $project->project.filename}
                                                <img src = "images/16x16/success.png" title = "{$smarty.const._FILEUPLOADEDAWAITINGGRADE}" alt = "{$smarty.const._FILEUPLOADEDAWAITINGGRADE}" />
                            {else}
                                                <img src = "images/16x16/warning.png" title = "{$smarty.const._DEADLINEPASSED}" alt = "{$smarty.const._DEADLINEPASSED}" />
                            {/if}
                                            </td>
                        {else}
                                            <td class = "centerAlign">{$project->doneUsers}/{$project->doneUsers+$project->pendingUsers}</td>
                                            <td class = "centerAlign">
                                                <a href = "professor.php?ctg=projects&view_project={$project->project.id}"> <img src = "images/16x16/search.png" alt = "{$smarty.const._PREVIEW}" title = "{$smarty.const._PREVIEW}"/></a>
                            {if $_change_}
                                                <a href = "professor.php?ctg=projects&edit_project={$project->project.id}"> <img border = "0" src = "images/16x16/edit.png"           alt = "{$smarty.const._EDIT}"               title = "{$smarty.const._EDIT}"/></a>
                            {/if}
                                                <a href = "professor.php?ctg=projects&project_results={$project->project.id}"> <img border = "0" src = "images/16x16/unit.png" alt = "{$smarty.const._SCORE}"              title = "{$smarty.const._SCORE}"/></a>
                            {if $project->doneUsers > 0}<a href = "professor.php?ctg=projects&compress_data={$project->project.id}"><img border = "0" src = "images/file_types/zip.png"    alt = "{$smarty.const._PROJECTSCOMPRESSED}" title = "{$smarty.const._PROJECTSCOMPRESSED}"/></a>{/if}
                            {if $_change_}
                                                <img class = "ajaxHandle" src = "images/16x16/error_delete.png" alt = "{$smarty.const._DELETE}" title = "{$smarty.const._DELETE}" onclick = "if (confirm('{$smarty.const._AREYOUSUREYOUWANTTODELETEPROJECT}')) deleteProject(this, '{$project->project.id}');"/>
                            {/if}
                                            </td>
                        {/if}
                                        </tr>
                        {foreachelse}
                                        <tr class = "defaultRowHeight oddRowColor"><td class = "emptyCategory" colspan = "100%">{$smarty.const._NODATAFOUND}</td></tr>
                        {/foreach}
                                    </table>
								{/capture}
                                {eF_template_printBlock tabber = "inactive" title="`$smarty.const._INACTIVE_PROJECTS` (`$T_INACTIVE_COUNT`)" data=$smarty.capture.t_inactive_projects_code image='32x32/projects.png'}

                                </div>
                {/capture}

                {eF_template_printBlock title=$smarty.const._PROJECTS data=$smarty.capture.t_print_projects_code image='32x32/projects.png' help='Projects'}
            {/if}
                                </td></tr>
        {/capture}