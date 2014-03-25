<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

/*
 * Class defining the new module
 * The name must match the one provided in the module.xml file
 */
class module_security extends EfrontModule {

	const URL = 'http://www.efrontlearning.net/resources/checksums/';
	
	/**
	 * Get the module name, for example "Demo module"
	 *
	 * @see libraries/EfrontModule#getName()
	 */
    public function getName() {
    	//This is a language tag, defined in the file lang-<your language>.php
        return _MODULE_SECURITY_MODULESECURITY;
    }

	/**
	 * Return the array of roles that will have access to this module
	 * You can return any combination of 'administrator', 'student' or 'professor'
	 *
	 * @see libraries/EfrontModule#getPermittedRoles()
	 */
    public function getPermittedRoles() {
        return array("administrator");		//This module will be available to administrators
    }

    /**
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getCenterLinkInfo()
     */
    public function getCenterLinkInfo() {
    	return array('title' => $this -> getName(),
                     'image' => $this -> moduleBaseLink . 'img/security_agent.png',
                     'link'  => $this -> moduleBaseUrl);
    }
    
    /**
     * The main functionality
     *
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getModule()
     */
    public function getModule() {
    	$smarty = $this -> getSmartyVar();
        $smarty -> assign("T_MODULE_BASEDIR" , $this -> moduleBaseDir);
        $smarty -> assign("T_MODULE_BASELINK" , $this -> moduleBaseLink);
        $smarty -> assign("T_MODULE_BASEURL" , $this -> moduleBaseUrl);

        $smarty->assign("T_TABLE_OPTIONS", array(
        		array('image' => $this -> moduleBaseLink . 'img/order.png', 'text' => _MODULE_SECURITY_RECHECKLOCALFILES, 'href' => $this -> moduleBaseUrl.'&delete_local_list=1'),
        		array('image' => $this -> moduleBaseLink . 'img/refresh.png', 'text' => _MODULE_SECURITY_RECHECKSERVERFILES, 'href' => $this -> moduleBaseUrl.'&delete_remote_list=1'),
        		array('image' => $this -> moduleBaseLink . 'img/rules.png', 'text' => _MODULE_SECURITY_RESETIGNORELIST, 'href' => $this -> moduleBaseUrl.'&delete_ignore_list=1')
        ));        
        if (isset($_GET['download_ignore_list'])) {
        	try {
        		$file = new EfrontFile($this->getIgnoreListName());
        		$file->sendFile(true);
        	} catch (Exception $e) {
        		$this->setMessageVar('The list is empty', 'failure');
        	}
        } else if ($_GET['delete_remote_list']) {
        	$file = new EfrontFile($this->getCachedRemoteListName());
        	$file->delete();
        } else if ($_GET['delete_local_list']) {
        	$file = new EfrontFile($this->getLocalListName());
        	$file->delete();
        } else if ($_GET['delete_ignore_list']) {
        	try {
        		$file = new EfrontFile($this->getIgnoreListName());
        		$file->delete();
        	} catch (Exception $e) {}
        }
        
        if (isset($_GET['download'])) {
        	$_GET['download'] = base64_decode($_GET['download']);
        }
        
        $form = new HTML_QuickForm("demo_form", "post", $this -> moduleBaseUrl."&type=".$_GET['type'], "", null, true);

        switch($_GET['type']) {
        	case 'install':
        		$form -> addElement('submit', 'submit_delete_install', _MODULE_SECURITY_DELETEINSTALLDIRECTORY, 'class = "flatButton"');
        		break;
        	case 'magic_quotes_gpc':
        		break;
        	case 'default_accounts':
        		$form -> addElement('submit', 'submit_deactivate', _MODULE_SECURITY_DEACTIVATEDEFAULTACCOUNTS, 'class = "flatButton"  title = "Click this to deactivate these accounts"');
        		break;
        	case 'changed_files':
        		list($changed_files, $new_files) = $this->checksumCheck();
        		$smarty->assign("T_CHANGED_FILES", $changed_files);

        		
        		if (isset($_GET['download']) && in_array($_GET['download'], array_keys($changed_files)) && $_GET['download'] != 'libraries/configuration.php') {
        			try {
        				$file = new EfrontFile(G_ROOTPATH.$_GET['download']);
        				$file->sendFile(true);
        				exit;
        			} catch (Exception $e) {
        				$this->setMessageVar(_MODULE_SECURITY_FILECOULDNOTBEDELETED, 'failure');
        			}
        		} else if (isset($_GET['ignore']) && in_array($_GET['ignore'], array_keys($changed_files))) {
        			$this->addToIgnoreList($_GET['ignore']);
        			echo json_encode(array('success' => true));
        			exit;
        		}
        		
        		$form -> addElement('submit', 'submit_recheck', _MODULE_SECURITY_RECHECKFILES, 'class = "flatButton" ');
        		$form -> addElement('submit', 'reset_ignore_list', _MODULE_SECURITY_RESETIGNORELIST, 'class = "flatButton" ');
        		$form -> addElement('submit', 'ignore_changed_all', _MODULE_SECURITY_IGNOREALL, 'class = "flatButton" ');
        		break;
        	case 'new_files':
        		list($changed_files, $new_files) = $this->checksumCheck();

        		if (isset($_GET['download']) && in_array($_GET['download'], array_keys($new_files)) && $_GET['download'] != 'libraries/configuration.php') {
        			try {
	        			$file = new EfrontFile(G_ROOTPATH.$_GET['download']);
	        			$file->sendFile(true);
	        			exit;
        			} catch (Exception $e) {
        				$this->setMessageVar(_MODULE_SECURITY_FILECOULDNOTBEDELETED, 'failure');
        			}
        		} else if (isset($_GET['ignore']) && in_array($_GET['ignore'], array_keys($new_files))) {
        			$this->addToIgnoreList($_GET['ignore']);
        		} else if (isset($_GET['delete']) && in_array($_GET['delete'], array_keys($new_files))) {
        			try {
	        			$file = new EfrontFile(G_ROOTPATH.$_GET['delete']);
	        			$file->delete();
        			} catch (Exception $e) {}
        			$file = new EfrontFile($this->getLocalListName());
        			$file->delete();
        			list($changed_files, $new_files) = $this->checksumCheck();
        		}

        		$smarty->assign("T_NEW_FILES", $new_files);
        		
        		$form -> addElement('submit', 'submit_recheck', _MODULE_SECURITY_RECHECKFILES, 'class = "flatButton" ');
        		$form -> addElement('submit', 'reset_ignore_list', _MODULE_SECURITY_RESETIGNORELIST, 'class = "flatButton" ');
        		$form -> addElement('submit', 'ignore_new_all', _MODULE_SECURITY_IGNOREALL, 'class = "flatButton" ');
        		break;
        	default:
        		$smarty->assign("T_SECURITY_FEEDS", $this->getRssFeeds());
        		try {
        			$smarty -> assign("T_LOCAL_ISSUES", $this->checkLocalIssues());
        		} catch (Exception $e) {
        			$this->setMessageVar($e->getMessage(), 'failure');
        		}
        		break;
        }

        
        if ($form -> isSubmitted() && $form -> validate()) {
        	try {
        		$values = $form -> exportValues();
        		if  ($values['submit_recheck']) {
        			$file = new EfrontFile($this->getLocalListName());
        			$file->delete();        			 
        			eF_redirect($this -> moduleBaseUrl.'&type='.$_GET['type'].'&message='.urlencode(_OPERATIONCOMPLETEDSUCCESSFULLY).'&message_type=success');
        		} else if ($values['submit_delete_install']) {
        			file_get_contents(G_SERVERNAME.'index.php?delete_install=1');
        			eF_redirect($this -> moduleBaseUrl.'&type='.$_GET['type'].'&message='.urlencode(_OPERATIONCOMPLETEDSUCCESSFULLY).'&message_type=success');
        		} else if ($values['submit_deactivate']) {
        			$result = eF_updateTableData("users", array('active' => 0), "(login = 'student' and password = '04aed36b7da8d1b5d8c892cf91486cdb') or (login = 'professor' and password = 'da18be534843cf9f9edd60c89de6a8e7')");
        			eF_redirect($this -> moduleBaseUrl.'&message='.urlencode(_OPERATIONCOMPLETEDSUCCESSFULLY).'&message_type=success');
        		} else if ($values['reset_ignore_list']) {
        			try {
        				$file = new EfrontFile($this->getIgnoreListName());
        				$file->delete();
        				eF_redirect($this -> moduleBaseUrl.'&type='.$_GET['type'].'&message='.urlencode(_OPERATIONCOMPLETEDSUCCESSFULLY).'&message_type=success');
        			} catch (Exception $e) {
        				$this->setMessageVar(_MODULE_SECURITY_THELISTISEMTPY, 'failure');
        			}
        		} else if($values['ignore_new_all']) {
        			list($changed_files, $new_files) = $this->checksumCheck();
        			foreach ($new_files as $key => $value) {
        				$this->addToIgnoreList($key);
        			}
        			eF_redirect($this -> moduleBaseUrl.'&type=new_files&message='.urlencode(_OPERATIONCOMPLETEDSUCCESSFULLY).'&message_type=success');
        		} else if($values['ignore_changed_all']) {
        			list($changed_files, $new_files) = $this->checksumCheck();
        			foreach ($changed_files as $key => $value) {
        				$this->addToIgnoreList($key);
        			}
        			eF_redirect($this -> moduleBaseUrl.'&type=changed_files&message='.urlencode(_OPERATIONCOMPLETEDSUCCESSFULLY).'&message_type=success');
        		} 
        	} catch (Exception $e) {
        		$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
        		$message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
        		$this -> setMessageVar($message, 'failure');
        	}        	 
        }
		$renderer = prepareFormRenderer($form);
        $smarty -> assign('T_SECURITY_FORM', $renderer -> toArray());
        
        //$feeds = $this->getRssFeeds(true, false);
		//$smarty->assign("T_SECURITY_FEEDS", $feeds);
		
        return true;
    }
    
    private function addToIgnoreList($file) {
    	$list = explode("\n", file_get_contents($this->getIgnoreListName()));
    	if (!is_array($list) || !in_array($file, $list)) {
    		$list[] = $file;
    		file_put_contents($this->getIgnoreListName(), implode("\n", $list));
    	}
    }
    
    private function checksumCheck() {
    	$local_list = $this->retrieveLocalChecksumList();
    	$remote_list = $this->retrieveRemoteChecksumList();
    	$ignore_list = $this->retrieveIgnoreList();
    	//$local_list = $this->calculateChecksumList();
    	foreach ($remote_list as $file => $checksum) {
    		if ($local_list[$file] && $local_list[$file] != $checksum && !in_array($file, $ignore_list)) {    			
    			$changed_files[$file] = $checksum;
    		}
    		unset($local_list[$file]);
    	}

    	unset($changed_files['libraries/configuration.php']);
    	unset($changed_files['libraries/phplivedocx_config.php']);
    	unset($local_list['libraries/configuration.php']);
    	unset($local_list['libraries/phplivedocx_config.php']);
    	$new_files = $local_list;
    	foreach ($new_files as $key=>$value) {
    		if (in_array($key, $ignore_list)) {
    			unset($new_files[$key]);
    		}
    	}
    	    	 
    	return array($changed_files, $new_files);
    }
    
    private function retrieveIgnoreList() {
    	if (is_file($this->getIgnoreListName())) {
    		$list = explode("\n", file_get_contents($this->getIgnoreListName()));
    		if (is_array($list)) {
    			return $list;
    		} else {
    			return array();
    		}
    	} else {    		
    		return array();
    	}
    	 
    }
    
    private function calculateChecksumList() {
    	$files = array();
    	
    	$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(G_ROOTPATH), RecursiveIteratorIterator::SELF_FIRST);
    	foreach($objects as $name => $object){
    		if (pathinfo($name, PATHINFO_EXTENSION) == 'php' && strpos(pathinfo($name, PATHINFO_DIRNAME), 'libraries/smarty/themes_cache/') === false) {
    			$name = str_replace("\\", "/", $name);	
    			$files[str_replace(G_ROOTPATH, '', $name)] = md5_file($name);
    		}
    	}
    	 
    	return $files;
    }
    
    private function storeChecksumList($list) {
    	$data = array();
    	foreach ($list as $file => $checksum) {
    		$data[] = "$file :: $checksum\n";
    	}
    	file_put_contents($this->getLocalListName(), implode("", $data));
    }
    
    private function getCachedRemoteListName() {
    	return dirname(__FILE__).'/checksum_remote_list.txt';
    }
    
    private function getLocalListName() {
    	return dirname(__FILE__).'/checksum_list.txt';
    }
    
    private function getIgnoreListName() {
    	return dirname(__FILE__).'/ignore_list.txt';
    }
    
    private function retrieveLocalChecksumList() {
    	if (is_file($this->getLocalListName())) {
    		foreach (explode("\n", file_get_contents($this->getLocalListName())) as $value) {
    			list($file, $checksum) = explode(' :: ', $value);
    			if ($file) {
    				$list[$file] = $checksum;
    			}
    		}
    		return $list;
    	} else {
    		$list = $this->calculateChecksumList();
    		$this->storeChecksumList($list);
    		return $list;
    	}
    }
    
    private function retrieveRemoteChecksumList() {
    	if (is_file($this->getCachedRemoteListName())) {
    		$filename = $this->getCachedRemoteListName();
    	} else {
    		$filename = self::URL.'checksum_list_'.G_BUILD.'_'.G_VERSIONTYPE.'.txt';
    	}
    	 
    	if (($contents = file_get_contents($filename)) === false) {
    		//throw new Exception(_MODULE_SECURITY_COULDNOTRETRIEVECHECKSUM." (".G_VERSION_NUM." build ".G_BUILD.")");	//commented out, because otherwise non-existing files forced connection every time 
    	}
    	foreach (explode("\n", $contents) as $value) {
    		list($file, $checksum) = explode(' :: ', $value);
    		if ($file) {
    			$list[$file] = $checksum;
    		}
    	}
    	
    	if (!is_file($this->getCachedRemoteListName())) {
    		file_put_contents($this->getCachedRemoteListName(), $contents);
    	}
    	return $list;
    }

    private function checkLocalIssues() {
    	$localIssues = array();
    	
    	if (is_dir("install/")) {
    		$localIssues['install'] = _MODULE_SECURITY_INSTALLATIONFOLDERSTILLEXISTS;
    	}
    	if (ini_get("magic_quotes_gpc") == 1 || strtolower(ini_get("magic_quotes_gpc")) == "on") {
    		$localIssues['magic_quotes_gpc'] = _MODULE_SECURITY_MAGICQUOTESGPCISON;
    	}
    	$result = eF_getTableData("users", "login", "archive = 0 and active = 1 and ((login = 'student' and password = '04aed36b7da8d1b5d8c892cf91486cdb') or (login = 'professor' and password = 'da18be534843cf9f9edd60c89de6a8e7'))");
    	if (!empty($result)) {
    		$localIssues['default_accounts'] = _MODULE_SECURITY_DEFAULTACCOUNTSSTILLEXIST;
    	}
    	
    	list($changed_files, $new_files) = $this->checksumCheck();

    	if (!empty($changed_files)) {
    		$localIssues['changed_files'] = str_replace('%x', sizeof($changed_files), _MODULE_SECURITY_SOMEFILESHAVECHANGEDSINCELASTTIME);
    	}
    	if (!empty($new_files)) {
    		$localIssues['new_files'] = str_replace('%x', sizeof($new_files), _MODULE_SECURITY_NEWFILESFOUND);
    	}
    	 
    	return $localIssues;
    	    	
    }
    
    private function getRssFeeds($refresh = false, $limit = 10) {
    	//session_write_close();
    	$feedTitle = '';
    	$feed = 'http://security.efrontlearning.net/feeds/posts/default';
    	 
    	$str = '';
    	if (!$refresh && $str = EfrontCache::getInstance()->getCache('security_cache:'.$key)) {
    		$rssString= $str;
    	} else  {
    		$response = $this -> parseFeed($feed);
    		!$limit OR $response = array_slice($response, 0, $limit);    		
    		foreach ($response as $value) {
    			$str .= '<li> '.formatTimestamp($value['timestamp']).' <a href = "'.$value['link'].'" target = "_NEW">'.$value['title'].'</a>'.$description.'</li>';    			
    		}
    		
    		$rssString = $str;

    		EfrontCache::getInstance()->setCache('security_cache:'.$key, $str, 3600);	//cache for one hour
    	}
    	 
    	return $rssString;
    }    
    
    public function parseFeed($feed) {
    	$context = stream_context_create(array('http' => array('timeout' => 3)));
    	$xmlString = file_get_contents($feed, 0, $context);
        try {
            $iterator  = new SimpleXMLIterator($xmlString);
            foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator :: SELF_FIRST) as $key => $value) {
                if ($key == 'item') {
                    $data = array('title' => (string)$value -> title, 'link' => (string)$value -> link, 'description' => (string)$value -> description);
                    if ($value -> pubDate) {
                    	$data['timestamp'] = strtotime((string)$value -> pubDate);
                    } else if ($value -> date) {
                    	$data['timestamp'] = (string)$value -> date;
                    }
                    $rss[] = $data;
                } else if ($key == 'entry') {
                	//pr(strtotime((string)$value -> updated));
                    $data = array('title' => (string)$value -> title, 'description' => (string)$value -> content, 'timestamp' => strtotime((string)$value -> updated));
                	foreach ($value->link as $link) {
                		if ($link['rel'] == 'alternate') {
                			$data['link'] = (string)$link['href'];
                		}
                	}
                    $rss[] = $data;                	
                }
            }
        } catch (Exception $e) {
            $rss[] = array('title' => '<span class = "emptyCategory">'._CONNECTIONERROR.'</span>', 'link' => 'javascript:void(0)');
        }
        
        return $rss;
    }    
    
    /**
     * Specify which file to include for template
     *
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getSmartyTpl()
     */
    public function getSmartyTpl() {
    	return $this -> moduleBaseDir."module.tpl";
    }

    public function getControlPanelModule() {
    	$smarty = $this -> getSmartyVar();    	
        $smarty -> assign("T_MODULE_BASEDIR" , $this -> moduleBaseDir);
        $smarty -> assign("T_MODULE_BASELINK" , $this -> moduleBaseLink);
        $smarty -> assign("T_MODULE_BASEURL" , $this -> moduleBaseUrl);

        $smarty->assign("T_MODULE_OPTIONS", array(array('text' => _MODULE_SECURITY_PAGE, 'image' => "{$this -> moduleBaseLink}img/go_into.png", 'href' => $this->moduleBaseUrl)));
        
		$feeds = $this->getRssFeeds();
		$smarty->assign("T_SECURITY_FEEDS", $feeds);

		try {
			if ($GLOBALS['configuration']['module_security_last_check'] < time() - 2*86400) {	//check every 2 days
				EfrontConfiguration::setValue('module_security_last_check', time());
				$file = new EfrontFile($this->getLocalListName());
				$file->delete();				
			}
			$localIssues = $this->checkLocalIssues();
			$smarty -> assign("T_LOCAL_ISSUES", $localIssues);
		} catch (Exception $e) {}	//Do nothing in the control panel in case of an exception
		
		return true;
    }
        
    /**
     * Specify which file to include for template
     *
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getSmartyTpl()
     */
    public function getControlPanelSmartyTpl() {
    	return $this -> moduleBaseDir."module_security_cpanel.tpl";
    }
    
    /**
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getNavigationLinks()
     */
    public function getNavigationLinks() {
        $path = array (array ('title' => _HOME, 'link'  => $_SERVER['PHP_SELF']),
                      array ('title' => $this -> getName(), 'link'  => $this -> moduleBaseUrl));
        if (isset($_GET['type'])) {
        	$path[] = array('title' => _MODULE_SECURITY_MOREINFO, 'link'  => $this -> moduleBaseUrl.'&type='.$_GET['type']);
        }
        return $path;
    }


}
