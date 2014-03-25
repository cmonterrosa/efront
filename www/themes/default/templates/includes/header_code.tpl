	<div id = "logo">
		<a href = "{if $smarty.session.s_login}{$smarty.server.PHP_SELF|basename}{else}index.php{/if}">
			<img class = "handle" src = "{$T_LOGO}" title = "{$T_CONFIGURATION.site_name}" alt = "{$T_CONFIGURATION.site_name}" />
		</a>
	</div>
	
	{if $smarty.session.s_login}
	<div id = "logout_link" >
		{if $T_THEME_SETTINGS->options.sidebar_interface}
			  {*if $T_SIMPLE_COMPLETE_MODE && ($smarty.session.s_type !='student' || $smarty.session.s_lesson_user_type =='professor')*}
				{if $T_SIMPLE_COMPLETE_MODE}
	            	<span class="headerText dropdown">
		            	<span class = "label" style = "cursor:pointer" title = "{if $T_SIMPLE_MODE}{$smarty.const._SWITCHTOCOMPLETEMODE}{else}{$smarty.const._SWITCHTOSIMPLEMODE}{/if}" onclick = "jQuery.fn.efront('switchmode')">{if $T_SIMPLE_MODE}{$smarty.const._SIMPLEMODE}{else}{$smarty.const._COMPLETEMODE}{/if}</span>
	            	</span>
	            {/if}
	            {if $smarty.session.s_type == 'administrator'}
	            <span class="headerText dropdown">
	                <a class="dropdown-toggle" data-toggle="dropdown" href="#">{$smarty.const._GOTO} <b class="caret"></b></a>
	                <ul class="dropdown-menu">
	                	<li><a href="userpage.php">{$smarty.const._HOME}</a></li>
	                	{if 'users'|eF_template_isOptionVisible}
	                	<li><a href="userpage.php?ctg=users">{$smarty.const._USERS}</a></li>
	                	{/if}
	                	{if 'lessons'|eF_template_isOptionVisible}
	                	<li><a href="userpage.php?ctg=courses">{$smarty.const._COURSES}</a></li>
	                	<li><a href="userpage.php?ctg=lessons">{$smarty.const._LESSONS}</a></li>
						{/if}	    
					{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
						{if 'branches'|eF_template_isOptionVisible}            	
	                	<li><a href="userpage.php?ctg=module_hcd&op=branches">{$smarty.const._BRANCHES}</a></li>
	                	{/if}
                	{/if} {* #cpp#endif *}
	                	<li class="divider"></li>
						<li class="nav-header">{$smarty.const._ADD}</li>
						{if 'users'|eF_template_isOptionVisible}	                	
	                	<li><a href="userpage.php?ctg=personal&user=admin&op=profile&add_user=1">{$smarty.const._USER}</a></li>
	                	{/if}
	                	{if 'lessons'|eF_template_isOptionVisible}
	                	<li><a href="userpage.php?ctg=courses&add_course=1">{$smarty.const._COURSE}</a></li>
	                	<li><a href="userpage.php?ctg=lessons&add_lesson=1">{$smarty.const._LESSON}</a></li>
						{/if}	    
					{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
						{if 'branches'|eF_template_isOptionVisible}            	
	                	<li><a href="userpage.php?ctg=module_hcd&op=branches&add_branch=1">{$smarty.const._BRANCH}</a></li>
	                	{/if}
                	{/if} {* #cpp#endif *}
	                </ul>
	            </span>
	            {/if}
	            <span class="headerText dropdown">
	                <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">#filter:login-{$smarty.session.s_login}# <b class="caret"></b></a>
	                <ul class="dropdown-menu">
					{if $T_MAPPED_ACCOUNTS && $smarty.get.ctg !='agreement'}
	                    <li class="nav-header">{$smarty.const._SWITCHACCOUNT}</li>
						{if !$T_CONFIGURATION.mapped_accounts || $T_CONFIGURATION.mapped_accounts == 1 && $smarty.session.s_type!='student' || $T_CONFIGURATION.mapped_accounts == 2 && $smarty.session.s_type=='administrator'}
							{foreach name = 'additional_accounts' item = "item" key = "key" from = $T_MAPPED_ACCOUNTS}
						<li><a href="javascript:void(0)" onclick = "changeAccount('{$item.login}')">#filter:login-{$item.login}#</a></li>
			                {/foreach}
			            {/if}
	                    <li class="divider"></li>
		            {/if}
	            	{if $T_CURRENT_USER->coreAccess.dashboard != 'hidden'}
	                  	<li><a href="userpage.php?ctg=personal&user={$T_CURRENT_USER->user.login}&op=dashboard">{$smarty.const._DASHBOARD}</a></li>
	              	{/if}
	                  	<li><a href="userpage.php?ctg=personal&user={$T_CURRENT_USER->user.login}&op=profile">{$smarty.const._ACCOUNT}</a></li>
	              	{if $smarty.session.s_type != 'administrator'}
	                  	<li><a href="userpage.php?ctg=personal&user={$T_CURRENT_USER->user.login}&op=user_courses">{$smarty.const._LEARNING}</a></li>
	              	{/if}
	              	{if $smarty.const.G_VERSIONTYPE == 'enterprise'} {* #cpp#ifdef ENTERPRISE *}
						{if $T_CURRENT_USER->coreAccess.organization != 'hidden'}
						<li><a href = "userpage.php?ctg=personal&user={$T_CURRENT_USER->user.login}&op=org_form">{$smarty.const._ORGANIZATION}</a></li>
						{/if}
						<li><a href = "userpage.php?ctg=personal&user={$T_CURRENT_USER->user.login}&op=files'">{$smarty.const._FILES}</a></li>
	              	{/if} {* #cpp#endif *}
	                </ul>
	            </span>				
			{if $T_CURRENT_USER->coreAccess.personal_messages != 'hidden' && 'messages'|eF_template_isOptionVisible}
	            <span class="headerText dropdown">
	                <a class="dropdown-toggle" data-toggle="dropdown" href="#">{$smarty.const._MESSAGES} <b class="caret"></b></a> {if $T_NUM_MESSAGES}<span id = "header_total_messages" class = "badge badge-info" style = "cursor:pointer" onclick = "window.location='{$smarty.server.PHP_SELF}?ctg=messages'">{$T_NUM_MESSAGES}</span>{/if}
	                <ul class="dropdown-menu">
	                	<li><a href="userpage.php?ctg=messages">{$smarty.const._INCOMING}</a></li>
	                	<li><a href="userpage.php?ctg=messages&add=1">{$smarty.const._CREATE}</a></li>
	                </ul>
	            </span>
			{/if}
			{if $smarty.server.PHP_SELF|basename != 'index.php' && $T_THEME_SETTINGS->options.sidebar_interface != 0 && $smarty.session.s_login}
				<span class = "headerText">
	            <form action = "{$smarty.server.PHP_SELF}?ctg={if $smarty.session.s_type == 'administrator'}control_panel{else}lessons{/if}&op=search" method = "post" style = "display:inline-block;">
					<input type = "text" name = "search_text" placeholder = "{$smarty.const._SEARCH}" class = "searchBox"/>
					<input type = "hidden" name = "current_location" id = "current_location" />
				</form>
				</span>
			{else}
				{$smarty.capture.header_language_code}
			{/if}			  				
			{if $T_FACEBOOK_USER}
             <div style="display:none" id="fb-root"></div>
				<!--<button id="fb-logout" onclick="facebook_logout()">Log out</button> -->
				 <span id="fbLogout" onclick="facebook_logout()"><a class="fb_button fb_button_medium"><span class="fb_button_text">{$smarty.const._LOGOUT}</span></a></span>
 				<!-- <span id="fbLogout" onclick="facebook_logout()"><a class = "headerText" href="javascript:void(0)">{$smarty.const._LOGOUT}</a></span> -->
 			{else}
 				<a class = "headerText" href = "index.php?logout=true">{$smarty.const._LOGOUT}</a>
			{/if}
		{/if}
		{if $T_THEME_SETTINGS->options.sidebar_interface != 0 && $T_HEADER_CLASS == 'header'}{$smarty.capture.t_path_additional_code}{/if}
	</div>
	{else}
	<div id = "logout_link" >
	{$smarty.capture.header_language_code}
	</div>
	{/if}
	{if $T_CONFIGURATION.motto_on_header}
		<div id = "info">
			<div id = "site_name" class= "headerText">{$T_CONFIGURATION.site_name}</div>
			<div id = "site_motto" class= "headerText">{$T_CONFIGURATION.site_motto}</div>
		</div>
	{/if}
	<div id = "path">
		<div id = "path_title">{$title|eF_formatTitlePath}</div>
		<div id = "tab_handles_div">
			{if $T_THEME_SETTINGS->options.sidebar_interface == 0 || $T_HEADER_CLASS == 'headerHidden'}{$smarty.capture.t_path_additional_code}{/if}
		</div>
	</div>
