<?php

// This file cannot be called directly, only included.
if(str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']){
	exit;
}

class XMLExport{

	public $template = '';

	private $active_certificate; /* indicates the active "page" meaning the active <certificate> xml tag */
	private $current_page = 0;
	private $number_of_pages;

	function __construct($xml){
		$this->template = simplexml_load_string($xml);
		$this->number_of_pages = count($this->template->certificate);
		$this->active_certificate = $this->template->certificate[$this->current_page]; 
	}

	public function getActiveCertificate() {
		return $this->active_certificate;
	}

	public function getNumberOfPages() {
		return $this->number_of_pages;
	}

	public function getNextPage() {
		$this->current_page = $this->current_page + 1;
		$this->active_certificate = $this->template->certificate[$this->current_page];
	}

	public function getCreator(){

		$creator = $this->active_certificate->creator;

		if($creator)
			return $creator['name'];
		else
			return 'eFront';
	}

	public function getAuthor(){
		
		$author = $this->active_certificate->author;

		if($author)
			return $author['name'];
		else
			return 'LMS';
	}

	public function getSubject($studentName){

		$subject = $this->active_certificate->subject;

		if($subject)
			return $subject['name'];
		else
			return 'Certificate for '.$studentName;
	}

	public function getKeywords(){
		
		$keywords = $this->active_certificate->keywords;

		if($keywords)
			return $keywords['name'];
		else
			return '';
	}

	public function getOrientation(){
		
		$orientation = $this->active_certificate->orientation;

		if($orientation){

			if($orientation['name'] == 'portrait')
				return 'P';

			else if($orientation['name'] == 'landscape')
				return 'L';
			else
				return 'L';
		}
		else
			return 'L';
	}

	public function setBackground($pdf){
		
		$background = $this->active_certificate->background;

		if($background){

			$start = substr($background['file'], 0, 7);

			if($start == 'http://')
				$backgroundUrl = $background['file'];
			else{
				//$backgroundUrl = G_SERVERNAME.'themes/default/images/certificate_logos/'.$background['file'];
				$backgroundFile = new EfrontFile(G_DEFAULTIMAGESPATH."certificate_logos/".$background['file']);
				$backgroundUrl = $backgroundFile['path'];
			}

			$pdf->setMargins(0);
			$pdf->SetHeaderData($backgroundUrl);
		}
		else{
			$pdf->setPrintHeader(false);
		}
	}

	public function drawLines($pdf){

		foreach($this->active_certificate->lines->line as $line)
			$this->drawLine($pdf, $line['x1'], $line['y1'], $line['x2'], $line['y2'], $line['color'], $line['thickness']);
	}

	public function showLabels($pdf){

		foreach($this->active_certificate->labels->label as $label)
			
			if($label['align']) {
				$this->showLabelAligned($pdf, $label['text'], $label['font'], $label['weight'], $label['size'], $label['color'], $label['x'], $label['y'], $label['align']);
			} else {
				$this->showLabel($pdf, $label['text'], $label['font'], $label['weight'], $label['size'], $label['color'], $label['x'], $label['y']);
			}
	}

	public function showImages($pdf){

		foreach($this->active_certificate->images->image as $img){

			$start = substr($img['file'], 0, 7);

			if($start == 'http://')
				$imgUrl = $img['file'];
			else{
				//$imgUrl = G_SERVERNAME.'themes/default/images/certificate_logos/'.$img['file'];
				try {
					$imgFile = new EfrontFile(G_DEFAULTIMAGESPATH."certificate_logos/".$img['file']);
					$imgUrl = $imgFile['path'];
				} catch (Exception $e) {
					//simply bypass image
				}
			}

			$this->showImage($pdf, $imgUrl, $img['x'], $img['y']);
		}
	}

	public function showLogo($pdf){

		$logo = $this->active_certificate->logo;

		if($logo){

			$start = substr($logo['file'], 0, 7);

			if($start == 'http://')
				$logoUrl = $logo['file'];
			else{
				/*try{
					try{
						$configuration = EfrontConfiguration::getValues();
						$logoFile = new EfrontFile($configuration['logo']);
						$logoUrl = G_SERVERNAME.'themes/default/images/logo/'.$logoFile['physical_name'];
					}
					catch(EfrontFileException $e){
						$currentTheme = new themes(G_CURRENTTHEME);
						$logoFile = new EfrontFile($currentTheme->options['logo']);
						$logoUrl = G_SERVERNAME.'themes/default/images/'.$logoFile['physical_name'];
					}
				}
				catch(EfrontFileException $e){
					$logoUrl = G_SERVERNAME.'themes/default/images/logo.png';
				}*/

				$logoFile = EfrontSystem::getSystemLogo();
				$logoUrl = $logoFile['path'];
			}

			$this->showImage($pdf, $logoUrl, $logo['x'], $logo['y']);
		}
	}

	public function showOrganization($pdf){

		$org = $this->active_certificate->organization;

		if($org)
			$this->showLabel($pdf, $org['text'], $org['font'], $org['weight'], $org['size'], $org['color'], $org['x'], $org['y']);
	}

	public function showDate($pdf, $date){

		$date_ = $this->active_certificate->date;

		if($date_)
			$this->showLabel($pdf, $date, $date_['font'], $date_['weight'], $date_['size'], $date_['color'], $date_['x'], $date_['y']);
	}
	
	public function showExpireDate($pdf, $expireDate){

		$date_ = $this->active_certificate->expire;

		if($date_)
			$this->showLabel($pdf, $expireDate, $date_['font'], $date_['weight'], $date_['size'], $date_['color'], $date_['x'], $date_['y']);
	}
	
	public function showCustomOne($pdf, $custom){

		$custom_ = $this->active_certificate->custom1;

		if($custom_) {
			if($custom_['align']) {	
				$this->showLabelAligned($pdf, $custom, $custom_['font'], $custom_['weight'], $custom_['size'], $custom_['color'], $custom_['x'], $custom_['y'],$custom_['align']);
			} else {
				$this->showLabel($pdf, $custom, $custom_['font'], $custom_['weight'], $custom_['size'], $custom_['color'], $custom_['x'], $custom_['y']);
			}
		}
	}
	
	public function showCustomTwo($pdf, $custom){

		$custom_ = $this->active_certificate->custom2;

		if($custom_) {
			if($custom_['align']) {	
				$this->showLabelAligned($pdf, $custom, $custom_['font'], $custom_['weight'], $custom_['size'], $custom_['color'], $custom_['x'], $custom_['y'],$custom_['align']);
			} else {
				$this->showLabel($pdf, $custom, $custom_['font'], $custom_['weight'], $custom_['size'], $custom_['color'], $custom_['x'], $custom_['y']);
			}
		}
	}
	public function showCustomThree($pdf, $custom){

		$custom_ = $this->active_certificate->custom3;

		if($custom_) {
			if($custom_['align']) {	
				$this->showLabelAligned($pdf, $custom, $custom_['font'], $custom_['weight'], $custom_['size'], $custom_['color'], $custom_['x'], $custom_['y'],$custom_['align']);
			} else {
				$this->showLabel($pdf, $custom, $custom_['font'], $custom_['weight'], $custom_['size'], $custom_['color'], $custom_['x'], $custom_['y']);
			}
		}
	}
	
	public function showSerialNumber($pdf, $serial){

		$serial_ = $this->active_certificate->serial;

		if($serial_)
			$this->showLabel($pdf, $serial, $serial_['font'], $serial_['weight'], $serial_['size'], $serial_['color'],
															$serial_['x'], $serial_['y']);
	}

	public function showStudentName($pdf, $studentName){

		$student = $this->active_certificate->student;

		if($student){

			if($student['align'])
				$this->showLabelAligned($pdf, $studentName, $student['font'], $student['weight'], $student['size'], $student['color'],
													$student['x'], $student['y'], $student['align']);
			else
				$this->showLabel($pdf, $studentName, $student['font'], $student['weight'], $student['size'],
													$student['color'], $student['x'], $student['y']);
		}
	}

	public function showCourseName($pdf, $courseName){

		$course = $this->active_certificate->course;

		if($course){
			
			if($course['align']) {
				$this->showLabelAligned($pdf, $courseName, $course['font'], $course['weight'], $course['size'], $course['color'],
													$course['x'], $course['y'], $course['align']);
			} else {
				$this->showLabel($pdf, $courseName, $course['font'], $course['weight'], $course['size'],
													$course['color'], $course['x'], $course['y']);
			}
		}
	}

	public function showGrade($pdf, $courseGrade){

		$grade = $this->active_certificate->grade;

		if($grade)
			$this->showLabel($pdf, $courseGrade, $grade['font'], $grade['weight'], $grade['size'], $grade['color'], $grade['x'], $grade['y']);
	}
/*
	private function showLabelAligned($p, $txt, $font, $font_weight, $font_size, $color, $x, $y, $a=''){

		$cell = '';
		$align = 'C';
		$fw = '';

		$align = (($a == 'Center' || $a == 'center') ? 'C' : (($a == 'Left' || $a == 'left') ? 'L' : ($a == 'Right' || $a == 'right') ? 'R' : ''));
		$rgb = $this->setColor($color);
		if($font_weight != ''){
			switch($font_weight){
		
				case($font_weight == 'Bold' || $font_weight == 'bold' || $font_weight == 'BOLD'): $fw = 'B'; break;
				case($font_weight == 'Italic' || $font_weight == 'italic' || $font_weight == 'ITALIC'): $fw = 'I'; break;
				case($font_weight == 'Bold|Italic' || $font_weight == 'bold|italic' || $font_weight == 'BOLD|ITALIC'): $fw = 'BI'; break;
			}
		}
		$p->SetFont('');
		$p->SetFont($font, $fw, floatval($font_size));
		$p->SetY($y);
		$p->SetTextColor($rgb['r'], $rgb['g'], $rgb['b']);
		$p->Cell(0, 13, $txt, 0, 0, $align);
	}
*/
	//Replaced old showLabelAligned() with this one, that uses MultiCell() which supports text wrapping
	private function showLabelAligned($p, $txt, $font, $font_weight, $font_size, $color, $x, $y, $a=''){
	
		$cell = '';
		$align = 'C';
		$fw = '';
	
		$align = (($a == 'Center' || $a == 'center') ? 'C' : (($a == 'Left' || $a == 'left') ? 'L' : ($a == 'Right' || $a == 'right') ? 'R' : ''));
		$rgb = $this->setColor($color);
		if($font_weight != ''){
			switch($font_weight){
	
				case($font_weight == 'Bold' || $font_weight == 'bold' || $font_weight == 'BOLD'): $fw = 'B'; break;
				case($font_weight == 'Italic' || $font_weight == 'italic' || $font_weight == 'ITALIC'): $fw = 'I'; break;
				case($font_weight == 'Bold|Italic' || $font_weight == 'bold|italic' || $font_weight == 'BOLD|ITALIC'): $fw = 'BI'; break;
			}
		}
		$p->SetFont('');
				
		$p->SetFont($font, $fw, floatval($font_size));
		$p->SetY($y);
		$p->SetTextColor($rgb['r'], $rgb['g'], $rgb['b']);
		$p->MultiCell(0, 0, $txt, 0, $align, false, 1, $x, $y, true, 0, false, true, 0, 'T', false);
	}
	
	private function showLabel($p, $txt, $font, $font_weight, $font_size, $color, $x, $y){

		$fw = '';
	
		if($font_weight != ''){

			switch($font_weight){

				case($font_weight == 'Bold' || $font_weight == 'bold' || $font_weight == 'BOLD'): $fw = 'B'; break;
				case($font_weight == 'Italic' || $font_weight == 'italic' || $font_weight == 'ITALIC'): $fw = 'I'; break;
				case($font_weight == 'Bold|Italic' || $font_weight == 'bold|italic' || $font_weight == 'BOLD|ITALIC'): $fw = 'BI'; break;
			}
		}

		$rgb = $this->setColor($color);
		$p->SetTextColor($rgb['r'], $rgb['g'], $rgb['b']);
		$p->SetFont($font, $fw, floatval($font_size));
		$p->Text($x, $y, $txt);
	}
	
	private function showLabel2($p, $txt, $font, $font_weight, $font_size, $color, $x, $y){
	
		$fw = '';
	
		if($font_weight != ''){
	
			switch($font_weight){
	
				case($font_weight == 'Bold' || $font_weight == 'bold' || $font_weight == 'BOLD'): $fw = 'B'; break;
				case($font_weight == 'Italic' || $font_weight == 'italic' || $font_weight == 'ITALIC'): $fw = 'I'; break;
				case($font_weight == 'Bold|Italic' || $font_weight == 'bold|italic' || $font_weight == 'BOLD|ITALIC'): $fw = 'BI'; break;
			}
		}
	
		$rgb = $this->setColor($color);
		$p->SetTextColor($rgb['r'], $rgb['g'], $rgb['b']);
		$p->SetFont($font, $fw, floatval($font_size));
		$p->MultiCell($x, $y, $txt);
	}

	private function showImage($p, $file, $x, $y){

		$p->Image($file, $x, $y, 0, 0);
	}

	private function drawLine($p, $x1, $y1, $x2, $y2, $color, $width){

		$rgb = $this->setColor($color);

		$p->SetDrawColor($rgb['r'], $rgb['g'], $rgb['b']);
		$p->SetLineWidth($width);
		$p->Line($x1, $y1, $x2, $y2);
	}

	private function setColor($color){

		$rgb = array('r' => 0, 'g' => 0, 'b' => 0);

		try{		
			if(strpos($color, '#') >= 0){
			
				$rgb = $this->hex2rgb($color);
			}
			else if(strrpos($color, ",")){
			
				$color_ = explode(",", $color);
				$rgb['r'] = $color_[0];
				$rgb['g'] = $color_[1];
				$rgb['b'] = $color_[2];
			}
		}
		catch(Exception $e){;}

		return $rgb;
	}

	private function hex2rgb($hex){

		$color = str_replace('#', '', $hex);
		$rgb = array('r' => hexdec(substr($color, 0, 2)), 'g' => hexdec(substr($color, 2, 2)), 'b' => hexdec(substr($color, 4, 2)));

		return $rgb;
	}
}
