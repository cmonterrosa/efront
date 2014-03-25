<?php
$path = "../../../../../libraries/";
require_once $path."phplivedocx_config.php";
require_once dirname(__FILE__) . '/../../common.php';
require_once dirname(__FILE__) . '/../../Converter.php';

// -----------------------------------------------------------------------------

define('PATH_BASE', dirname(__FILE__) );

if (isset($_GET['filename'])){
	$fileName = $_GET['filename'];
}
$inputFilename = PATH_BASE . DIRECTORY_SEPARATOR . $fileName.'.rtf';  // convert this file
$outputFormat  = 'pdf';                                             // into this format

//print_r($inputFilename);
// -----------------------------------------------------------------------------
$outputFilename = Converter::getFilename($inputFilename, $outputFormat);
//print_r($outputFilename);
//printf('Converting %s to %s... ', basename($inputFilename), basename($outputFilename));

$convertedDocument = Converter::convert($inputFilename, $outputFormat);
if (false !== $convertedDocument) {
    file_put_contents($outputFilename, $convertedDocument);
    print("true");
} else {
    print("false");
}

// -----------------------------------------------------------------------------
