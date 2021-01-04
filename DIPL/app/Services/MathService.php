<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 16.12.2020
 * Time: 10:11
 */

namespace App\Services;

use Nette;
class MathService
{


    public function __construct()
    {

    }

    public function historyRating($d1, $d2){
        $c = ($d1 - $d2)/86400;
        if ($c < 1){
            $day = 1;
        }elseif ($c < 7){
            $day = 0.7;
        }elseif($c < 30){
            $day = 0.5;
        }elseif ($c < 90){
            $day = 0.2;
        }else{
            $day = 0;
        }
        return $day;
    }


    public function access2Rating($vote){
        if ($vote >= 5 && $vote <= 10){
            $percent = 10;
        }elseif ($vote <= 20){
            $percent = 30;
        }elseif ($vote <= 30){
            $percent = 50;
        }elseif ($vote > 30){
            $percent = 70;
        }
        return $percent;
    }
}