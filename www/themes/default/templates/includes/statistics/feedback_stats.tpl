{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
    {capture name='test_statistics'}
	{if isset($smarty.get.question_ID)}
		<table class = "sortedTable statisticsGeneralInfo" style = "margin-bottom:10px">
                    <tr>
                        <td class = "topTitle">{$smarty.const._STUDENT}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._STUDENTANSWER}</td>
					</tr>
				{foreach name = 'students_list' key = "key" item = "item" from = $T_TEST_COMPLETED}
					
					<tr class = "{cycle name = 'test_students' values = 'oddRowColor, evenRowColor'}">
						<td>#filter:login-{$item.users_LOGIN}#</td>

						<td class = "centerAlign">
							{assign var = 'questions' value = $item.test->questions}
							{$questions[$smarty.get.question_ID]->question.preview}
						{*{$item.preview}  *}
						</td>					
					</tr>
					
                {foreachelse}
                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                {/foreach}      
				</table>     
	
	{else}


		   <table class = "statisticsSelectList">
                <tr><td class = "labelCell">{$smarty.const._CHOOSEFEEDBACK}:</td>
                    <td class = "elementCell">
                        <input type = "text" id = "autocomplete" class = "autoCompleteTextBox"/>
                        <img id = "busy" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/> 
                        <div id = "autocomplete_feedback" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
                <tr><td></td>
                	<td class = "infoCell">{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td></tr>     
            </table>       

	{if isset($T_TEST_INFO)}
            
            <table class = "statisticsTools">
                <tr><td id = "right">
                    {$smarty.const._EXPORTSTATS}
                    <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=feedback&sel_test={$smarty.get.sel_test}&excel=1">
                        <img src = "images/file_types/xls.png" title = "{$smarty.const._XLSFORMAT}" alt = "{$smarty.const._XLSFORMAT}" />
                    </a>
                    <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=feedback&sel_test={$smarty.get.sel_test}&pdf=1">
                        <img src = "images/file_types/pdf.png" title = "{$smarty.const._PDFFORMAT}" alt = "{$smarty.const._PDFFORMAT}" />
                    </a>
                </td></tr>
	        </table>            
        	<br/>
        
        <table class = "statisticsGeneralInfo">
            <tr class = "{cycle name = 'test_common_info' values = 'oddRowColor, evenRowColor'}">                
                <td class = "labelCell">{$smarty.const._NAME}:</td>
                <td class = "elementCell">{$T_TEST_INFO.general.name} {if $T_TEST_INFO.general.scorm}(SCORM){/if}</td></tr>
            </tr>
            <tr class = "{cycle name = 'test_common_info' values = 'oddRowColor, evenRowColor'}">                
                <td class = "labelCell" >{$smarty.const._LESSON}:</td>
                <td class = "elementCell">{$T_TEST_INFO.general.lesson_name}</td></tr>
            </tr>           
            {if !$T_TEST_INFO.general.scorm}
            <tr class = "{cycle name = 'test_common_info' values = 'oddRowColor, evenRowColor'}">                
                <td class = "labelCell" >{$smarty.const._QUESTIONS}:</td>
                <td class = "elementCell">{$T_TEST_INFO.questions.total}</td></tr>
            </tr>
            {/if}
        </table>                

        <div class = "tabber">
        
        	{if !$T_TEST_INFO.general.scorm}
            {*Question analysis*}
            <div class = "statisticsDiv tabbertab" title = "{$smarty.const._QUESTIONANALYSIS}">    
                <table class = "sortedTable" style = "margin-bottom:10px">
                    <tr>
                        <td class = "topTitle">{$smarty.const._QUESTIONTEXT}</td>
						<td class = "topTitle centerAlign">{$smarty.const._TYPE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._DETAILS}</td>
					</tr>
				{foreach name = 'question_list' key = "key" item = "question" from = $T_TEST_QUESTIONS}
					
					<tr class = "{cycle name = 'test_questions' values = 'oddRowColor, evenRowColor'}">
						<td>
						{if $question->question.type == 'multiple_one' || $question->question.type == 'multiple_many'}
							{$question->question.preview_percent}
						{else}
							{$question->question.text}
						{/if}						
						</td>
						<td class = "centerAlign"><img src="{$question->question.type_icon}" /> </td>
						<td class = "centerAlign">
						
						<a href = "{$smarty.server.PHP_SELF}?ctg=statistics&option=feedback&sel_test={$smarty.get.sel_test}&question_ID={$key}">
		              			<img src = "images/16x16/search.png" alt = "{$smarty.const._ANSWERS}" title = "{$smarty.const._ANSWERS}"/></a>
						</td>

					
					</tr>
					
                {foreachelse}
                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                {/foreach}      
				</table>            						                
            </div>        
        	{/if}       
        
            
            <div class = "statisticsDiv tabbertab {if $smarty.get.tab == 'users'}tabbertabdefault{/if}" title = "{$smarty.const._USERS}">
            
                <table class = "sortedTable">
                    <tr>
						<td class = "topTitle">{$smarty.const._COMPLETEDON}</td>
						<td class = "topTitle">{$smarty.const._STUDENT}</td>
						<td class = "topTitle centerAlign noSort">{$smarty.const._FUNCTIONS}</td>
					</tr>
				{foreach name = 'question_list' key = "login" item = "test" from = $T_TEST_STATS}
					<tr class = "{cycle name = 'test_questions' values = 'oddRowColor, evenRowColor'}">
					{assign var = 'testID' value = $test.last_test_id}
						<td>#filter:timestamp_time-{if isset($test.$testID.time_end)}{$test.$testID.time_end}{else}{$test.$testID.timestamp}{/if}#</td>
						<td>#filter:login-{$login}#</td>
		                <td class = "centerAlign">
							<a href = "view_test.php?done_test_id={$testID}&popup=1" onclick = "eF_js_showDivPopup(event, '{$smarty.const._VIEWFEEDBACK}', 3)" target = "POPUP_FRAME">
		              			<img src = "images/16x16/search.png" alt = "{$smarty.const._VIEWFEEDBACK}" title = "{$smarty.const._VIEWFEEDBACK}"/></a>
						</td>
					</tr>
                {foreachelse}
                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                {/foreach}      
				</table>
            </div>
        </div>
    {/if}
    {/if}   
    {/capture}
    
	{if $T_TEST_NAME != ""}
    	{eF_template_printBlock title="`$smarty.const._REPORTSFORFEEDBACK` <span class='innerTableName'>&quot;`$T_TEST_NAME`&quot;</span>" data=$smarty.capture.test_statistics image='32x32/feedback.png' help = 'Reports'} 
	{else}
		{eF_template_printBlock title=$smarty.const._FEEDBACKSTATISTICS data=$smarty.capture.test_statistics image='32x32/feedback.png' help = 'Reports'} 
	{/if} 

{/if} {* #cpp#endif *}