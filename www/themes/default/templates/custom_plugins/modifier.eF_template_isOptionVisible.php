<?php
/*
 {eF_template_decode_ip option='projects'}
*/
function smarty_modifier_eF_template_isOptionVisible($option)
{
	if ($GLOBALS['currentUser'] instanceof EfrontUser) {
		return $GLOBALS['currentUser']->isOptionVisible($option);
	} else {
		return false;
	}
}
?>