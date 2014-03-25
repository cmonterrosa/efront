<?php
#cpp#ifndef COMMUNITY

if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

if (isset($currentLesson -> options['smart_content']) && $currentLesson -> options['smart_content'] == 0) {
    eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
}

/*
 define("_IMPORTMETHOD", "Import method");
 define("_FROMURL", "From URL");
 define("_FROMPATH", "From path");
 */

$loadScripts[] = 'includes/import';

$form = new HTML_QuickForm("import_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=import", "", null, true);

/*
 $form -> addElement('select', 'import_type', _IMPORTTYPE, array('efront'    => _EFRONTFILE,
 'scorm2004' => _SCORM2004,
 'scorm12'   => _SCORM12,
 //'aicc'      => _AICC,
 //'csv'       => _CSV,
 'pdf'       => _PDF,
 //'doc'       => _DOC,
 'html'      => _HTML,
 'xml'       => _XML,
 'auto'      => _AUTODETECT));
 */
$form -> addElement('advcheckbox', 'folders_to_hierarchy', _CONVERTFOLDERSTOHIERARCHY, null, 'class = "inputCheckbox"', array(0, 1));
$form -> addElement('advcheckbox', 'uncompress_recursive', _UNCOMPRESSRECURSIVELYIMPORT, null, 'class = "inputCheckbox"', array(0, 1));
$form -> addElement('advcheckbox', 'prompt_download', _FORCEDOWNLOADFILE, null, 'class = "inputCheckbox"', array(0, 1));
$form -> addElement('static', 'note', _FORCEDOWNLOADFILEINFO);
$form -> setDefaults(array('folders_to_hierarchy' => true));

//$form -> addElement('select', 'import_method', _IMPORTMETHOD, array(_UPLOADFILE, _FROMURL, _FROMPATH), 'onchange = "selectBox(this);"');
$form -> addElement('file', 'import_file[0]', _IMPORTFILE);
for ($i = 1; $i < 10; $i++) {
    $form -> addElement('file', "import_file[$i]", null);
}
$form -> addElement('text', "import_url[0]", _IMPORTFROMURL, 'class = "inputText"');
for ($i = 1; $i < 10; $i++) {
    $form -> addElement('text', "import_url[$i]", null, 'class = "inputText"');
}
$form -> addElement('text', "import_path[0]", _IMPORTFROMPATH, 'class = "inputText"');
for ($i = 1; $i < 10; $i++) {
    $form -> addElement('text', "import_path[$i]", null, 'class = "inputText"');
}
$form -> setMaxFileSize(FileSystemTree :: getUploadMaxSize() * 1024);            //getUploadMaxSize returns size in KB
$form -> addElement('submit', 'import_submit', _IMPORT, 'class = "flatButton"');

if ($form -> isSubmitted() && $form -> validate()) {
	try {
		$values = $form -> exportValues();
		$errors = $uploadedFiles = array();

		//Create, if it does not exist, the folder where the files will be uploaded
		//is_dir($uploadDir = $currentUser -> getDirectory().'temp/') OR mkdir($uploadDir, 0755);
		$uploadDir = $currentLesson -> getDirectory();
		$filesystem = new FileSystemTree($uploadDir, true);

		//Perform any direct file uploads
		foreach ($_FILES['import_file']['name'] as $key => $name) {
			if (!in_array($name, $uploadedFiles)) {        //This way we bypass duplicates
				try {
					$uploadedFiles[$name] = $filesystem -> uploadFile("import_file", $uploadDir, $key);
				} catch (EfrontFileException $e) {
					if ($e -> getCode() != UPLOAD_ERR_NO_FILE) {
						$errors[] = $e -> getMessage();
					}
				}
			}
		}
		//Perform any url uploads
		foreach ($values['import_url'] as $key => $urlUpload) {
			if ($urlUpload && !in_array(basename($urlUpload), $uploadedFiles)) {
				FileSystemTree :: checkFile($urlUpload);
				$urlArray = explode("/", $urlUpload);
				$urlFile  = urldecode($urlArray[sizeof($urlArray) - 1]);
				if (!copy(dirname($urlUpload).'/'.rawurlencode(basename($urlUpload)), $uploadDir."/".$urlFile)) {
					$errors[] = _PROBLEMUPLOADINGFILE.': '.$urlUpload;
				} else {
					$uploadedFiles[basename($urlUpload)] = new EfrontFile($uploadDir."/".$urlFile);
				}
			}
		}

		//Perform any path uploads
		foreach ($values['import_path'] as $key => $pathUpload) {
			if ($pathUpload && !in_array($pathUpload, $uploadedFiles)) {
				$pathUpload = EfrontDirectory :: normalize($pathUpload);
				if (strpos(dirname($pathUpload), rtrim(G_ROOTPATH, "/")) !== false) {
					FileSystemTree :: checkFile($pathUpload);
					$pathArray = explode("/", $pathUpload);
					$pathFile  = urldecode($pathArray[sizeof($pathArray) - 1]);
					if (!copy($pathUpload, $uploadDir."/".$pathFile)) {
						$errors[] = _PROBLEMUPLOADINGFILE.': '.$pathUpload;
					} else {
						$uploadedFiles[basename($pathUpload)] = new EfrontFile($uploadDir."/".$pathFile);
					}
				} else {
					$errors[] = _PROBLEMUPLOADINGFILE.': '.$pathUpload;
				}
			}
		}

		if (!empty($errors)) {
			throw new Exception(implode("<br>", $errors));
		}

		//Re-index $uploadedFiles
		$uploadedFiles = array_values($uploadedFiles);
		//We use for instead of foreach, because the 'uncompress_recursive' parameter may augment the $uploadedFiles array
		$unzipFlag = true;
		for ($i = 0; $i < sizeof($uploadedFiles); $i++) {
			$emptyUnits = array();
			$file = $uploadedFiles[$i];
			$pathParts = pathinfo($file['name']);
				
			if ($pathParts['extension'] == 'zip' && $unzipFlag == true) {
					$fileContents = $file -> listContents();
					sort($fileContents);
					//Check if it is a SCORM file
					if (in_array("imsmanifest.xml", $fileContents)) {
						$scormFolderName = EfrontFile :: encode(basename($file['name'], '.zip'));
						$scormPath       = $currentLesson -> getDirectory().$scormFolderName.'/';
						is_dir($scormPath) OR mkdir($scormPath, 0755);

						$file -> rename($scormPath.$file['name'], true);
						$file -> uncompress(false);

						$manifestFile = new EfrontFile($scormPath.'imsmanifest.xml');
						EfrontScorm :: import($currentLesson, $manifestFile, $scormFolderName);
					}
					//Check if it is efront proprietary file
					elseif (in_array("data.dat", $fileContents)) {
						$currentLesson -> import($file);
					}
					//General case. Simply uncompress the file
					else {
						$file -> uncompress();
						if ($values['uncompress_recursive']) {
							$unzipFlag = false;
							foreach ($fileContents as $additionalFile) {
								if (!is_dir($uploadDir.$additionalFile)) {
									//Add to the list of the uploaded files, all those that where extracted
									$uploadedFiles[] = new EfrontFile($uploadDir.$additionalFile);
								} else {
									//Assign folders to the $emptyUnits array, so that we can create the empty units hierarchy from them
									$emptyUnits[] = explode("/", trim($additionalFile, "/"));
								}
							}

							//Create the empty Units hierarchy
							$currentContent = new EfrontContentTree($currentLesson);
							$treeStructure = $currentContent -> createEmptyUnits($emptyUnits, $currentLesson -> lesson['id']);
						}

					}
					$file -> delete();
					//break;
			} else {
				if (!isset($currentContent)) {
					$currentContent = new EfrontContentTree($currentLesson);
				}
				foreach ($treeStructure as $key => $value) {
					end($value);
					$units[implode("/", $value)] = key($value);
				}

				$offset   		= str_replace($currentLesson -> getDirectory(), "", $file['path']);
				$offsetDir   	= str_replace($currentLesson -> getDirectory(), "", $file['directory']);
				$parentId		 = $units[dirname($offset)];

				$fields = array('name'       => basename($file['name'], '.'.$pathParts['extension']),
                                'lessons_ID' => $currentLesson -> lesson['id'],
                                'parent_content_ID' => $parentId ? $parentId : 0);
				
				$pathParts['extension'] = strtolower($pathParts['extension']);
				if ($pathParts['extension'] == 'pdf')	{
					$fields['data'] = '<iframe src="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'"  name="pdfaccept" width="100%" height="600"></iframe>';
					$unit = $currentContent -> insertNode($fields);
				}  elseif (in_array($pathParts['extension'], array_keys(FileSystemTree :: getFileTypes('image')))) {
                    $fields['data'] = '<img src="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" border="0" />';
					$unit = $currentContent -> insertNode($fields);
				} elseif (strpos(EfrontFile :: $mimeTypes[$pathParts['extension']] , "x-m4v") !== false) {
					$fields['data'] = '<object height="400" width="500" codebase="http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0" classid="clsid:02bf25d5-8c17-4b23-bc80-d3488abddc6b"><param name="src" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" /><embed height="400" width="400" src="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" type="video/quicktime"></embed></object>';
					$unit = $currentContent -> insertNode($fields);
				} elseif (strpos(EfrontFile :: $mimeTypes[$pathParts['extension']] , "mp4") !== false) {
					$fields['data'] = '<object height="400" width="500" codebase="http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0" classid="clsid:02bf25d5-8c17-4b23-bc80-d3488abddc6b"><param name="src" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" /><embed height="400" width="400" src="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" type="video/quicktime"></embed></object>';
					$unit = $currentContent -> insertNode($fields);
				} elseif (strpos(EfrontFile :: $mimeTypes[$pathParts['extension']] , "mpeg") !== false) {
					$fields['data'] = '<object width="500" height="400" data="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" type="video/quicktime"><param name="url" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" /><param name="src" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" /></object>';
					$unit = $currentContent -> insertNode($fields);
				} elseif ($pathParts['extension'] == 'class')	{
              		$fields['data'] = '<table width="632" height="345" rules="rows" frame="box" cellspacing="4" cellpadding="4" border="2" style="border-style: dotted; border-width: 3px;  vertical-align: top; color: rgb(51, 51, 51); background-color: rgb(204, 255, 153);"><tbody><tr><td align="center" valign="center"><applet codebase="'.$currentLesson -> getDirectoryUrl().'/'.$offsetDir.'" code="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" width="632" height="345"/></applet><img src="images/file_types/java.gif" /></td></tr></tbody></table>';
                	$unit = $currentContent -> insertNode($fields);
				} elseif (strpos(EfrontFile :: $mimeTypes[$pathParts['extension']] , "html") !== false) {
					$fields['data'] = '<iframe src="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" frameborder="0" name="htmlFrame_'.urlencode($file['id']).'" width="100%" height="500px"></iframe>';
					$unit = $currentContent -> insertNode($fields);
				} elseif (strpos(EfrontFile :: $mimeTypes[$pathParts['extension']] , "flash") !== false) {
					$fields['data'] = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" width="400" height="400">
							<param name="src" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" />
							<param name="url" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" />
							<param name="width" value="400" />
							<param name="height" value="400" />
							<embed type="application/x-shockwave-flash" url="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" src="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" width="400" height="400"></embed>
							</object>';
					$unit = $currentContent -> insertNode($fields);
				} elseif (strpos(EfrontFile :: $mimeTypes[$pathParts['extension']] , "wmv") !== false) {
					$fields['data'] = '<object classid="clsid:6BF52A52-394A-11D3-B153-00C04F79FAA6" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701" width="300" height="300">
							<param name="width" value="300" />
							<param name="height" value="300" />
							<param name="src" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" />
							<param name="url" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" />
							<embed type="application/x-mplayer2" width="300" height="300" url="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" src="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'"></embed>
						</object>';
					$unit = $currentContent -> insertNode($fields);
				} elseif (strpos(EfrontFile :: $mimeTypes[$pathParts['extension']] , "audio") !== false) {
					$fields['data'] = '<object width="320" height="40" data="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" type="video/quicktime"><param name="src" value="'.$currentLesson -> getDirectoryUrl().'/'.$offset.'" /></object>';
					$unit = $currentContent -> insertNode($fields);
				} elseif (strpos(EfrontFile :: $mimeTypes[$pathParts['extension']] , "flv") !== false) {
					$fields['data'] = '<iframe width="300" height="300" src="editor/tiny_mce/plugins/media/img/flv_player.swf?flvToPlay=##EFRONTEDITOROFFSET##'.$currentLesson -> getDirectoryUrl().'/'.$offset.'&amp;autostart=0" frameborder="0"></iframe>';
					$unit = $currentContent -> insertNode($fields);
				} elseif ($values['prompt_download']) {
					$fields['data'] = '<iframe style="visibility:hidden;display:none" src="view_file.php?file='.$file['id'].'&action=download"></iframe>';
					$unit = $currentContent -> insertNode($fields);
				} elseif ($pathParts['extension'] == 'txt')	{
					$fields['data'] = '<a href = "view_file.php?file='.$file['id'].'&action=download"><img src="images/file_types/txt.png" style="vertical-align:middle" />'.basename($file['name']).'</a>';
					$unit = $currentContent -> insertNode($fields);
				} elseif ($pathParts['extension'] == 'doc' || $pathParts['extension'] == 'rtf' || $pathParts['extension'] == 'docx' || $pathParts['extension'] == 'xls' || $pathParts['extension'] == 'xlsx' || $pathParts['extension'] == 'ppt' || $pathParts['extension'] == 'pptx' || $pathParts['extension'] == 'zip' || $pathParts['extension'] == 'rar')	{
					$fields['data'] = '<a href = "view_file.php?file='.$file['id'].'&action=download"><img src="images/file_types/'.$pathParts['extension'].'.png" style="vertical-align:middle" />'.basename($file['name']).'</a>';
					$unit = $currentContent -> insertNode($fields);
				} elseif ($pathParts['extension'] == 'js') {
					//do not create unit for js files
				}
				else {
					if (file_exists(G_THEMESPATH.'default/images/file_types/'.$pathParts['extension'].'.png')){
						$fields['data'] = '<a href = "view_file.php?file='.$file['id'].'&action=download"><img src="images/file_types/'.$pathParts['extension'].'.png" style="vertical-align:middle" />'.basename($file['name']).'</a>';
					} else {
						$fields['data'] = '<a href = "view_file.php?file='.$file['id'].'&action=download">'.basename($file['name']).'</a>';
					}
					$unit = $currentContent -> insertNode($fields);
				}
		 	}
		}

		$message = _FILESIMPORTEDSUCCESSFULLY;
		$message_type = 'success';
	} catch (Exception $e) {
		handleNormalFlowExceptions($e);
	}
}


$form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
$form -> setRequiredNote(_REQUIREDNOTE);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
$renderer->setRequiredTemplate(
       '{$html}{if $required}
            &nbsp;<span class = "formRequired">*</span>
        {/if}');

$renderer->setErrorTemplate(
       '{$html}{if $error}
            <span class = "formError">{$error}</span>
        {/if}');
$form -> accept($renderer);

$smarty -> assign("T_MAX_FILE_SIZE", FileSystemTree :: getUploadMaxSize());
$smarty -> assign('T_ENTITY_FORM', $renderer -> toArray());

#cpp#endif
