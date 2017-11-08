<?php

namespace App\Templates;
use Nette;
use Tracy\Debugger; 
use Nette\Utils\Html;

class Filters extends Nette\Object {		
		
	public function dynamicDate($year, $month = "", $day = "") {
        if ($month && $day) { return $day.'.'.$month.'.'.$year;	}
        elseif ($month) {     return $month.".".$year; }
		else { 				  return $year;	}
	}	
	
	public function dynamicDateSortable($year, $month = "", $day = "") {
		$helper_tag = Html::el('span class=sort-helper');
		$day_tag = Html::el('span class=day')->setText($day);
		$month_tag = Html::el('span class=month')->setText($month);
		
        if ($month && $day) {
	        if($day < 10) $day = "0".$day;
	        if($month < 10) $month = "0".$month;
        	$helper_tag->setHtml($year."x".$month."x".$day);
        	return $helper_tag . $day_tag . $month_tag . $year;
		}
        elseif ($month) {
	        if($month < 10) $month = "0".$month;
        	$helper_tag->setHtml($year.'x'.$month.'00');
            return $helper_tag . $month_tag . $year;
        }
        else {
        	$helper_tag->setHtml($year.'00x00');
            return $helper_tag . $year;
        }
	}	
	
	public function verseReadable($abbreviation, $chapter, $verse) {
		$string = $abbreviation;
		if($chapter != "") {
			$string .= substr($abbreviation, 0, 3) == "ŚB" ? "." : " ";
			$string .= $chapter;
			
			if($verse != "") {
				$string .= ".".$verse;
			}
		}

	    return $string;
	}

    public function decimalNumber($number) {
        if($number == NULL) {
            $empty_tag = Html::el('div class=number-empty')->setText(".");
            return $empty_tag;
        }

        $number_parts = explode(".", $number);
        $integer_tag = Html::el('div class=number-integer')->setText($number_parts[0]);
        $delimiter_tag = Html::el('div class=number-delimiter')->setText(".");

        if(isset($number_parts[1])) {
            $decimal_tag = Html::el('div class=number-decimal')->setText($number_parts[1]);
            return $integer_tag.$delimiter_tag.$decimal_tag;
        }
        else {
            return $integer_tag;
        }
    } 
    
    public function round($number) {
	    return round($number, 0);
    }

    public function padRightEx($input, $pad_length, $pad_string = "&nbsp;") {
        $string = str_pad($input, strlen($input) - mb_strlen($input, "UTF-8") + $pad_length, "~", STR_PAD_RIGHT);
        return str_replace('~', $pad_string, $string);
    }

    public function padLeftEx($input, $pad_length, $pad_string = "&nbsp;") {
        $string = str_pad($input, strlen($input) - mb_strlen($input, "UTF-8") + $pad_length, "~", STR_PAD_LEFT);
        return str_replace('~', $pad_string, $string);
    }    
}