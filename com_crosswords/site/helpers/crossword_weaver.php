<?php
/**
 * @version		$Id: crossword_weaver.php 01 2012-03-23 11:37:09Z maverick $
 * @package		CoreJoomla.Crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die('Restricted access');

//
// Originally written in JavaScript by Micheal Johnson
// c. 2009 BigAtticHouse.com (Michael Johnson)
// MIT style license as long as you include these comment lines in your code... do with it as you will.
// Posted 11/19/2009 www.startup-something.com
//

class Crossword{
	
	var $_db = null;
	var $table = null;
	var $board = Array();
	var $board_width = 30;
	var $board_height = 30;
	var $maxwords = 20;

	var $across = Array();
	var $down = Array();
	var $wordlist = array();

	function Crossword($boardsize, $words){

		$this->board_width = $boardsize;
		$this->board_height = $boardsize;
		$this->wordlist = $words;
	}

	function clear_board(){

		$this->grid = Array();

		for($x=0;$x<$this->board_width;$x++){
				
			$this->grid[] = array();
				
			for($y=0;$y<$this->board_width;$y++){

				$this->grid[$x][] = ' ';
			}
		}
	}


	function get_matched_letters($word){

		$positions = array();

		for($x = 0; $x < $this->board_width; $x++){
				
			for($y = 0; $y < $this->board_width; $y++){

				$c = mb_strtoupper($this->grid[$x][$y], 'UTF-8');

				if (mb_strpos($word, $c, 0, 'UTF-8') !== false){
						
					$positions[] = array($c, $x, $y);  //'M',1,2
				}
			}
		}

		return $positions;
	}

	function is_previous_blank($x, $y, $dx, $dy, $word){

		$dx = $dx * -1;
		$dy = $dy * -1;

		$x = $x + $dx;
		$y = $y + $dy;

		if( !empty($this->grid[$x]) ){
				
			if ( $this->grid[$x][$y] == ' ' ){

				return true;
			} else {

				return false;
			}
		}
		return true;
	}


	function is_blank_next($x, $y, $dx, $dy, $word){

		$dx = $dx * (mb_strlen($word, 'UTF-8') + 1);
		$dy = $dy * (mb_strlen($word, 'UTF-8') + 1);

		$x = $x + $dx;
		$y = $y + $dy;

		if(!empty($this->grid[$x])){
				
			if ($this->grid[$x][$y] == ' '){

				return true;
			} else {

				return false;
			}
		}
		return true;
	}


	function score_path($x, $y, $dx, $dy, $word){
		$score = 0;
		$chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
		$word_length = count($chars);
		$blank = 0;

		$px = $x - $dx;
		$py = $y - $dy;

		if(!empty($this->grid[$px])){
				
			if ($px < 0 || $py < 0 || $this->grid[$px][$py] != ' '){

				return -1;
			}
		}


		for($i = 0; $i < $word_length; $i++){
				
			if( ($x < $this->board_width) && ($y < $this->board_height) && ($x > -1) && ($y > -1) ){

				if($this->grid[$x][$y] != ' '){
						
					if($this->grid[$x][$y] == $chars[$i]){

						$score+=1;
					} else {

						return -1;
					}
				} else {
						
					$blank += 1;
				}

				if($this->grid[$x][$y] != $chars[$i]){
						
					if($dx==1){

						$subscore = 0;

						if(!empty($this->grid[$x][$y-1])) {

							if($this->grid[$x][$y-1] == ' ')  {

								$subscore += 1;
							}
						} else  {
								
							$subscore += 1;
						}
							
						if(!empty($this->grid[$x][$y+1])) {

							if($this->grid[$x][$y+1] == ' ') {

								$subscore += 1;
							}
						} else  {
								
							$subscore += 1;
						}

						if ($subscore == 2) {
								
							$score += 1;
						} else {
								
							return -1;
						}
					}
					if($dy==1){

						$subscore = 0;

						if(!empty($this->grid[$x-1])) {
								
							if($this->grid[$x-1][$y] == ' ') {

								$subscore += 1;
							}
						} else  {
								
							$subscore += 1;
						}

						if(!empty($this->grid[$x+1])) {
								
							if($this->grid[$x+1][$y] == ' ') {

								$subscore += 1;
							}
						} else  {
								
							$subscore += 1;
						}
						if ($subscore == 2) {
								
							$score += 1;
						} else {
								
							return -1;
						}
					}
				}

			} else {

				return -1;
			}
			$x = $x + $dx;
			$y = $y + $dy;
		}

		if(!empty($this->grid[$x])){
				
			if ($x < 0 || $y < 0 || $x >= $this->board_width || $y >= $this->board_width || $this->grid[$x][$y] != ' '){

				return -1;
			}
		}


		if($blank == $word_length) {
				
			$score=0;
		}

		return $score;
	}


	function get_crossable_places($keyword){

		$locations = Array();
		$startpos = $this->get_matched_letters($keyword);
		$dx=0;
		$dy=0;
		$bestscore = 0;
		$bestdx = 0;
		$bestx=0;
		$bestdy = 0;
		$besty=0;

		for($x = 0; $x < $this->board_width; $x++){
				
			for($y = 0; $y < $this->board_width; $y++){

				$hscore = $this->score_path($x, $y, 1, 0, $keyword);
				$vscore = $this->score_path($x, $y, 0, 1, $keyword);

				// document.write($keyword+" ("+$x+","+$y+") H="+$hscore+"  V="+$vscore+" <br>");
				if($hscore > $bestscore){
						
					$bestscore = $hscore;
					$bestx = $x;
					$besty = $y;
					$bestdx = 1;
					$bestdy = 0;
				}

				if($vscore > $bestscore){
						
					$bestscore = $vscore;
					$bestx = $x;
					$besty = $y;
					$bestdx = 0;
					$bestdy = 1;
				}
			}
		}

		if($bestscore > 0){
				
			// echo $keyword." - ".$bestscore."(".$bestx.",".$besty.")-(".$bestdx.",".$bestdy.")<br>";
			$locations[] = array($bestscore, $bestx, $besty, $bestdx, $bestdy);
		}

		return $locations;
	}

	function place_word($x, $y, $dx, $dy, $word){

		$chars = preg_split('//u', $word->keyword, -1, PREG_SPLIT_NO_EMPTY);
		$word_length = count($chars);

		for($i=0; $i < $word_length; $i++){
				
			if(!empty($this->grid[$x])){

				$this->grid[$x][$y] = $chars[$i];
			}
				
			$x = $x + $dx;
			$y = $y + $dy;
		}

		if($dx == 1){
				
			$this->down[] = array($x, $y, $word);
		}

		if($dy==1){
				
			$this->across[] = array($x, $y, $word);
		}

		return true;
	}

	function is_path_clear($x, $y, $dx, $dy, $word){

		$word_length = mb_strlen($word, 'UTF-8');

		for($i = 0; $i < $word_length; $i++){
				
			if(!empty($this->grid[$x]) && $y > -1 && $y < $this->board_width){

				if ($this->grid[$x][$y] != ' '){
						
					return false;
				}

				if ($this->score_path($x, $y, $dx, $dy, $word) < 0) {
						
					return false;
				}
			} else {

				return false;
			}
				
			$x = $x + $dx;
			$y = $y + $dy;
		}

		return true;
	}

	function place_at_random($word){

		$trynum=0;
		$keeptrying=true;

		while($keeptrying){
				
			$x = rand(0, $this->board_width - 1);
			$y = rand(0, $this->board_width - 1);
				
			$hor_v = rand(0, $this->board_height-1);
				
			if($hor_v % 2 == 0){

				$dx=0;
				$dy=1;
			} else {

				$dx=1;
				$dy=0;
			}
				
			$keeptrying = (($trynum < $this->board_width * $this->board_height) && !$this->is_path_clear($x, $y, $dx, $dy, $word->keyword));
			$trynum++;
		} //  document.write($word+":random("+($trynum+1)+")<br>");

		if ($this->is_path_clear($x, $y, $dx, $dy, $word->keyword)){
				
			$this->place_word($x, $y, $dx, $dy, $word);
		}
	}

	function place_at_best_crossing($locations, $word){

		$x = $locations[0][1];
		$y = $locations[0][2];
		$dx = $locations[0][3];
		$dy = $locations[0][4];

		$this->place_word($x, $y, $dx, $dy, $word);
	}

	//1. Loops

	function build_crossword(){

		$this->clear_board();
		$unconnected=0;
		$xwordlist = $this->wordlist;
		$retry = Array();
		$retry2 = Array();

		while(count($xwordlist) > 0){
				
			$word = array_pop($xwordlist);
			$locations = $this->get_crossable_places($word->keyword);
				
			if(count($locations) > 0){

				// document.write($word+":best("+$locations.length+" results)<br>");
				$this->place_at_best_crossing($locations, $word);  //so Mia could cross both Mike and Ann..
			} else {

				if(in_array($word->keyword, $retry) === false){
						
					$retry[] = $word->keyword;
					$xwordlist[] = $word;
				} else {
					if(in_array($word->keyword, $retry2) === false){

						$retry2[] = $word->keyword;
						$xwordlist[] = $word;
					} else {

						$this->place_at_random($word);
					}
				}
			}
		}
	}


	function ScreenDumpCrossword(){

		echo "!<pre>";

		for($x = 0; $x < $this->board_width; $x++){
				
			for($y = 0; $y < $this->board_width; $y++){

				$c = $this->grid[$x][$y];
				echo $c;
			}
				
			echo $x."\r\n";
		}
		echo "</pre>";

		echo 'Down:<br>';

		for($i = 0; $i < count($this->across); $i++){
				
			echo ($i+1).'. ('.$this->across[$i].')<br>';
		}

		echo 'Across:<br>';
		for($i = 0; $i < count($this->down); $i++){
				
			echo ($i+1).'. ('.$this->down[$i].')<br>';
		}
	}

	function CrosswordTable(){

		echo '<table border=1 cellspacing=0 cellpadding=2>';

		for($x = 0; $x < $this->board_width; $x++){
				
			echo "<tr>";
			for($y = 0; $y < $this->board_width; $y++){

				$c = $this->grid[$x][$y];

				if($c == ' '){
						
					echo '<td style="background:#000">';
				} else {
						
					echo "<td>";
				}

				echo $c;
				echo "</td>";
			}
				
			echo "</tr>";
		}

		echo "</table>";
		echo 'Across:<br>';

		for($i = 0; $i < count($this->across); $i++){
				
			echo ($i+1).'. ('.$this->across[$i][2]->keyword.') x='.$this->across[$i][0].' | y='.$this->across[$i][1].'<br>';
		}

		echo 'Down:<br>';
		for($i = 0; $i < count($this->down); $i++){
				
			echo ($i+1).'. ('.$this->down[$i][2]->keyword.') x='.$this->down[$i][0].' | y='.$this->down[$i][1].'<br>';
		}
	}
}