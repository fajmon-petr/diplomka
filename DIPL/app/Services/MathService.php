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
            $day = 0.01;
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

    public function access4koeficient($count){
        if ($count > 20){
            $count = 20;
        }
        return ($count/20)/2;
    }

    public function access5koeficient($type, $histr){
            if (!in_array($type, $histr)){
                $koeficient = 0;
            }else{
                $koeficient = 0.5;
            }
        return $koeficient;
    }

    public function normalize($array){

        $min = min($array);
        $max = max($array);

        foreach ($array as $k => $value){
            $array[$k] = ($value - $min) / ($max - $min);
        }

        return $array;
    }

    public function normalizeTfIdf($array){

        $newArray = $this->normalize($array);

        foreach ($newArray as $k => $value){
            $newArray[$k] = $value * (1 - 0.5) + 0.5;
        }

        return $newArray;
    }
}