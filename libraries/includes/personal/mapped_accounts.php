<?php

unserialize($editedUser -> user['additional_accounts']) ? $additionalAccounts = unserialize($editedUser -> user['additional_accounts']) : $additionalAccounts = array();
$smarty -> assign("T_ADDITIONAL_ACCOUNTS", $additionalAccounts);

if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if ($GLOBALS['configuration']['social_modules_activated'] & FB_FUNC_CONNECT) {
		$smarty -> assign("T_FB_ACCOUNT", EfrontFacebook::getEfToFbUser($currentUser->user['login']));
	}
} #cpp#endif

if (isset($_GET['ajax']) && $_GET['ajax'] == 'additional_accounts') {
	try {
		if (isset($_GET['fb_login'])) {
			if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
				EfrontFacebook::deleteEfUser($_GET['fb_login']);
			} #cpp#endif
		} else {
			if (isset($_GET['delete'])) {
				unset($additionalAccounts[array_search($_GET['login'], $additionalAccounts)]);
			} else {
				if	($_GET['login'] == $_SESSION['s_login']){
					throw new Exception(_CANNOTMAPSAMEACCOUNT);
				}

				if (in_array($_GET['login'], $additionalAccounts)) {
					throw new Exception(_ADDITIONALACCOUNTALREADYEXISTS);
				}
				//handle ldap users
				try {
					$newAccount = EfrontUserFactory::factory($_GET['login'], EfrontUser::createPassword($_GET['pwd']));
				} catch (Exception $e){
					if ($e -> getCode() ==EfrontUserException :: INVALID_PASSWORD){
						$newAccount = EfrontUserFactory::factory($_GET['login']);
						if ($newAccount -> user['password'] != 'ldap' || $_GET['pwd'] != 'ldap') {
							handleAjaxExceptions($e);
						}
					}
				}
				$additionalAccounts[] = $newAccount -> user['login'];

				unserialize($newAccount -> user['additional_accounts']) ? $additionalAccounts2 = unserialize($newAccount -> user['additional_accounts']) : $additionalAccounts2 = array();
				$additionalAccounts2[] = $editedUser -> user['login'];
				$newAccount -> user['additional_accounts'] = serialize(array_unique($additionalAccounts2));
				$newAccount -> persist();
			}
			$editedUser -> user['additional_accounts'] = serialize(array_unique($additionalAccounts));
			$editedUser -> persist();
		}
	} catch (Exception $e) {
		handleAjaxExceptions($e);
	}
	exit;
}
