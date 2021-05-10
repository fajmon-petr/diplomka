<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 16.4.2021
 * Time: 14:24
 */

namespace App\Services;

use Nette;
use Tracy;

class Lflc
{


    public function lflc($array){
//        dump(__DIR__.'/lflc/lflc-run.bat');
        $vystup = 0;
        $inputFile = __DIR__."/lflc/data.txt";
        $fh = fopen($inputFile, 'w') or die("nelze otevrit vstupni soubor");
        $hlavicka_promenne = 'V1'."\t".'V2'."\t".'V3'."\r\n";
        //Zapsani dat do vstupniho souboru
        fwrite($fh, $hlavicka_promenne);
        foreach ($array as $v){
            $v1_text = str_replace('.', ',',$v['rating'])."\t";
            $v2_text = str_replace('.', ',',$v['similarity'])."\t";
            $v3_text = str_replace('.', ',',$v['koeficient'])."\t\n";
            fwrite($fh, $v1_text);
            fwrite($fh, $v2_text);
            fwrite($fh, $v3_text);
        }
        fclose($fh);
        //Spusteni LFLC
//        exec(__DIR__.'/lflc/lflc-run.bat', $o, $v);
//        dump(exec(__DIR__.'/lflc/lflc-run.bat', $o, $v));
//        dump(escapeshellcmd(sprintf('cd %s && /lflc/lflc-run.bat', __DIR__)));
//        dump($o, $v);
//        exec(escapeshellcmd(__DIR__ . '/lflc/lflc-run.bat'), $out, $var);
//        dump($out, $var);
//        exec(escapeshellcmd(sprintf('cd %s && /lflc/lflc-run.bat', __DIR__)), $out, $var);
        $dir = __DIR__.'\lflc';
        exec(sprintf('cd %s && lflc-run.bat', $dir));
//        exec('cd ' . __DIR__.'\lflc && D:\xampp\htdocs\DIPL\app\Services\lflc\hierarchic_base.exe -l -k D:\xampp\htdocs\DIPL\app\Services\lflc\test_db.knb -i D:\xampp\htdocs\DIPL\app\Services\lflc\data.txt -o D:\xampp\htdocs\DIPL\app\Services\lflc\output.txt');
//        exec(sprintf('cd %s && %s\hierarchic_base.exe -l -k %s\test_db.knb -i %s\data.txt -o %s\output.txt', $dir, $dir, $dir, $dir, $dir));
//        dump($out, $var);
//        Tracy\Debugger::$maxLength = null;
//        dump($out, $var);

//        dump(escapeshellcmd(sprintf('%s/lflc-run.bat', $this->binDir)));
//        exec(escapeshellcmd(sprintf('%s/lflc-run.bat', $this->binDir)), $output, $status);
//        dump($output, $status);

        $outputFile = __DIR__."/lflc/output.txt";
        $fh = fopen($outputFile, 'r');
        $output = fread($fh, filesize($outputFile));
        fclose($fh);
        //Nacteni vystupu
        $values = explode("\t",$output);
        $test = [];
        for ($i = 6; $i <= count($values);){
            $test[] = $values[$i];
            $i += 3;
        }
        $vystup_array = [];
        foreach($test as $t){
            $vystup_array[] = explode("\n",$t);
        }
        $vystup_val = [];
        foreach ($vystup_array as $ar) {
            $vystup_val[] = str_replace(",", ".", $ar[0]);
        }
//        $vystup_array = explode("\n",$values[6]);
//        $vystup_val = $vystup_array[0];
        //LFLC dava cislo ve forme O,5 musim upravit na 0.5
//        $vystup_val = str_replace(",",".",$vystup_val);
//        $vystup_val = str_replace("\r", "", $vystup_val );
//        $vystup_val = floatval($vystup_val);
        return $vystup_val;
    }

}