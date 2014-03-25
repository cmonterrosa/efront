    {$T_LOGIN_FORM.javascript}
    <form {$T_LOGIN_FORM.attributes}>
    	{$T_LOGIN_FORM.hidden}
		<div class = "formRow">
    		<div class = "formLabel">
                <div class = "header">{$T_LOGIN_FORM.login.label}</div>
                {*<div class = "explanation centerOnly"><a href = "{$smarty.server.PHP_SELF}?ctg=signup">{$smarty.const._DONTHAVEACCOUNT}</a></div>*}
        	</div>
    		<div class = "formElement">
            	<div class = "field">{$T_LOGIN_FORM.login.html}</div>
        		{if $T_LOGIN_FORM.login.error}<div class = "error">{$T_LOGIN_FORM.login.error}</div>{/if}
    	    </div>
    	</div>
		<div class = "formRow">
    		<div class = "formLabel">
                <div class = "header">{$T_LOGIN_FORM.password.label}</div>
                {*<div class = "explanation centerOnly"><a href = "{$smarty.server.PHP_SELF}?ctg=reset_pwd">{$smarty.const._FORGOTPASSWORD}</a></div>*}
        	</div>
    		<div class = "formElement">
            	<div class = "field">{$T_LOGIN_FORM.password.html}</div>
        		{if $T_LOGIN_FORM.password.error}<div class = "error">{$T_LOGIN_FORM.password.error}</div>{/if}
    	    </div>
    	</div>
{if $T_CONFIGURATION.remember_login}
		<div class = "formRow">
    		<div class = "formElement">
            	<div class = "field">{$T_LOGIN_FORM.remember.html} {$T_LOGIN_FORM.remember.label}</div>
        		{if $T_LOGIN_FORM.remember.error}<div class = "error">{$T_LOGIN_FORM.remember.error}</div>{/if}
    	    </div>
    	</div>
{/if}
		<div class = "formRow">
        	<div class = "formLabel">
                <div class = "header">&nbsp;</div>
                <div class = "explanation"></div>
        	</div>
    		<div class = "formElement">
            	<div class = "field">{$T_LOGIN_FORM.submit_login.html}</div>
            	{if $T_CONFIGURATION.signup && !$T_CONFIGURATION.only_ldap}<div class = "small note"><a href = "{$smarty.server.PHP_SELF}?ctg=signup">{$smarty.const._DONTHAVEACCOUNT}</a></div>{/if}
            	{if $T_CONFIGURATION.password_reminder && !$T_CONFIGURATION.only_ldap}<div class = "small note"><a href = "{$smarty.server.PHP_SELF}?ctg=reset_pwd">{$smarty.const._FORGOTPASSWORD}</a></div>{/if}
            	<div class = "small note"><a href = "{$smarty.server.PHP_SELF}?ctg=contact">{$smarty.const._CONTACTUS}</a></div>
            	{if $T_CONFIGURATION.lessons_directory == 1}<div class = "small note"><a href = "{$smarty.server.PHP_SELF}?ctg=lessons">{$smarty.const._LESSONSLIST}</a></div>{/if}
    	    </div>
    	</div>
     </form>
{if $T_OPEN_FACEBOOK_SESSION && !$T_NO_FACEBOOK_LOGIN}        
  	<div id="fb-root"></div>
	        	 <button class="fb_login flatButton">Login with Facebook</button>       	 
{/if}       	  

