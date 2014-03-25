<?php
    session_cache_limiter('none');          //Initialize session
    session_start();
    $path = "../libraries/";       
    require_once $path."configuration.php";
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    $css = $GLOBALS['configuration']['css'];
    if (strlen($css) > 0 && is_file(G_CUSTOMCSSPATH.$css)){
        $smarty->assign("T_CUSTOM_CSS", $css);   
    }
    $loadScripts = array_merge($loadScripts, array('scriptaculous/prototype','scriptaculous/scriptaculous','scriptaculous/effects','scriptaculous/controls'));
    
    $actions = array();
    $actions[] = "token";
    $actions[] = "login";
    $actions[] = "efrontlogin";
    $actions[] = "efrontlogin_ajax";
	$actions[] = "create_lesson";
    $actions[] = "create_user";
    $actions[] = "user_info";
    $actions[] = "user_lessons";
    $actions[] = "user_courses";            
    $actions[] = "user_groups";
    $actions[] = "update_user";
    $actions[] = "activate_user";
    $actions[] = "deactivate_user";
    $actions[] = "remove_user";    
    $actions[] = "users";
    $actions[] = "groups";
    $actions[] = "group_info"; 
    $actions[] = "group_users";
    $actions[] = "group_to_user";
    $actions[] = "group_from_user";
    $actions[] = "catalog";
    $actions[] = "lessons";
    $actions[] = "lesson_info";    
    $actions[] = "lesson_to_user";
    $actions[] = "lesson_from_user";
    $actions[] = "courses";
    $actions[] = "course_info";    
    $actions[] = "course_to_user";
    $actions[] = "course_from_user";
	$actions[] = "course_lessons";
	$actions[] = "curriculum_to_user";
	$actions[] = "efrontlogout";
    $actions[] = "logout";
    
    /* actions by vprountzos */
    $actions[] = "categories";
    $actions[] = "category";
    $actions[] = "buy_lesson";
    $actions[] = "buy_course";
    $actions[] = "get_user_autologin_key";
    $actions[] = "set_user_autologin_key";
    $actions[] = "languages";
    $actions[] = "user_to_branch";
    $actions[] = "branch_jobs";
    
    $smarty -> assign("T_ACTIONS", $actions);
    
    if (isset($_GET['action'])){
        $action = $actions[$_GET['action']];
        $action_id = $_GET['action'];
    }
    else if (isset($_POST['action'])){
        $action = $actions[$_POST['action']];
        $action_id = $_POST['action'];
    }
    else{
        $action = "token";
        $action_id = 0;
    }
    $smarty -> assign("T_ACTION", $action);
    
    $postTarget = basename($_SERVER['PHP_SELF']);
    $form = new HTML_QuickForm("action_form", "post", $postTarget, "", null, true);
    $form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');                   	   //Register this rule for checking user input with our function, eF_checkParameter    
    $form -> addElement('select', 'action', _ACTION, $actions, 'class = "inputSelect" id = "action" onchange = "window.location = \''.basename($_SERVER['PHP_SELF']).'?action=\'+this.options[this.selectedIndex].value"');     //Depending on user selection, changing the question type reloads the page with the corresponding form fields
    $form -> addRule('action', _THEFIELD.' '._QUESTIONTYPE.' '._ISMANDATORY, 'required', null, 'client');
    $form -> addRule('action', _INVALIDFIELDDATA, 'callback', 'text');        
    $output = "";
    switch ($action){
        case 'token':{
            break;
        }
        case 'login':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'login', _LOGIN, 'class = "inputText"');    
            $form -> addElement('password', 'password', _PASSWORD, 'class = "inputText"');    
            break;   
        }
        case 'efrontlogin':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
			$form -> addElement('text', 'login', _LOGIN, 'class = "inputText"'); 
            break;
        }
        case 'efrontlogin_ajax':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
			$form -> addElement('text', 'login', _LOGIN, 'class = "inputText"'); 
            break;
        }
        case 'efrontlogout':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
			$form -> addElement('text', 'login', _LOGIN, 'class = "inputText"'); 
            break;
        }
		case 'create_lesson':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'name', _LESSONNAME, 'class = "inputText"');    
            $form -> addElement('text', 'category', _CATEGORY, 'class = "inputText"');    
            $form -> addElement('select', 'course_only', _COURSEONLY, array(0 => _NO, 1 => _YES));    
            $form -> addElement('text', 'language', _LANGUAGE, 'class = "inputText"'); 
			$form -> addElement('text', 'price', _PRICE, 'class = "inputText"'); 
            break;
        } case 'create_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'login', _LOGIN, 'class = "inputText"');    
            $form -> addElement('password', 'password', _PASSWORD, 'class = "inputText"');    
            $form -> addElement('text', 'name', _FIRSTNAME, 'class = "inputText"');   
            $form -> addElement('text', 'surname', _LASTNAME, 'class = "inputText"'); 
            $form -> addElement('text', 'email', _EMAIL, 'class = "inputText"');   
            $form -> addElement('text', 'language', _LANGUAGE, 'class = "inputText"');

            $userProfile = eF_getTableData("user_profile", "*", "active=1");
            $additional_fields= array();
            foreach ($userProfile as $key => $field) {
            	switch ($field['type']) {
            		case 'select':
            			$form -> addElement("select", $field['name'], $field['description'], unserialize($field['options']));
            			break;
            		case 'checkbox':
            			$form -> addElement("advcheckbox", $field['name'], $field['description'], null, 'class = "inputCheckbox"', array(0,1));
            			break;
            		case 'text':
            			$form -> addElement("text", $field['name'], $field['description'], 'class = "inputText"');
            			break;
            		case 'textarea':
            			$form -> addElement("textarea", $field['name'], $field['description'], 'class = "inputContentTextarea simpleEditor" style = "width:100%;height:8em;"');
            			break;
            		case 'date':
            			$options = unserialize($field['options']);
            			$form -> addElement(EfrontEntity::createDateElement($form, $field['name'], $field['description'], array('minYear' => $options['year_range_from'], 'maxYear' => $options['year_range_to'], 'include_time' => $options['include_time'])));
            			break;
            		default: break;
            	}
            
            	if ($field['mandatory'] == 1) {
            		$form -> addRule($field['name'], _THEFIELD.' "'.$field['description'].'" '._ISMANDATORY, 'required', null, 'client');
            	}
            	$additional_fields[] = $field['name'];
            }
            $smarty -> assign("T_ADDITIONAL_FIELDS", $additional_fields);            
            
            break;
        }
        case 'update_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'login', _LOGIN, 'class = "inputText"');    
            $form -> addElement('password', 'password', _PASSWORD, 'class = "inputText"');    
            $form -> addElement('text', 'name', _FIRSTNAME, 'class = "inputText"');   
            $form -> addElement('text', 'surname', _LASTNAME, 'class = "inputText"'); 
            $form -> addElement('text', 'email', _EMAIL, 'class = "inputText"');   
            $form -> addElement('text', 'language', _LANGUAGE, 'class = "inputText"');

            $userProfile = eF_getTableData("user_profile", "*", "active=1");
            $additional_fields= array();
        	foreach ($userProfile as $key => $field) {
				switch ($field['type']) {
					case 'select':
						$form -> addElement("select", $field['name'], $field['description'], unserialize($field['options']));
						break;
					case 'checkbox':
						$form -> addElement("advcheckbox", $field['name'], $field['description'], null, 'class = "inputCheckbox"', array(0,1));
						break;
					case 'text':
						$form -> addElement("text", $field['name'], $field['description'], 'class = "inputText"');
						break;
					case 'textarea':
						$form -> addElement("textarea", $field['name'], $field['description'], 'class = "inputContentTextarea simpleEditor" style = "width:100%;height:8em;"');
						break;
					case 'date':
						$options = unserialize($field['options']);
						$form -> addElement(EfrontEntity::createDateElement($form, $field['name'], $field['description'], array('minYear' => $options['year_range_from'], 'maxYear' => $options['year_range_to'], 'include_time' => $options['include_time'])));
						break;
					default: break;
				}

				if ($field['mandatory'] == 1) {
					$form -> addRule($field['name'], _THEFIELD.' "'.$field['description'].'" '._ISMANDATORY, 'required', null, 'client');
				}
				$additional_fields[] = $field['name'];
			}
			$smarty -> assign("T_ADDITIONAL_FIELDS", $additional_fields);
            break;
        }
        case 'activate_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'login', _LOGIN, 'class = "inputText"');    
            break;
        }
        case 'deactivate_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN, 'class = "inputText"');    
           break; 
        }
        case 'remove_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'login', _LOGIN, 'class = "inputText"');    
            break;
        }
        case 'users':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            break;
        }
        case 'groups':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            break;
        }
        case 'group_info':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'group', _GROUP, 'class = "inputText"');    
            break;
        }
        case 'group_users':{
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	$form -> addElement('text', 'group', _GROUP,   'class = "inputText"');
        	break;
        }
        
        case 'group_to_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            $form -> addElement('text', 'group', _GROUP, 'class = "inputText"');    
            break;
        }
        case 'group_from_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            $form -> addElement('text', 'group', _GROUP, 'class = "inputText"');    
            break;
        }
        case 'lesson_to_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            $form -> addElement('text', 'lesson', _LESSON, 'class = "inputText"');   
			$form -> addElement("select", "type", _USERTYPE, array("student"=>_STUDENT, "professor"=>_PROFESSOR), 'class = "inputText"');			
            break;
        }
        case 'lesson_from_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            $form -> addElement('text', 'lesson', _LESSON, 'class = "inputText"');    
            break;
        }
        case 'user_lessons':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            break;
        }
        case 'course_to_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            $form -> addElement('text', 'course', _COURSE, 'class = "inputText"');
			$form -> addElement("select", "type", _USERTYPE, array("student"=>_STUDENT, "professor"=>_PROFESSOR), 'class = "inputText"');
            break;
        }
		case 'curriculum_to_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            $form -> addElement('text', 'curriculum', _CURRICULUM, 'class = "inputText"');
            break;
        }
        case 'course_from_user':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            $form -> addElement('text', 'course', _COURSE, 'class = "inputText"');    
            break;
        }

        case 'user_courses':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            break;
        }
        case 'user_groups':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');    
            break;
        }
        case 'lesson_info':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'lesson', _LESSON, 'class = "inputText"');    
            break;
        }
        case 'course_info':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'course', _COURSE, 'class = "inputText"');    
            break;
        }
		case 'course_lessons':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            $form -> addElement('text', 'course', _COURSE, 'class = "inputText"');    
            break;
        }
        case 'user_info':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');  
            $form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');  
            break;
        }
        case 'catalog':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            break;
        }
        case 'lessons':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            break;
        }
        case 'courses':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            break;
        }
        case 'logout':{
            $form -> addElement('text', 'token', _TOKEN,   'class = "inputText"'); 
            break;
        }
        case 'categories':{
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	break;
        }
        case 'category':{
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	$form -> addElement('text', 'direction', 'Direction ID',   'class = "inputText"');
        	break;
        }
        case 'buy_lesson':{
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	$form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');
        	$form -> addElement('text', 'lesson', _LESSON, 'class = "inputText"');
        	break;
        }
        case 'buy_course':{
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	$form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');
        	$form -> addElement('text', 'course', _COURSE, 'class = "inputText"');
        	break;
        }  
        case 'get_user_autologin_key':{
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	$form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');
        	break;
        }
        case 'set_user_autologin_key':{
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	$form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');
        	break;
        }
        case 'languages': {
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	break;
        }
        case 'user_to_branch': {
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	$form -> addElement('text', 'login', _LOGIN,   'class = "inputText"');
        	$form -> addElement('text', 'branch', _BRANCH,   'class = "inputText"');
        	$form -> addElement('text', 'job', _JOBDESCRIPTION,   'class = "inputText"');
        	$form -> addElement('text', 'position', _EMPLOYEEPOSITION,   'class = "inputText"');
        	$form -> addElement('text', 'job_description', _JOBANALYTICALDESCRIPTION,   'class = "inputText"');
        	break;
        }
        case 'branch_jobs': {
        	$form -> addElement('text', 'token', _TOKEN,   'class = "inputText"');
        	$form -> addElement('text', 'branch', _BRANCH,   'class = "inputText"');
        	break;
        }        
        
    }
    $form -> addElement('textarea', 'output', _OUTPUT, 'id = "output" class = "simpleEditor inputTextarea" style = "disabled:true;width:60%;height:120px"'); 
    $form -> addElement('submit', 'submit_action', _SUBMIT, 'class = "flatButton"');
    
    if ($form -> isSubmitted()) {
        if ($form -> validate()) {
            $values = $form -> exportValues();
            switch ($action){
                case 'token':{
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=token', 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;
                }
                case 'login':{
                    $login = $values['login'];
                    $pwd = $values['password'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=login&username='.$login.
                     '&password='.$pwd."&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
                case 'efrontlogin':{
                    $token = $values['token'];
					$login = $values['login'];
					/*
					 * Update: See "efrontlogin_ajax" below on how to do this (and on apidemo2.tpl file as well)
					 * 
					 * WARNING: This will not work as expected: It will simply register the user as being login, without actually logging 
					 * in the browser to the system, due to the inability to set session variables through fopen() (and streams in general).
					 * If we need to login the current browser to the system, we need to open an actual connection FROM the browser to the
					 * api2.php page, using the same URL query string. For example, this can be done using header(), an iframe, or a javascript
					 * popup window. For example:
					 * echo "<script>var mine = window.open('api2.php?action=efrontlogin&token=".$token."&login=".$login."', 'api', 'width=1,height=1,left=0,top=0,scrollbars=no');</script>";
					 * -OR- using AJAX query:
					 * 		echo '
					 *			<script type = "text/javascript" src = "js/scriptaculous/prototype.js"> </script>
					 *			<script>new Ajax.Request("api2.php?action=efrontlogin&token='.$token.'&login='.$login.'")</script>';
					 */ 
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=efrontlogin&token='.$token.'&login='.$login, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);                        
                    }
                    break;   
                }
                case 'efrontlogin_ajax':{
                	session_start();
                	$smarty -> assign("T_LOGIN_AJAX_TOKEN", $values['token']);
                	$smarty -> assign("T_LOGIN_AJAX_LOGIN", $values['login']);                	
                    break;   
                }
                case 'efrontlogout':{
                    $token = $values['token'];
					$login = $values['login'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=efrontlogout&token='.$token.'&login='.$login, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
				case 'create_lesson':{
                    $name = $values['name'];
                    $category = $values['category'];
                    $token = $values['token'];
                    $course_only = $values['course_only'];
                    $price = $values['price'];
                    $language = $values['language'];
					$token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=create_lesson&name='.urlencode($name).
                     '&category='.$category.'&course_only='.$course_only.'&price='.$price.'&language='.$language.'&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;
				}
                case 'create_user':{
                    $login = $values['login'];
                    $pwd = $values['password'];
                    $token = $values['token'];
                    $name = $values['name'];
                    $surname = $values['surname'];
                    $language = $values['language'];
                    $email = $values['email'];
                    $token = $values['token'];

                    $userProfile = eF_getTableData("user_profile", "*", "active=1");
                    $custom_fields_uri = "";
                    foreach ($userProfile as $key => $field) {
                    	$custom_fields_uri .= "&".$field['name']."=".urlencode($values[$field['name']]);
                    }                    
                    
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=create_user&login='.$login.
                     '&password='.$pwd.'&name='.$name.'&surname='.$surname.'&email='.$email.'&languages='.$language.'&token='.$token.$custom_fields_uri, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;
                }
                case 'update_user':{
                    $login 		= $values['login'];
                    $pwd 		= $values['password'];
                    $token 		= $values['token'];
                    $name	 	= $values['name'];
                    $surname 	= $values['surname'];
                    $email 		= $values['email'];
                    $token 		= $values['token'];
					$language 	= $values['language'];
					
					$userProfile = eF_getTableData("user_profile", "*", "active=1");
					$custom_fields_uri = "";
					foreach ($userProfile as $key => $field) {
						$custom_fields_uri .= "&".$field['name']."=".urlencode($values[$field['name']]);
					}
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=update_user&login='.$login.
                     '&password='.$pwd.'&name='.$name.'&surname='.$surname.'&email='.$email.'&token='.$token.'&language='.$language.$custom_fields_uri, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;
                }
                case 'activate_user':{
                    $login = $values['login'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=activate_user&login='.$login.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
                case 'deactivate_user':{
                    $login = $values['login'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=deactivate_user&login='.$login.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }
                case 'remove_user':{
                    $login = $values['login'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=remove_user&login='.$login.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
                case 'users':{
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=users&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;
                }
                case 'groups':{
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=groups&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;
                }
                case 'group_info':{
                    $group = $values['group'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=group_info&group='.$group.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }
                case 'group_users':{
                	$group = $values['group'];
                	$token = $values['token'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=group_users&group='.$group.
                			"&token=".$token, 'r')) {
                			$output = stream_get_contents($stream);
                			fclose($stream);
                	}
                	break;
                }
                
                case 'group_to_user':{ 
                    $login = $values['login'];
                    $group = $values['group'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=group_to_user&login='.$login.
                    '&group='.$group.'&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
                case 'group_from_user':{
                    $login = $values['login'];
                    $group = $values['group'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=group_from_user&login='.$login.
                    '&group='.$group.'&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                    break;
                }
                case 'lesson_to_user':{ 
                    $login = $values['login'];
                    $lesson = $values['lesson'];
					$type 	= $values['type'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=lesson_to_user&login='.$login.
                    '&lesson='.$lesson.'&type='.$type.'&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
                case 'lesson_from_user':{
                    $login = $values['login'];
                    $lesson = $values['lesson'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=lesson_from_user&login='.$login.
                    '&lesson='.$lesson.'&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                    break;
                }
                case 'user_lessons':{
                    $login = $values['login'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=user_lessons&login='.$login.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }
                case 'lesson_info':{
                    $lesson = $values['lesson'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=lesson_info&lesson='.$lesson.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }
                case 'course_to_user':{ 
                    $login 	= $values['login'];
                    $course = $values['course'];
					$type 	= $values['type'];
                    $token 	= $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=course_to_user&login='.$login.
                    '&course='.$course.'&type='.$type.'&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
                case 'course_from_user':{
                    $login = $values['login'];
                    $course = $values['course'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=course_from_user&login='.$login.
                    '&course='.$course.'&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                    break;
                }
                case 'user_courses':{
                    $login = $values['login'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=user_courses&login='.$login.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }
                case 'user_groups':{
                    $login = $values['login'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=user_groups&login='.$login.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }
                case 'course_info':{
                    $course = $values['course'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=course_info&course='.$course.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }
				case 'course_lessons':{
                    $course = $values['course'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=course_lessons&course='.$course.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }

                case 'user_info':{
                    $login = $values['login'];
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=user_info&login='.$login.
                    "&token=".$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;    
                }
                case 'catalog':{
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=catalog&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                    break;
                }
                case 'lessons':{
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=lessons&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                    break;
                }
                case 'courses':{
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=courses&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                    break;
                }
				case 'curriculum_to_user':{ 
                    $login 			= $values['login'];
                    $curriculum 	= $values['curriculum'];
                    $token 			= $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=curriculum_to_user&login='.$login.
                    '&curriculum='.$curriculum.'&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
                case 'logout':{
                    $token = $values['token'];
                    if ($stream = fopen(G_SERVERNAME.'api2.php?action=logout&token='.$token, 'r')) {
                        $output = stream_get_contents($stream);
                        fclose($stream);
                    }
                    break;   
                }
                case 'categories':{
                	$token = $values['token'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=categories&token='.$token, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}
                	break;
                }
                case 'category':{
                	$token = $values['token'];
                	$category_id = $values['direction'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=category&token='.$token.'&category='.$category_id, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}
                	break;
                }
                case 'buy_lesson':{
                	$token = $values['token'];
                	$login = $values['login'];
                	$lesson_id = $values['lesson'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=buy_lesson&token='.$token.'&login='.$login.'&lesson='.$lesson_id, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}
                	break;
                }
                case 'buy_course':{
                	$token = $values['token'];
                	$login = $values['login'];
                	$course_id = $values['course'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=buy_course&token='.$token.'&login='.$login.'&course='.$course_id, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}                	
                	break;
                }
                case 'get_user_autologin_key':{
                	$token = $values['token'];
                	$login = $values['login'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=get_user_autologin_key&token='.$token.'&login='.$login, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}
                	break;
                }
                case 'set_user_autologin_key':{
                	$token = $values['token'];
                	$login = $values['login'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=set_user_autologin_key&token='.$token.'&login='.$login, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}
                	break;
                }
                case 'languages': {
                	$token = $values['token'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=languages&token='.$token, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}
                	 
                	break;
                }
                case 'user_to_branch': {
                	$token = $values['token'];
                	$login = $values['login'];
                	$branch = $values['branch'];
                	$job = $values['job'];
                	$position = $values['position'];
                	$job_description = $values['job_description'];
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=user_to_branch&token='.$token.'&login='.$login.'&branch='.$branch.'&job='.$job.'&position='.$position.'&job_description='.$job_description, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}
                	break;
                }
                case 'branch_jobs': {
                	$token = $values['token'];
                	$branch = $values['branch'];
                	pr(G_SERVERNAME.'api2.php?action=branch_jobs&token='.$token.'&branch='.$branch);
                	if ($stream = fopen(G_SERVERNAME.'api2.php?action=branch_jobs&token='.$token.'&branch='.$branch, 'r')) {
                		$output = stream_get_contents($stream);
                		fclose($stream);
                	}
                	break;
                }                
            }
        }
    }
    $form -> setDefaults(array('action' => $action_id)); 
    $element = & $form->getElement('output');
    $element -> setValue($output);
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
    $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
    $form -> setRequiredNote(_REQUIREDNOTE);
    $form -> accept($renderer);
    $smarty -> assign('T_ACTION_FORM', $renderer -> toArray());         
    $smarty -> display('apidemo2.tpl');
?>
