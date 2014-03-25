{*moduleCopyContent: Content tree to change order*}
{capture name = "moduleCopyContent"}
	<tr><td class = "moduleCell">
	    {capture name = 't_copy_content_code'}
	        <table style = "width:100%">
            	<tr><td class = "labelCell">{$smarty.const._COPYPROPERTIESFROM}:</td>
                    <td class = "elementCell">
                  	  <input type = "hidden" id = "copy_properties"/>
                        <input type = "text" id = "autocomplete_copy" class = "autoCompleteTextBox"/>
                        <img id = "busy_copy" src = "images/16x16/clock.png" style="display:none;" alt = "{$smarty.const._LOADING}" title = "{$smarty.const._LOADING}"/>
                        <div id = "autocomplete_lessons_copy" class = "autocomplete"></div>&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
	            <tr><td></td>
                		<td class = "infoCell">{$smarty.const._STARTTYPINGFORRELEVENTMATCHES}</td></tr>
	        </table>
			<br/>
        {if $smarty.get.from}
        	<script>var TransferedNodes = "";</script>
	        <table class = "copyContent">
	            <tr><td style = "width:50%">{eF_template_printBlock title = $smarty.const._DRAGAUNITTOCOPY data = $T_SOURCE_TREE image = "32x32/content.png" alt = '<span class = "emptyCategory">'|cat:$smarty.const._NOCONTENTFOUND|cat:'</span>'}</td>
	            	<td style = "width:50%">{eF_template_printBlock title = $smarty.const._DROPAUNITTOCOPY data = $T_CONTENT_TREE image = "32x32/content.png" alt = '<span class = "emptyCategory">'|cat:$smarty.const._NOCONTENTFOUND|cat:'</span>'}</td></tr>
	            <tr><td></td>
	            	<td>
	            		<input id = "save_button" class = "flatButton" type = "button" onclick = "this.disabled=true;saveTree(this)" value = "{$smarty.const._COPY}" />
	            		<input id = "save_button" class = "flatButton" type = "button" onclick = "this.disabled=true;saveTree(this, 'link')" value = "{$smarty.const._LINK}" />
	            	</td></tr>
	        </table>	        

	        <fieldset class = "fieldsetSeparator">
            	<legend>{$smarty.const._COPYOTHERENTITIES}</legend>
				<table>
                {if 'glossary'|eF_template_isOptionVisible}
					<tr><td class = "labelCell">{$smarty.const._COPYGLOSSARY}:&nbsp;</td>
						<td class = "elementCell"><input type = "submit" class = "flatButton" value = "{$smarty.const._COPY}" onclick = "copyLessonEntity(this, 'glossary')"></td></tr>
                {/if}
{if ($smarty.const.G_VERSIONTYPE != 'community')} {* #cpp#ifndef COMMUNITY *}
                {if 'surveys'|eF_template_isOptionVisible}
					<tr><td class = "labelCell">{$smarty.const._COPYSURVEYS}:&nbsp;</td>
						<td class = "elementCell"><input type = "submit" class = "flatButton" value = "{$smarty.const._COPY}" onclick = "copyLessonEntity(this, 'surveys')"></td></tr>
                {/if}
{/if} {* #cpp#endif *}					
					<tr><td class = "labelCell">{$smarty.const._COPYQUESTIONS}:&nbsp;</td>
						<td class = "elementCell"><input type = "submit" class = "flatButton" value = "{$smarty.const._COPY}" onclick = "copyLessonEntity(this, 'questions')"></td></tr>
				</table>
			</fieldset>
            
		{/if}
        {/capture}
        {eF_template_printBlock title=$smarty.const._COPYFROMANOTHERLESSON data=$smarty.capture.t_copy_content_code image='32x32/lesson_copy.png' help = 'Copy_from_another_lesson'}
	</td></tr>
{/capture}
                                
