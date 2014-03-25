{if $smarty.const.G_VERSIONTYPE != 'community'} {* #cpp#ifndef COMMUNITY *}

	{capture name="moduleVersionKey"}
	        <tr><td class = "moduleCell">


		{if $smarty.get.download}
			<table style = "margin:auto;">
				<tr><td>{$smarty.const._DOWNLOADINGFILE} {$T_FILENAME}:</td>
					<td class = "progressCell">
	                    <span class = "progressNumber" id = "file_progress">0%</span>
	                    <span class = "progressBar" id = "file_progress_bar" style = "width:1px;">&nbsp;</span>
				</td></tr>
			</table>
		
		{else}
		
	        	{if $T_NEW_VERSION || $T_NEW_BUILD}
	{*
	        	<div class = "newVersion">
	        		<p>{$smarty.const._THEREISNEWVERSION} 
	        		<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, '{$smarty.const._DOWNLOADANDINSTALL}', 0, 'download_version');showVersionFileDetails();">{$smarty.const._DOWNLOADANDINSTALL}</a> - 
	        		<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, '{$smarty.const._RELEASENOTES}', 2, 'release_notes')">{$smarty.const._RELEASENOTES}</a></p>
	        	</div>
	*}        	
	        	<div id = "release_notes" style = "display:none" class = "popup">
					{eF_template_printBlock title = $smarty.const._RELEASENOTES data = "<pre>`$T_ONLINE_VERSION.notes`</pre>" image = '32x32/generic.png'}
				</div>
					
	        	<div id = "download_version" style = "display:none">
	        	{capture name = "t_download_version_code"}
	        		<div id = "version_contact_server">{$smarty.const._CONTACTINGSERVER}</div>
	        		<table class = "formElements" id = "version_file_details" style = "display:none;">
	        			<tr><td class = "labelCell">{$smarty.const._FILENAME}:&nbsp;</td>
	        				<td class = "elementCell" id = "version_filename"></td></tr>
	        			<tr><td class = "labelCell" >{$smarty.const._FILESIZE}:&nbsp;</td>
	        				<td class = "elementCell"  id = "version_filesize"></td></tr>
	        			<tr><td></td>
	        				<td class = "submitCell">
	        					<button id = "version_download" type = "button" name = "version_download" class = "flatButton" onclick = "downloadVersionFile(this)">{$smarty.const._DOWNLOADANDINSTALL}</button>
	        				</td></tr>
	        			<tr id = "progress_cell" style = "display:none"><td colspan = "2" style = "text-align:center;"><p id = "progress_message">Downloading...</p><img src = "images/others/progress_big.gif" title = "{$smarty.const._DOWNLOADING}" alt = "{$smarty.const._DOWNLOADING}"/></td></tr>
	        			<tr id = "finished_cell" style = "display:none"><td colspan = "2" style = "text-align:center;"><p><span style = "vertical-align:middle">Upgrade complete! Click </span><a href = "administrator.php?ctg=versionkey">here</a><span style = "vertical-align:middle"> to continue</span></p></td></tr>
					</table>				
				{/capture}
				{eF_template_printBlock title = $smarty.const._DOWNLOADANDINSTALL data = $smarty.capture.t_download_version_code image = '32x32/generic.png'}
				</div>
	        	
				{/if}     		 
	        {capture name = "version_check"}
	        	
	        	<table>
	{*        		<tr><td class = "labelCell">{$smarty.const._LICENSESERVER}:&nbsp;</td>
	        			<td class = "elementCell">{$smarty.const.LICENSE_SERVER}</td>*}
					<tr><td class = "labelCell">{$smarty.const._LICENSESERVER}:&nbsp;</td>
						<td class = "elementCell">{if $T_ERROR_CONNECT_SERVER}{$T_ERROR_CONNECT_SERVER}{else}{$smarty.const._OK}{/if}</td></tr>
	        		<tr><td class = "labelCell">{$smarty.const._EDITION}:&nbsp;</td>
	        			<td class = "elementCell">{$T_VERSION_TYPE} {$smarty.const._EDITION}</td>
	        		<tr><td class = "labelCell">{$smarty.const._INSTALLEDVERSION}:&nbsp;</td>
	        			<td class = "elementCell">{$smarty.const.G_VERSION_NUM} {$smarty.const._BUILD} {$smarty.const.G_BUILD}</td></tr>
	        		{if $T_ONLINE_VERSION}
	        		<tr><td class = "labelCell">{$smarty.const._LATESTVERSION}:&nbsp;</td>
	        			<td class = "elementCell">
							{if $T_NEW_VERSION || $T_NEW_BUILD}
								<span style = "color:green">{$T_ONLINE_VERSION.version} {$smarty.const._BUILD} {$T_ONLINE_VERSION.build}</span>
								{if !$T_EXPIRED_UPGRADES}
								<a style = "color:green" href = "javascript:void(0)" title = "{$smarty.const._DOWNLOADANDINSTALL}" onclick = "eF_js_showDivPopup(event, '{$smarty.const._DOWNLOADANDINSTALL}', 1, 'download_version');showVersionFileDetails();">{$smarty.const._DOWNLOADANDINSTALL}</a>
								&nbsp;|&nbsp;							
								{/if}
								<a style = "color:green" href = "javascript:void(0)" title = "{$smarty.const._RELEASENOTES}" onclick = "eF_js_showDivPopup(event, '{$smarty.const._RELEASENOTES}', 2, 'release_notes')">{$smarty.const._RELEASENOTES}</a>
	{*							
								<img src = "images/16x16/import.png" alt = "{$smarty.const._DOWNLOADANDINSTALL}" title = "{$smarty.const._DOWNLOADANDINSTALL}" class = "ajaxHandle" onclick = "eF_js_showDivPopup(event, '{$smarty.const._DOWNLOADANDINSTALL}', 1, 'download_version');showVersionFileDetails();"/>
								<img src = "images/16x16/help.png" alt = "{$smarty.const._RELEASENOTES}" title = "{$smarty.const._RELEASENOTES}" class = "ajaxHandle" onclick = "eF_js_showDivPopup(event, '{$smarty.const._RELEASENOTES}', 2, 'release_notes')"/>
	
	*}						{else}
								{$T_ONLINE_VERSION.version} {$smarty.const._BUILD} {$T_ONLINE_VERSION.build}
							{/if}
	        			</td></tr>
	        		<tr><td class = "labelCell">{$smarty.const._ACTIVATEDON}:&nbsp;</td>
	        			<td class = "elementCell">#filter:timestamp-{$T_CONFIGURATION.version_activated}#</td>
	        		<tr><td class = "labelCell">{$smarty.const._UPGRADESVALIDUNTIL}:&nbsp;</td>
	        			<td class = "elementCell">#filter:timestamp-{$T_CONFIGURATION.version_upgrades}#</td>
	        		{/if}
	        	</table>
	        	
	        {/capture}
	        {eF_template_printBlock title = $smarty.const._VERSIONINFORMATION data = $smarty.capture.version_check image = '32x32/generic.png' help = 'Registration-update'}
	        
	        {capture name="changeKey"}
	
	        	{$T_VERSIONKEY_DEFAULT.javascript}
	            <form {$T_VERSIONKEY_DEFAULT.attributes}>
	                {$T_VERSIONKEY_DEFAULT.hidden}
	                <table class = "formElements">
				{if $T_VERSIONKEY_DEFAULT_MSG.users}
        		    {if $smarty.const.G_VERSIONTYPE != 'standard'} {* #cpp#ifndef STANDARD *}
		                <tr><td class = "labelCell">{$smarty.const._VERSIONALLOEDUSERS} ({$smarty.const._CURRENTUSERS}):&nbsp;</td>
		                    <td class = "elementCell">{$T_VERSIONKEY_DEFAULT_MSG.users} / {$T_VERSIONKEY_DEFAULT_MSG.current_users}</td>
		            {/if} {* #cpp#endif *}
		                <tr><td class = "labelCell">{$smarty.const._VERSIONSERIAL}:&nbsp;</td>
		                    <td class = "elementCell">{$T_VERSIONKEY_DEFAULT_MSG.serial}</td>
	            {/if}
		                <tr><td class = "labelCell">{$smarty.const._VERSIONKEY}:&nbsp;</td>
		                    <td class = "elementCell">{$T_VERSIONKEY_DEFAULT.version_key.html}</td>
		                </tr>
		                <tr><td></td>
		                    <td class = "submitCell">{$T_VERSIONKEY_DEFAULT.submit_config.html}</td>
		                </tr>
	                </table>
	            </form>
	        {/capture}
	        {eF_template_printBlock title = $smarty.const._VERSIONKEYTITLE data = $smarty.capture.changeKey image = '32x32/keys.png' help = 'Registration-update'}
		{/if}
		    </td></tr>
	{/capture}


{/if}  {* #cpp#endif *}