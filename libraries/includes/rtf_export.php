<?php
//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}


if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] != 'change') {
		eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
		exit;
	}
	if (eF_checkParameter($_GET['course'], 'id') ) {
		if (eF_checkParameter($_GET['user'], 'login')) {
			$result = eF_getTableData("users_to_courses", "*", "users_LOGIN = '".$_GET['user']."' and courses_ID = '".$_GET['course']."' limit 1");
		} else {
			$result = array();
		}
		
		if (sizeof($result) == 1 || isset($_GET['preview'])) {
			$course = new EfrontCourse($_GET['course']);
			if (!isset($_GET['preview'])){
				$certificate_tpl_id_rtf = $course -> options['certificate_tpl_id_rtf'];
				if ($certificate_tpl_id_rtf <= 0) {
					$cfile = new EfrontFile(G_CERTIFICATETEMPLATEPATH."certificate1.rtf");
				} else {
					$cfile = new EfrontFile($certificate_tpl_id_rtf);
				}
				$template_data = file_get_contents($cfile['path']);
				$issued_data = unserialize($result[0]['issued_certificate']);
				$certificate = $template_data;
				if (sizeof($issued_data) > 1){
					$certificate   = $template_data;
					$certificate   = str_replace("#organization#", utf8ToUnicode($issued_data['organization']), $certificate);
					$certificate   = str_replace("#user_name#", utf8ToUnicode($issued_data['user_name']), $certificate);
					$certificate   = str_replace("#user_surname#", utf8ToUnicode($issued_data['user_surname']), $certificate);
					$certificate   = str_replace("#course_name#", utf8ToUnicode($issued_data['course_name']), $certificate);
					$certificate   = str_replace("#grade#", utf8ToUnicode($issued_data['grade']), $certificate);
					if (eF_checkParameter($issued_data['date'], 'timestamp')) {
						$issued_data['date']  = formatTimestamp($issued_data['date']);
					}
					$certificate   = str_replace("#date#", utf8ToUnicode($issued_data['date']), $certificate);
					$certificate   = str_replace("#serial_number#", utf8ToUnicode($issued_data['serial_number']), $certificate);
				}

			} else {
				$certificateDirectory = G_CERTIFICATETEMPLATEPATH;
				$selectedCertificate  = $_GET['certificate_tpl'];
				$certificate          = file_get_contents($certificateDirectory.$selectedCertificate);
			}
			$filenameRtf = "certificate_".$_GET['user'].".rtf";

			$webserver         = explode(' ',$_SERVER['SERVER_SOFTWARE']);      //GET Server information from $_SERVER
			$webserver_type    = explode('/', $webserver[0]);

			$filenamePdf = G_ROOTPATH."www/phplivedocx/samples/mail-merge/convert/certificate_".$_GET['user'].".pdf";
			$filenameRtf = G_ROOTPATH."www/phplivedocx/samples/mail-merge/convert/certificate_".$_GET['user'].".rtf";
			file_put_contents(G_ROOTPATH."www/phplivedocx/samples/mail-merge/convert/certificate_".$_GET['user'].".rtf", $certificate);

			if (stristr($webserver_type[0], "IIS") === false) {  //because of note here http://php.net/manual/en/function.file.php
				$retValues = file(G_SERVERNAME."phplivedocx/samples/mail-merge/convert/convert-document.php?filename=certificate_".$_GET['user']);
			} else {
				$retValues[0] == "false";
			}

			if ($retValues[0] == "true") {
				//file_put_contents($filenamePdf);
				$file = new EfrontFile($filenamePdf);
				$file -> sendFile();
			} else {
				//file_put_contents($filenameRtf);
				$file = new EfrontFile($filenameRtf);
				$file -> sendFile();
			}
		}
	}
} #cpp#endif

