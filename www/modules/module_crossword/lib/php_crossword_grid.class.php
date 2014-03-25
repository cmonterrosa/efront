<?php
// ----------------------------------------------------------------------------
// This file is part of PHP Crossword.
//
// PHP Crossword is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// PHP Crossword is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Foobar; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
// ----------------------------------------------------------------------------

/**
 * PHP Crossword Grid
 *
 * @package		PHP_Crossword 
 * @copyright	Laurynas Butkus, 2004
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version		0.2
 */


class PHP_Crossword_Grid
{
	var $rows;
	var $cols;
	var $cells 		= array();
	var $words 		= array();

	var $inum 		= 0; 
	var $maxinum 	= 0; 
	var $totwords 	= 0; 

	/**
	 * Constructor
	 * @param int $rows
	 * @param int $cols
	 */
	function PHP_Crossword_Grid($rows, $cols)
	{
		$this->rows = (int)$rows;
		$this->cols = (int)$cols;

		$this->__initCells();
	}

	/**
	 * Initialize cells (create celll objects)
	 * @private
	 */
	function __initCells()
	{
		for ($y = 0; $y < $this->rows; $y++)
		for ($x = 0; $x < $this->cols; $x++)
		$this->cells[$x][$y] =& new PHP_Crossword_Cell($x, $y);
	}

	/**
	 * Count words in the grid
	 * @return int 
	 */
	function countWords()
	{
		$this->totwords = count($this->words); // sandy addition
		return $this->totwords;
	}

	/**
	 * Get random word from the grid (not fully crossed)
	 * @return object word object
	 */
	function &getRandomWord()
	{
		$words = array();

		for ($i = 0; $i < count($this->words); $i++)
		if (!$this->words[$i]->isFullyCrossed())
		$words[] = $i;


		if (!count($words))
		return PC_WORDS_FULLY_CROSSED;

		$n = array_rand($words);
		$n = $words[$n];

		return $this->words[$n];
	}

	/**
	 * Place word
	 * @param string $word
	 * @param int $x 
	 * @param int $y
	 * @param int $axis 
	 */
	function placeWord($word, $x, $y, $axis)
	{
		
		$w =& new PHP_Crossword_Word($word, $axis, $this->cells[$x][$y]);

		++$this->inum; // sandy addition
		++$this->maxinum; // sandy addition

		$w->inum = $this->inum; // sandy addition

		$this->words[] =& $w;

		$cx = $x;
		$cy = $y;

		if ($axis == PC_AXIS_H)
		{
			$s = $x;
			$c =& $cx;
		}
		else
		{
			$s = $y;
			$c =& $cy;
		}

		// dump( "PLACING WORD: $cx x $cy - {$w->word}" );
//pr($word);		
//pr($s);
//pr(mb_strlen($word));

		for ($i = 0; $i < mb_strlen($word); $i++)
		{
			$c = $s + $i;
			$cell =& $this->cells[$cx][$cy];
$one_letter = mb_substr($w->word, $i, 1);

			$cell->setLetter($one_letter, $axis, $this);
			
			$w->cells[$i] =& $cell;
		}
//pr($this);exit;
		// disable cell before first cell
		$c = $s - 1;
		if ($c >= 0 )
		$this->cells[$cx][$cy]->setCanCross(PC_AXIS_BOTH, FALSE);

		$this->cells[$cx][$cy]->number = $w->inum; // sandy addition

		// disable cell after first cell
		$c = $s + mb_strlen($word);
		if (is_object($this->cells[$cx][$cy]))
		$this->cells[$cx][$cy]->setCanCross(PC_AXIS_BOTH, FALSE);

	}

	/**
	 * Check if it's possible to place the word
	 * @param string $word
	 * @param int $x
	 * @param int $y
	 * @param int $axis
	 * @return boolean
	 */
	function canPlaceWord($word, $x, $y, $axis)
	{
		for ($i = 0; $i < mb_strlen($word); $i++)
		{
			if ($axis == PC_AXIS_H )
			$cell =& $this->cells[$x+$i][$y];
			else
			$cell =& $this->cells[$x][$y+$i];

			if (!is_object($cell))
			{
				echo "ERROR!!! Word: $word, x=$x, y=$y, axis=$axis";
				echo $this->getHTML(1);
			}
$one_letter = mb_substr($word, $i, 1);
			if (!$cell->canSetLetter($one_letter, $axis))
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Get number of columns in the grid
	 * @return int
	 */
	function getCols()
	{
		return $this->cols;
	}

	/**
	 * Get number of rows in the grid
	 * @return int
	 */
	function getRows()
	{
		return $this->rows;
	}

	/**
	 * Get random position
	 * @param int $axis
	 * @param string $word
	 * @return int 
	 */
	function getRandomPos($axis, $word = NULL)
	{
		$n = $axis == PC_AXIS_H ? $this->cols : $this->rows;

		if (!is_null($word))
		$length = mb_strlen($word);

		if ($n == $length) return 0;

		return rand(0, $n-$length-1);
	}

	/**
	 * Get center position
	 * @param int $axis
	 * @param string $word
	 * @return int
	 */
	function getCenterPos($axis, $word = '')
	{
		$n = $axis == PC_AXIS_H ? $this->cols : $this->rows;
		$n-= mb_strlen($word);
		$n = floor($n / 2);
		return $n;
	}

	/**
	 * Get minimum starting cell on the axis
	 * @param object $cell crossing cell
	 * @param int $axis
	 * @return object cell
	 */
	function &getStartCell(&$cell, $axis )
	{
		$x = $cell->x;
		$y = $cell->y;

		if ($axis == PC_AXIS_H)
		$n =& $x;
		else
		$n =& $y;

		while ($n >= 0)
		{
			if (!$this->cells[$x][$y]->canCross($axis))
			break;

			$n--;

			if (isset($this->cells[$x][$y]->letter))
			{
				$n++;
				break;
			}
		}

		$n++;

		return $this->cells[$x][$y];
	}

	/**
	 * Get maximum ending cell on the axis
	 * @param object $cell crossing cell
	 * @param int $axis
	 * @return object cell
	 */
	function &getEndCell(&$cell, $axis)
	{
		$x = $cell->x;
		$y = $cell->y;

		if ($axis == PC_AXIS_H)
		{
			$n =& $x;
			$max = $this->getCols() - 1;
		}
		else
		{
			$n =& $y;
			$max = $this->getRows() - 1;
		}

		while ($n <= $max)
		{
			if (!$this->cells[$x][$y]->canCross($axis))
			break;

			$n++;

			if (isset($this->cells[$x][$y]->letter))
			{
				$n--;
				break;
			}
		}

		$n--;

		return $this->cells[$x][$y];
	}

	/**
	 * Get HTML (for debugging)
	 * @param array params
	 * @return string HTML
	 */
	function getHTML($answor)
	{
		//extract((array)$params);
		
		$html = "<table border=0 class='crossTable' align='center'>";

		for ($y = -1; $y < $this->rows; $y++)
		{
			$html.= "<tr align='center'>";

			for ($x = -1; $x < $this->cols; $x++)
			{
				if ($x > -1 && $y > -1)
				{
					switch ($this->cells[$x][$y]->getCanCrossAxis())
					{
						case PC_AXIS_H:
						$color = "yellow";
						break;

						case PC_AXIS_V:
						$color = "brown";
						break;

						case PC_AXIS_NONE:
						$color = "red";
						break;

						case PC_AXIS_BOTH:
						$color = "lightgreen";
						break;
					}
				}

				$class = isset($this->cells[$x][$y]->letter) ? 'cellLetter' : 'cellEmpty';

				if (!$colors) $color = "white";
				else $class = 'cellDebug';

				$html .= "\n";

				if (isset($this->cells[$x][$y]->number)) {
					//global $maxinum, $totwords, $wc, $fillflag, $cellflag; 
					$tempinum = $this->cells[$x][$y]->number;
					//$tempinum = $tempinum + 10 - $maxinum - $wc;
					//dump($tempinum);
					//$tempinum = $tempinum + 10 - $this->maxinum - $this->totwords;

					$html.= "<td class='cellNumber$cellflag' align=right valign=bottom><b>$tempinum</b></td>"; 
				}
				elseif ($y == -1)

				$html.= "<td bgcolor='{$color}' class='{$class}$cellflag'>&nbsp;</td>";
				elseif ($x == -1)

				$html.= "<td bgcolor='{$color}' class='{$class}$cellflag'>&nbsp;</td>";
				elseif (isset($this->cells[$x][$y]->letter))
				{
					if ($fillflag) {
						$letter=$this->cells[$x][$y]->letter;
					} else {
						$letter="&nbsp;";
					}
					
					$html.= "<td bgcolor='{$color}' class='{$class}$cellflag'>$letter</td>";

				}
				else
				$html.= "<td bgcolor='{$color}' class='{$class}$cellflag'>&nbsp;</td>";
			}
			$html.= "</tr>";
		}

		$html.= "</table>";
		
	 $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
	 $pdf -> SetCreator(PDF_CREATOR);
	 $pdf -> SetAuthor(PDF_AUTHOR);
	 //set margins
	 $pdf -> SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	 //set auto page breaks
	 $pdf -> SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	 $pdf -> SetHeaderMargin(PDF_MARGIN_HEADER);
	 $pdf -> SetFooterMargin(PDF_MARGIN_FOOTER);
	 $pdf -> setImageScale(PDF_IMAGE_SCALE_RATIO);
	
	//set some language-dependent strings
	$pdf->setLanguageArray($l);
	
	// ---------------------------------------------------------
	$pdf -> AliasNbPages();
	// set font
	$pdf->SetFont('FreeSerif', '', 10);
	
	// add a page
	$pdf -> AddPage();
	
	$ans = 1;
	foreach($answor[0] as $row1){ 
		    if($ans<=2){
    		$pdf -> Cell(100, 10, $row1, 0, 1, L, 0);
		    }
    		$ans++;
    	}
	
	for ($y = -1; $y < $this->rows; $y++)
		{
			//$html.= "<tr align='center'>";

			for ($x = -1; $x < $this->cols; $x++)
			{
				if ($x > -1 && $y > -1)
				{
					switch ($this->cells[$x][$y]->getCanCrossAxis())
					{
						case PC_AXIS_H:
						$color = "yellow";
						break;

						case PC_AXIS_V:
						$color = "brown";
						break;

						case PC_AXIS_NONE:
						$color = "red";
						break;

						case PC_AXIS_BOTH:
						$color = "lightgreen";
						break;
					}
				}

				$class = isset($this->cells[$x][$y]->letter) ? 'cellLetter' : 'cellEmpty';

				if (!$colors) $color = "white";
				else $class = 'cellDebug';

				//$html .= "\n";

				if (isset($this->cells[$x][$y]->number)) {
					$tempinum = $this->cells[$x][$y]->number; 
					$pdf -> Cell(5, 5, "$tempinum", 0, 0, L, 0);
				}
				elseif ($y == -1){
				if("{$class}$cellflag"=='cellLetter') {
				$pdf -> Cell(5, 5, "", 1, 0, L, 0);
				}else{
				$pdf -> Cell(5, 5, "", 0, 0, L, 0);	
				}
				}
				elseif ($x == -1){
				if("{$class}$cellflag"=='cellLetter') {
				$pdf -> Cell(5, 5, "", 1, 0, L, 0);
				}else{
				$pdf -> Cell(5, 5, "", 0, 0, L, 0);	
				}
				}
				elseif (isset($this->cells[$x][$y]->letter))
				{
					if ($fillflag) {
						$letter=$this->cells[$x][$y]->letter;
					} else {
						$letter="";
					}
					$pdf -> Cell(5, 5, "$letter", 1, 0, L, 0);
				}
				else{
				if("{$class}$cellflag"=='cellLetter') {
				$pdf -> Cell(5, 5, "", 1, 0, L, 0);
				}else{
				$pdf -> Cell(5, 5, "", 0, 0, L, 0);	
				}
				}
				
			}
			//$html.= "</tr>";
			$pdf -> Cell(5, 5, "", 0, 1, L, 0);
		}
		
		$pdf->SetFont('FreeSerif', '', 10);
	
	// add a page
	$pdf->AddPage();
	   
	    
		foreach($answor[0] as $row1){ 

    		$pdf -> MultiCell(0, 20, $row1, 0, L, false, 1, '', '', true, 0, true);
 //   		$pdf -> Cell(5, 5, '', 0, 1, L, 0);
    	}
//exit;		
	// set font
	$pdf->SetFont('FreeSerif', '', 10);
	
	// add a page
	$pdf->AddPage();
	
	$ans = 1;
	foreach($answor[0] as $row1){ 
		    if($ans<=2){
    		$pdf -> Cell(100, 10, $row1, 0, 1, L, 0);
		    }
    		$ans++;
    	}
	for ($y = -1; $y < $this->rows; $y++)
		{
			//$html.= "<tr align='center'>";

			for ($x = -1; $x < $this->cols; $x++)
			{
				if ($x > -1 && $y > -1)
				{
					switch ($this->cells[$x][$y]->getCanCrossAxis())
					{
						case PC_AXIS_H:
						$color = "yellow";
						break;

						case PC_AXIS_V:
						$color = "brown";
						break;

						case PC_AXIS_NONE:
						$color = "red";
						break;

						case PC_AXIS_BOTH:
						$color = "lightgreen";
						break;
					}
				}

				$class = isset($this->cells[$x][$y]->letter) ? 'cellLetter' : 'cellEmpty';

				if (!$colors) $color = "white";
				else $class = 'cellDebug';

				//$html .= "\n";

				if (isset($this->cells[$x][$y]->number)) {
					$tempinum = $this->cells[$x][$y]->number; 
					$pdf -> Cell(5, 5, "$tempinum", 0, 0, L, 0);
				}
				elseif ($y == -1){
				if("{$class}$cellflag"=='cellLetter') {
				$pdf -> Cell(5, 5, "", 1, 0, L, 0);
				}else{
				$pdf -> Cell(5, 5, "", 0, 0, L, 0);	
				}
				}
				elseif ($x == -1){
				if("{$class}$cellflag"=='cellLetter') {
				$pdf -> Cell(5, 5, "", 1, 0, L, 0);
				}else{
				$pdf -> Cell(5, 5, "", 0, 0, L, 0);	
				}
				}
				elseif (isset($this->cells[$x][$y]->letter))
				{
					if ($fillflag) {
						$letter=$this->cells[$x][$y]->letter;
					} else {
						$letter=$this->cells[$x][$y]->letter;
					}
					$pdf -> Cell(5, 5, "$letter", 1, 0, L, 0);
				}
				else{
				if("{$class}$cellflag"=='cellLetter') {
				$pdf -> Cell(5, 5, "", 1, 0, L, 0);
				}else{
				$pdf -> Cell(5, 5, "", 0, 0, L, 0);	
				}
				}
				
			}
			//$html.= "</tr>";
			$pdf -> Cell(5, 5, "", 0, 1, L, 0);
		}
	$pdf->lastPage();
	$pdf->Output($answor[0][0].' - '.$answor[0][1].' Crossword.pdf', 'I');
	return $pdf;
	}
}
?>
