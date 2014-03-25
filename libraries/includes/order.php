<?php
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

!isset($currentUser -> coreAccess['content']) || $currentUser -> coreAccess['content'] == 'change' ? $_change_ = 1 : $_change_ = 0;
$smarty -> assign("_change_", $_change_);
if (!$_change_) {
    eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
    exit;
}

$loadScripts[] = 'includes/order';

try {
    $currentContent = new EfrontContentTree($currentLesson);
    $iterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST));

    //Legal values are the array of entities that the current user may actually edit or change.
    foreach ($iterator as $key => $value) {
        $legalValues[] = $key;
    }

    $smarty -> assign("T_UNIT_ORDER_TREE", $currentContent -> toHTML($iterator, 'dhtmlContentTree', array('delete' => true, 'noclick' => true, 'activate' => true, 'drag' => true, 'expand' => true)));
    $options = array(array('image' => '16x16/undo.png', 'text' => _REPAIRTREE, 'href' => 'javascript:void(0)', 'onClick' => 'if (confirm (\''._ORDERWILLPERMANENTLYCHANGE.'\')) repairTree(this);'));
    $smarty -> assign("T_TABLE_OPTIONS", $options);

    try {
        if (isset($_POST['delete_nodes']) && $_POST['delete_nodes']) {
            //Needed in order to delete branches as well            
            $_POST['delete_nodes'] = array_reverse($_POST['delete_nodes']);
            foreach ($_POST['delete_nodes'] as $value) {
                try {
                    if (in_array($value, $legalValues) && eF_checkParameter($value, 'id')) {
                        $currentContent -> removeNode($value);
                        if (($pos = array_search($value, $legalValues)) !== false) {
                            unset($legalValues[$pos]);
                        }
                    }
                } catch (Exception $e) {
                    $errorMessages[] = $e -> getMessage().' '.$e -> getCode();
                }
            }
        }        

        if (isset($_POST['activate_nodes']) && $_POST['activate_nodes']) {
            
            foreach ($_POST['activate_nodes'] as $value) {
                if (in_array($value, $legalValues) && eF_checkParameter($value, 'id')) {
                    try {
                        $currentContent -> seekNode($value) -> activate();
                    } catch (Exception $e) {
                        $errorMessages[] = $e -> getMessage().' '.$e -> getCode();
                    }
                }
            }
        }
        if (isset($_POST['deactivate_nodes']) && $_POST['deactivate_nodes']) {
            foreach ($_POST['deactivate_nodes'] as $value) {
                if (in_array($value, $legalValues) && eF_checkParameter($value, 'id')) {
                    try {
                        $currentContent -> seekNode($value) -> deactivate();
                    } catch (Exception $e) {
                        $errorMessages[] = $e -> getMessage().' '.$e -> getCode();
                    }
                }
            }
        }
        if (isset($_POST['node_orders']) && $_POST['node_orders']) {     	
            //$nodeOrders        = explode(",", $_POST['node_orders']);
            $previousContentId = 0; 
            $legalValues[]  = 0;    					//Add 0 to possible content units, since both parent and previous units may be 0          
            foreach ($_POST['node_orders'] as $value) {	
                list($id, $parentContentId) = explode("-", $value);
                if ($id && in_array($id, $legalValues) && in_array($parentContentId, $legalValues) && eF_checkParameter($id, 'id') && eF_checkParameter($parentContentId, 'id') !== false ) {
					try {   		                      //Putting the try/catch block here, makes the process to continue even if it fails for some units
						$unit = $currentContent -> seekNode($id);
                        $unit -> offsetSet('previous_content_ID', $previousContentId);
                        $unit -> offsetSet('parent_content_ID', $parentContentId);
                        $unit -> offsetSet('data', $unit['data']);
                        $unit -> persist();
                        $previousContentId = $id;
                    } catch (Exception $e) {
                        $errorMessages[] = $e -> getMessage().' '.$e -> getCode();
                    }
                }
            }
			//echo $previousContentId;exit;
        }
        if (isset($_POST['repair_tree'])) {
            $currentContent -> repairTree();
        }

        if (isset($errorMessages) && $errorMessages) {
            header("HTTP/1.0 500 ");
            echo _ERRORSAVINGTREE."\n".implode("\n", $errorMessages);
        }
    } catch (Exception $e) {
        header("HTTP/1.0 500 ");
        echo $e -> getMessage().' ('.$e -> getCode().')';
        exit;
    }

} catch (Exception $e) {
    $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
    $message      = _ERRORLOADINGCONTENT." ".$_SESSION['s_lessons_ID'].": ".$e -> getMessage().' &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
    $message_type = 'failure';
}
 if (isset($_GET['ajax'])) {exit;}
