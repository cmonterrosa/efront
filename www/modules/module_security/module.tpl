{capture name = "t_code"}

{if $smarty.get.type == 'install'}
	<h1>{$smarty.const._MODULE_SECURITY_INSTALLATIONFOLDERSTILLEXISTS}</h1>
	<br/>
	<h3>{$smarty.const._MODULE_SECURITY_WHATSTHERISK}</h3>
	<p>{$smarty.const._MODULE_SECURITY_INSTALLATIONRISKEXPLANATION}</p>
	
	<h3>{$smarty.const._MODULE_SECURITY_WHATCANIDO}</h3>
    {$T_SECURITY_FORM.javascript}
    <form {$T_SECURITY_FORM.attributes}>
        {$T_SECURITY_FORM.hidden}
    	{$T_SECURITY_FORM.submit_ignore.html}
        {$T_SECURITY_FORM.submit_delete_install.html}
    </form> 	
{elseif $smarty.get.type == 'magic_quotes_gpc'}
	<h1>{$smarty.const._MODULE_SECURITY_MAGICQUOTESGPCISON}</h1>
	<br/>
	<h3>{$smarty.const._MODULE_SECURITY_WHATSTHERISK}</h3>
	<p>{$smarty.const._MODULE_SECURITY_MAGICQUOTESEXPLANATION}</p>
	
	<h3>{$smarty.const._MODULE_SECURITY_WHATCANIDO}</h3>
	<p>{$smarty.const._MODULE_SECURITY_MAGICQUOTESSOLUTIONEXPLANATION}</p>	
    {$T_SECURITY_FORM.javascript}
    <form {$T_SECURITY_FORM.attributes}>
        {$T_SECURITY_FORM.hidden}
    	{$T_SECURITY_FORM.submit_ignore.html}
    </form> 	
{elseif $smarty.get.type == 'default_accounts'}
	<h1>{$smarty.const._MODULE_SECURITY_DEFAULTACCOUNTSSTILLEXIST}</h1>
	<br/>
	<h3>{$smarty.const._MODULE_SECURITY_WHATSTHERISK}</h3>
	<p>{$smarty.const._MODULE_SECURITY_DEFAULTACCOUNTSEXPLANATION}</p>
	
	<h3>{$smarty.const._MODULE_SECURITY_WHATCANIDO}</h3>
    {$T_SECURITY_FORM.javascript}
    <form {$T_SECURITY_FORM.attributes}>
        {$T_SECURITY_FORM.hidden}
    	{$T_SECURITY_FORM.submit_ignore.html}
        {$T_SECURITY_FORM.submit_deactivate.html}
    </form> 	
{elseif $smarty.get.type == 'changed_files'}
	{assign var = "t_size" value = $T_CHANGED_FILES|@sizeof}
	<h1>{$smarty.const._MODULE_SECURITY_SOMEFILESHAVECHANGEDSINCELASTTIME|replace:'%x':$t_size}</h1>
	<br/>
	<h3>{$smarty.const._MODULE_SECURITY_WHATSTHERISK}</h3>
	<p>{$smarty.const._MODULE_SECURITY_CHANGEDFILESEXPLANATION}</p>
	<h3>{$smarty.const._MODULE_SECURITY_WHATCANIDO}</h3>
	<p>{$smarty.const._MODULE_SECURITY_CHANGEDFILESSOLUTIONEXPLANATIONPART1} <a href = "{$T_MODULE_BASEURL}&type={$smarty.get.type}&download_ignore_list=1" style = "font-weight:bold">{$smarty.const._MODULE_SECURITY_IGNORELIST}</a> {$smarty.const._MODULE_SECURITY_CHANGEDFILESSOLUTIONEXPLANATIONPART2} efront_{$smarty.const.G_VERSION_NUM}_build{$smarty.const.G_BUILD}{if $smarty.const.G_VERSIONTYPE != 'community'}_{$smarty.const.G_VERSIONTYPE}{/if}.zip</p>
	<dl>
	{foreach name = 'changed_files_list' item = "item" key = "key" from = $T_CHANGED_FILES}
		<dt>
			<span class = "handle">{$key}</span>&nbsp;&nbsp;&nbsp;
			<img src = "images/16x16/success.png" class = "ajaxHandle" alt = "{$smarty.const._MODULE_SECURITY_ADDTOIGNORELIST}" title = "{$smarty.const._MODULE_SECURITY_ADDTOIGNORELIST}" onclick = "ajaxRequest(this, '{$T_MODULE_BASEURL}&type={$smarty.get.type}&ignore={$key}', {ldelim}{rdelim}, function(el, response) {ldelim}el.up().hide(){rdelim})" />
			<a href = "{$T_MODULE_BASEURL}&type={$smarty.get.type}&download={$key|base64_encode}" target = "_new" class = "handle" alt = "{$smarty.const._DOWNLOADFILE}" title = "{$smarty.const._DOWNLOADFILE}"><img src = "images/16x16/import.png" class = "handle"></a>
		</dt>
	{/foreach}
	</dl>
    {$T_SECURITY_FORM.javascript}
    <form {$T_SECURITY_FORM.attributes}>
        {$T_SECURITY_FORM.hidden}
        {$T_SECURITY_FORM.submit_recheck.html}
        {$T_SECURITY_FORM.reset_ignore_list.html}
        {$T_SECURITY_FORM.ignore_changed_all.html}
    </form> 	
	
{elseif $smarty.get.type == 'new_files'}
	{assign var = "t_size" value = $T_NEW_FILES|@sizeof}
	<h1>{$smarty.const._MODULE_SECURITY_NEWFILESFOUND|replace:'%x':$t_size}</h1>
	<br/>
	<h3>{$smarty.const._MODULE_SECURITY_WHATSTHERISK}</h3>
	{$smarty.const._MODULE_SECURITY_NEWFILESEXPLANATIONPART1}
	<ul>
	<li>{$smarty.const._MODULE_SECURITY_NEWFILESEXPLANATIONPART2}</li>
	<li>{$smarty.const._MODULE_SECURITY_NEWFILESEXPLANATIONPART3}</li>
	<li>{$smarty.const._MODULE_SECURITY_NEWFILESEXPLANATIONPART4}</li>
	</ul>
	<h3>{$smarty.const._MODULE_SECURITY_WHATCANIDO}</h3>
	<p>{$smarty.const._MODULE_SECURITY_CHANGEDFILESSOLUTIONEXPLANATIONPART1} <a href = "{$T_MODULE_BASEURL}&type={$smarty.get.type}&download_ignore_list=1" style = "font-weight:bold">{$smarty.const._MODULE_SECURITY_IGNORELIST}</a>{$smarty.const._MODULE_SECURITY_NEWFILESSOLUTIONEXPLANATION}</p>
	
	
	<dl>
	{foreach name = 'changed_files_list' item = "item" key = "key" from = $T_NEW_FILES}
		<dt>
			<span class = "handle">{$key}</span>&nbsp;&nbsp;&nbsp;
			<img src = "images/16x16/success.png" class = "ajaxHandle" alt = "{$smarty.const._MODULE_SECURITY_ADDTOIGNORELIST}" title = "{$smarty.const._MODULE_SECURITY_ADDTOIGNORELIST}" onclick = "ajaxRequest(this, '{$T_MODULE_BASEURL}&type={$smarty.get.type}&ignore={$key}', {ldelim}{rdelim}, function(el, response) {ldelim}el.up().hide(){rdelim})" />
			<a href = "{$T_MODULE_BASEURL}&type={$smarty.get.type}&download={$key|base64_encode}" target = "_new" class = "handle" alt = "{$smarty.const._DOWNLOADFILE}" title = "{$smarty.const._DOWNLOADFILE}"><img src = "images/16x16/import.png" class = "handle"></a>
			<img src = "images/16x16/error_delete.png" class = "ajaxHandle" alt = "{$smarty.const._DELETE}" title = "{$smarty.const._DELETE}" onclick = "ajaxRequest(this, '{$T_MODULE_BASEURL}&type={$smarty.get.type}&delete={$key}', {ldelim}{rdelim}, function(el, response) {ldelim}el.up().hide(){rdelim})" />
		</dt>
	{/foreach}
	</dl>
	
    {$T_SECURITY_FORM.javascript}
    <form {$T_SECURITY_FORM.attributes}>
        {$T_SECURITY_FORM.hidden}
        {$T_SECURITY_FORM.submit_recheck.html}
        {$T_SECURITY_FORM.reset_ignore_list.html}
        {$T_SECURITY_FORM.ignore_new_all.html}
    </form> 	
{else}
        	<table style = "width:100%;">
        		<tr><td style = "vertical-align:top;">
                	<ul style = "padding-left:0px;margin-left:0px;list-style-type:none;">
						{foreach name = 'issues_list' item = "item" key = "key" from = $T_LOCAL_ISSUES}
							<li><a href = "{$T_MODULE_BASEURL}&type={$key}" style = "color:red">{$smarty.const._MODULE_SECURITY_LOCALISSUE}: {$item}</a></li>
						{/foreach}
                	</ul>
                	<ul style = "padding-left:0px;margin-left:0px;list-style-type:none;">
                		Security feeds
						{$T_SECURITY_FEEDS}
                	</ul>
        		</td></tr>
        	</table>
        	
    {$T_SECURITY_FORM.javascript}
    <form {$T_SECURITY_FORM.attributes}>
        {$T_SECURITY_FORM.hidden}
        {$T_SECURITY_FORM.submit_recheck.html}
        {$T_SECURITY_FORM.submit_delete_remote.html}
    </form> 	
        	
{/if}

{/capture}

{eF_template_printBlock title=$smarty.const._MODULE_SECURITY_MODULESECURITY data=$smarty.capture.t_code image= $T_MODULE_BASELINK|cat:'img/security_agent.png' absoluteImagePath = 1 options = $T_TABLE_OPTIONS}
