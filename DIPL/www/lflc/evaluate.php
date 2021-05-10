<?php

function lflc($v1, $v2, $v3){

    $vystup = 0;
    $inputFile = "data.txt";
    $fh = fopen($inputFile, 'w') or die("nelze otevrit vstupni soubor");
    $hlavicka_promenne = 'V1'."\t".'V2'."\t".'V3'."\r\n";
    //Zapsani dat do vstupniho souboru
    fwrite($fh, $hlavicka_promenne);
    $v1_text = $v1."\t";
    $v2_text = $v2."\t";
    $v3_text = $v3."\t";
    fwrite($fh, $v1_text);
    fwrite($fh, $v2_text);
    fwrite($fh, $v3_text);
    fclose($fh);
    //Spusteni LFLC
    exec("lflc-run.bat");
    $outputFile = "output.txt";
    $fh = fopen($outputFile, 'r');
    $output = fread($fh, filesize($outputFile));
    fclose($fh);
    //Nacteni vystupu
    $values = explode("\t",$output);
    $vystup_array = explode("\n",$values[6]);
    $vystup_val = $vystup_array[0];
    //LFLC dava cislo ve forme O,5 musim upravit na 0.5
    $vystup_val = str_replace(",",".",$vystup_val);
    var_dump($vystup_val);
}

lflc('4', '0,5', '0,7');
lflc('3,6', '0,7', '1,2');




?>


