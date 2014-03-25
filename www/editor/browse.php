<?php
/**
* This file is used to display a small files list, and is used 
* inside the "insert image" operation of the editor
*/

//General initialization and parameters
session_cache_limiter('none');
session_id($_COOKIE['parent_sid']);
session_start();

$path = "../../libraries/";
/** Configuration file.*/
include_once $path."configuration.php";

//Access is not allowed to users that are not logged in
if (isset($_SESSION['s_login']) && $_SESSION['s_password']) {
    try {
        $currentUser = EfrontUserFactory :: factory($_SESSION['s_login']);
    } catch (EfrontException $e) {
        $message = $e -> getMessage().' ('.$e -> getCode().')';
        eF_redirect("index.php?message=".urlencode($message)."&message_type=failure");
        exit;
    }
} else {
    eF_redirect("index.php?message=".urlencode(_YOUCANNOTACCESSTHISPAGE)."&message_type=failure");
    exit;
}

try {
    //There are 2 legal modes: 'lessons' and 'external'. In the first case, we read the legitimate directory from the session. In the second case, we take it from global constant    
    if ($_GET['mode'] == 'lesson') {
        $currentLesson = new EfrontLesson($_SESSION['s_lessons_ID']);
        $rootDir       = new EfrontDirectory($currentLesson -> getDirectory());
        $filesBaseUrl  = $currentLesson -> getDirectoryUrl();
        
    } elseif ($_GET['mode'] == 'external') {
        $rootDir       = new EfrontDirectory(G_EXTERNALPATH);
        $filesBaseUrl  = G_EXTERNALURL;
    } elseif ($_GET['mode'] == 'upload') {
    	$rootDir       = new EfrontDirectory(G_UPLOADPATH.$_SESSION['s_login']);
    	$filesBaseUrl  = G_UPLOADPATH.$_SESSION['s_login'];
    } else {
        throw new Exception(_ILLEGALMODE);
    }

    //We are inside a directory. Verify that this directory is below the $rootDir, as defined previously
    if (isset($_GET['directory'])) {
        $directory = new EfrontDirectory($_GET['directory']);
        if (strpos($directory['path'], $rootDir['path']) === false) {
            $directory = $rootDir;
        } else {
            if (EfrontDirectory :: normalize($directory['path']) == EfrontDirectory :: normalize($rootDir['path'])) {

                $smarty -> assign("T_PARENT_DIR", '');
            } else {
                $smarty -> assign("T_PARENT_DIR", $directory['directory']);
            }
        }
    } else {
        $directory = $rootDir;
    }

    $offset = str_replace($rootDir['path'], '', $directory['path'].'/');
	//$t_offset = rtrim($filesBaseUrl.$offset, '/').'/';  //possibly the problem with doulbe slash will be fixed by removing / from the above line, but in order to be sure .... 
    $t_offset = str_replace('//','/', $filesBaseUrl.$offset.'/');
    $t_offset = str_replace('//','/', $t_offset);  
	$smarty -> assign("T_OFFSET", $t_offset);
  	$files = $folders = array();
    //for_type defines which kind of files we need.
    switch ($_GET['for_type']) {
        case 'image'	: $mode = true; $filter = array_keys(FileSystemTree :: getFileTypes('image')); break;
        case 'java' 	: $mode = true; $filter = array_keys(FileSystemTree :: getFileTypes('java'));  break;
        case 'media'	: $mode = true; $filter = array_keys(FileSystemTree :: getFileTypes('media')); break;
		case 'document'	: $mode = true; $filter = array_keys(FileSystemTree :: getFileTypes('document')); break;
        case 'files'	: $mode = false; $filter = array(); break;
        default     	: $mode = true; $filter = array(); break;        
    }

    $filesystem = new FileSystemTree($directory['path']);
    
    //$directory != $rootDir ? $tree = $filesystem -> seekNode($directory['path']) : $tree = $filesystem -> tree; // Changed because of #2634
    $tree = $filesystem -> tree;
    foreach (new EfrontDirectoryOnlyFilterIterator(new EfrontNodeFilterIterator(new ArrayIterator($tree, RecursiveIteratorIterator :: SELF_FIRST))) as $key => $value) {
        $value['image']    = $value -> getTypeImage();
        $folders[]         = (array)$value;
    }
    foreach (new EfrontFileOnlyFilterIterator(new EfrontFileTypeFilterIterator(new EfrontNodeFilterIterator(new ArrayIterator($tree, RecursiveIteratorIterator :: SELF_FIRST)), $filter, $mode)) as $key => $value) {
        $value['image']    = $value -> getTypeImage();
        $files[]           = (array)$value;
    }
    //for sorting files
	$folders 	= eF_multiSort($folders, 'name');
	$files 		= eF_multiSort($files, 'name');
	$files 		= array_merge((array)$folders, (array)$files);
//pr($files);
    $smarty -> assign("T_FILES", $files);
} catch (Exception $e) {
    $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
    $message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
    $message_type = 'failure';
}

$smarty -> assign("T_MESSAGE", $message);
$smarty -> assign("T_MESSAGE_TYPE", $message_type);
$smarty -> display("browse.tpl");
?>
