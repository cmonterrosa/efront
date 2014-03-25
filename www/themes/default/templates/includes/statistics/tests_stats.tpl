{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}
    {capture name='test_statistics'}
            <table class = "statisticsSelectList">
                <tr><td class = "labelCell">{$smarty.const._CHOOSETEST}:</td>
                    <td class = "elementCell">
                        <input type = "text" id = "autocomplete" class = "autoCompleteTextBox"/>
                        <img id = "busy" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
                        <div id = "autocomplete_tests" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
                <tr><td></td>
                	<td class = "infoCell">{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td></tr>
            </table>

    {if isset($T_TEST_INFO)}

            <table class = "statisticsTools">
                <tr><td class = "filter">
			        <select style = "vertical-align:middle" name = "group_filter" onchange = "if (this.options[this.selectedIndex].value != '') document.location='{$smarty.server.PHP_SELF}?ctg=statistics&option=test&sel_test={$smarty.get.sel_test}&group_filter='+this.options[this.selectedIndex].value">
			                <option value = "-1" class = "inactiveElement" {if !$smarty.get.group_filter}selected{/if}>{$smarty.const._SELECTGROUP}</option>
			            {foreach name = "group_options" from = $T_GROUPS item = 'group' key='id'}
			                <option value = "{$group.id}" {if $smarty.get.group_filter == $group.id}selected{/if}>{$group.name}</option>
			            {/foreach}
			        </select>
			    </td>
    			<td id = "right">
                    {$smarty.const._EXPORTSTATS}
                    <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=test&sel_test={$smarty.get.sel_test}&excel=1{if $smarty.get.group_filter}&group_filter={$smarty.get.group_filter}{/if}">
                        <img src = "images/file_types/xls.png" title = "{$smarty.const._XLSFORMAT}" alt = "{$smarty.const._XLSFORMAT}" />
                    </a>
                    <a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=test&sel_test={$smarty.get.sel_test}&pdf=1{if $smarty.get.group_filter}&group_filter={$smarty.get.group_filter}{/if}">
                        <img src = "images/file_types/pdf.png" title = "{$smarty.const._PDFFORMAT}" alt = "{$smarty.const._PDFFORMAT}" />
                    </a>
			<!--	<a href = "{$T_BASIC_TYPE}.php?ctg=statistics&option=test&sel_test={$smarty.get.sel_test}&total_export=1">
                    <img src = "images/file_types/pdf.png" title = "{$smarty.const._TOTALTESTEXPORT}" alt = "{$smarty.const._PDFFORMAT}" />
					</a> -->

	                <img class = "ajaxHandle"  src = "images/16x16/reports.png" alt = "{$smarty.const._SCOREDISTRIBUTION}" title = "{$smarty.const._SCOREDISTRIBUTION}" onclick = "eF_js_showDivPopup(event, '{$smarty.const._ACCESSSTATISTICS}', 2, 'graph_table');showGraph($('proto_chart'), 'graph_test_score');"/>
					<img class = "ajaxHandle" src = "images/16x16/reports.png" alt = "{$smarty.const._CUMULATIVESCOREDISTRIBUTION}" title = "{$smarty.const._CUMULATIVESCOREDISTRIBUTION}" onclick = "eF_js_showDivPopup(event, '{$smarty.const._ACCESSSTATISTICS}', 2, 'graph_table');showGraph($('proto_chart'), 'graph_test_cumulative_score');"/>
					<div id = "graph_table" style = "display:none"><div id = "proto_chart" class = "proto_graph"></div></div>
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
            <tr class = "{cycle name = 'test_common_info' values = 'oddRowColor, evenRowColor'}">
                <td class = "labelCell" >{$smarty.const._TESTDURATION}:</td>
                <td class = "elementCell">
                	{if $T_TEST_INFO.general.duration}
                    	{if $T_TEST_INFO.general.duration_str.hours > 1}{$T_TEST_INFO.general.duration_str.hours} {$smarty.const._HOURS}
                    	{elseif $T_TEST_INFO.general.duration_str.hours == 1}{$T_TEST_INFO.general.duration_str.hours} {$smarty.const._HOUR}{/if}
                    	{if $T_TEST_INFO.general.duration_str.minutes > 1}{$T_TEST_INFO.general.duration_str.minutes} {$smarty.const._MINUTES}
                    	{elseif $T_TEST_INFO.general.duration_str.minutes == 1}{$T_TEST_INFO.general.duration_str.minutes} {$smarty.const._MINUTE}{/if}
                    	{if $T_TEST_INFO.general.duration_str.seconds > 1}{$T_TEST_INFO.general.duration_str.seconds} {$smarty.const._SECONDS}
                    	{elseif $T_TEST_INFO.general.duration_str.seconds == 1}{$T_TEST_INFO.general.duration_str.seconds} {$smarty.const._SECOND}{/if}
                    {else}
                    	{$smarty.const._UNLIMITED}
                    {/if}
                	</td></tr>
            </tr>
            <tr class = "{cycle name = 'test_common_info' values = 'oddRowColor, evenRowColor'}">
                <td class = "labelCell" >{$smarty.const._TIMESDONE}:</td>
                <td class = "elementCell">{$T_TEST_TIMES_DONE}</td></tr>
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
                        <td class = "topTitle centerAlign">{$smarty.const._CORRECTANSWERPERCENTAGE}</td>
					</tr>
				{foreach name = 'question_list' key = "key" item = "question" from = $T_TEST_QUESTIONS}

					<tr class = "{cycle name = 'test_questions' values = 'oddRowColor, evenRowColor'}">
						<td>{$question->question.preview}</td>
						<td class = "centerAlign">#filter:score-{$T_TEST_QUESTIONS_STATS[$key].correct_percent}#%</td>
					</tr>

                {foreachelse}
                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                {/foreach}
				</table>
            </div>
        	{/if}


            {*Test analysis*}
            <div class = "statisticsDiv tabbertab" title = "{$smarty.const._TESTANALYSIS}">

                <table class = "sortedTable" style = "margin-bottom:10px">
                    <tr>
                        <td class = "topTitle" width="20%">{$smarty.const._SCORE}</td>
                        <td class = "topTitle centerAlign" width="20%">{$smarty.const._PARTICIPANTSCAP}</td>
                        <td class = "topTitle centerAlign" width="30%">{$smarty.const._PERCENT}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._PERCENTWITHSCOREHIGHEREQUAL}</td>
					</tr>
				{foreach name = 'results_list' key = "key" item = "score_category" from = $T_TEST_SCORE_CATEGORIES}

					<tr class = "{cycle name = 'test_questions' values = 'oddRowColor, evenRowColor'}">
						<td><span style="display:none">{$score_category.from}</span>{$smarty.const._FROM} {$score_category.from}% {$smarty.const._TO} {$score_category.to}%</td>
						<td class = "centerAlign">{$score_category.count}</td>
						<td class = "centerAlign">#filter:score-{$score_category.percent}#%</td>
						<td class = "centerAlign">#filter:score-{$score_category.sum_count_percent}#%</td>
					</tr>

                {foreachelse}
                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                {/foreach}
				</table>

            </div>

			{if !$T_TEST_INFO.general.scorm}
            <div class = "statisticsDiv tabbertab" title = "{$smarty.const._QUESTIONS}">
                <table class = "sortedTable">
                    <tr>
                        <td class = "topTitle">{$smarty.const._QUESTIONTEXT}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._QUESTIONTYPE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._DIFFICULTY}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._WEIGHT}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._TIMESDONE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._AVERAGESCORE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._MINSCORE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._MAXSCORE}</td>
                    </tr>
                    {foreach name = 'question_list' key = "key" item = "question" from = $T_TEST_QUESTIONS}
                        <tr class = "{cycle name = 'test_questions' values = 'oddRowColor, evenRowColor'}">
                            <td>{$question->question.plain_text}</td>
                            <td class = "centerAlign">
                            	{assign var = "qtype" value = $question->question.type}
                            	<span style = "display:none">{$question->question.type}</span>
                            	<img src = "{$question->question.type_icon}" title = "{$T_TEST_QUESTIONS_TRANSLATIONS[$qtype]}" alt = "{$T_TEST_QUESTIONS_TRANSLATIONS[$qtype]}"></td>
                            <td class = "centerAlign">
                                <span style = "display:none">{$question->question.difficulty}</span>
                                {if $question->question.difficulty == 'low'}        <img src = "images/16x16/flag_green.png" title = "{$smarty.const._LOW}"    alt = "{$smarty.const._LOW}" />
                                {elseif $question->question.difficulty == 'medium'} <img src = "images/16x16/flag_blue.png"  title = "{$smarty.const._MEDIUM}" alt = "{$smarty.const._MEDIUM}" />
                                {elseif $question->question.difficulty == 'high'}   <img src = "images/16x16/flag_red.png"   title = "{$smarty.const._HIGH}"   alt = "{$smarty.const._HIGH}" />
                                {/if}
                            </td>
                            <td class = "centerAlign">{$question->question.weight}</td>
                            <td class = "centerAlign">{if $T_TEST_QUESTIONS_STATS[$key].times_done}{$T_TEST_QUESTIONS_STATS[$key].times_done}{else}0{/if}</td>
                            <td class = "centerAlign">#filter:score-{$T_TEST_QUESTIONS_STATS[$key].avg_score}#%</td>
                            <td class = "centerAlign">#filter:score-{$T_TEST_QUESTIONS_STATS[$key].min_score}#%</td>
                            <td class = "centerAlign">#filter:score-{$T_TEST_QUESTIONS_STATS[$key].max_score}#%</td>
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
                        <td class = "topTitle">{$smarty.const._USER}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._TIMESDONE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._AVERAGESCORE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._MINSCORE}</td>
                        <td class = "topTitle centerAlign">{$smarty.const._MAXSCORE}</td>
					</tr>
				{foreach name = 'question_list' key = "login" item = "test" from = $T_TEST_STATS}
					<tr class = "{cycle name = 'test_questions' values = 'oddRowColor, evenRowColor'}">
						<td>#filter:login-{$login}#</td>
						<td class = "centerAlign">{$test.times_done}</td>
						<td class = "centerAlign">#filter:score-{$test.average_score}#%</td>
						<td class = "centerAlign">#filter:score-{$test.min_score}#%</td>
						<td class = "centerAlign">#filter:score-{$test.max_score}#%</td>
					</tr>
                {foreachelse}
                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                {/foreach}
				</table>
				<table class = "statisticsTable" style = "margin-top:20px;">
					<tr><td>
                            <a href = "javascript:void(0)" onclick = "toggleVisibility($('details'), Element.extend(this).down())">
            					<img src = "images/16x16/navigate_down.png" title = "{$smarty.const._SHOWHIDE}" alt = "{$smarty.const._SHOWHIDE}"/>
            					{$smarty.const._SHOWDETAILS}</a>
					</td></tr>
				</table>
				<div id = "details" style = "display:none">
			{foreach name = 'question_list' key = "login" item = "test" from = $T_TEST_STATS}
                <table class = "sortedTable" style = "margin-bottom:10px">
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
						<td class = "centerAlign">#filter:score-{$T_TEST_STATS.$login.scores[$id]}#%</td>
					</tr>
					{/if}
                {foreachelse}
                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                {/foreach}
				</table>
			{/foreach}
				</div>
            </div>

            <div class = "statisticsDiv tabbertab {if $smarty.get.tab == 'details'}tabbertabdefault{/if}" title = "{$smarty.const._RESPONSEDETAILS}">
                <table class = "sortedTable" style = "margin-bottom:10px">
                    <tr>
                        <td class = "topTitle noSort">{$smarty.const._QUESTIONTEXT}</td>
                        <td class = "topTitle noSort centerAlign">{$smarty.const._QUESTIONTYPE}</td>
                        <td class = "topTitle noSort" style = "width:150px">{$smarty.const._USER}</td>
                        <td class = "topTitle noSort">{$smarty.const._RESPONSEDETAILS}</td>
					</tr>
				{foreach name = 'question_list' key = "key" item = "question" from = $T_TEST_QUESTIONS}
					<tr class = "{cycle name = 'test_questions' values = "oddRowColor, evenRowColor"}" style = "border:1px dotted #D0D0D0;">
						<td>
							{$question->question.plain_text}
						</td>
                        <td class = "centerAlign">
                            {assign var = "qtype" value = $question->question.type}
                            <span style = "display:none">{$question->question.type}</span>
                            <img src = "{$question->question.type_icon}" title = "{$T_TEST_QUESTIONS_TRANSLATIONS[$qtype]}" alt = "{$T_TEST_QUESTIONS_TRANSLATIONS[$qtype]}">
                        </td>
                        <td colspan="2" >
                        	<table style = "width:100%">
					{foreach name = 'response_list' key = "user" item = "responses" from = $T_RESPONSE_DETAILS[$key]}
						{foreach name = 'user_responses_list' key = "foo" item = "response" from = $responses}
                        	<tr class = "{cycle name = 'responses' values = "oddRowColor, evenRowColor"}">
                        		<td style = "width:150px">#filter:login-{$user}#</td>
                        		<td>{$response}</td></tr>
						{/foreach}
					{/foreach}
							</table>
						</td>
                     </tr>
                {foreachelse}
                    <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                {/foreach}
				</table>

            </div>
        </div>
    {/if}

    {/capture}
    {if $T_TEST_NAME != ""}
    	{eF_template_printBlock title="`$smarty.const._REPORTSFORTEST` <span class='innerTableName'>&quot;`$T_TEST_NAME`&quot;</span>" data=$smarty.capture.test_statistics image='32x32/tests.png' help = 'Reports'}
	{else}
		{eF_template_printBlock title=$smarty.const._TESTSTATISTICS data=$smarty.capture.test_statistics image='32x32/tests.png' help = 'Reports'}
	{/if}
{/if} {* #cpp#endif *}