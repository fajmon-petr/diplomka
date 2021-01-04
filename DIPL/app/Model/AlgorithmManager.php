<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 29.10.2020
 * Time: 10:25
 */

namespace App\Model;
use http\QueryString;
use Nette;

class AlgorithmManager
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     *
     * česká stopwords
     * https://countwordsfree.com/stopwords/czech/json
     * @param $term
     * @return bool
     */

    public function stopwords($term){

        $stopwords = [
            "ačkoli","ahoj","ale","anebo","ano","asi","aspoň","během","bez","beze","blízko","bohužel","brzo","bude",
            "budeme","budeš","budete","budou","budu","byl","byla","byli","bylo","byly","bys","čau","chce","chceme",
            "chceš","chcete","chci","chtějí","chtít","chut\u0027","chuti","co","čtrnáct","čtyři","dál","dále","daleko",
            "děkovat","děkujeme","děkuji","den","deset","devatenáct","devět","do","dobrý","docela","dva","dvacet",
            "dvanáct","dvě","hodně","já","jak","jde","je","jeden","jedenáct","jedna","jedno","jednou","jedou","jeho",
            "její","jejich","jemu","jen","jenom","ještě","jestli","jestliže","jí","jich","jím","jimi","jinak","jsem",
            "jsi","jsme","jsou","jste","kam","kde","kdo","kdy","když","ke","kolik","kromě","která","které","kteří",
            "který","kvůli","má","mají","málo","mám","máme","máš","máte","mé","mě","mezi","mí","mít","mně","mnou","moc",
            "mohl","mohou","moje","moji","možná","můj","musí","může","my","na","nad","nade","nám","námi","naproti","nás"
            ,"náš","naše","naši","ne","ně","nebo","nebyl","nebyla","nebyli","nebyly","něco","nedělá","nedělají","nedělám"
            ,"neděláme","neděláš","neděláte","nějak","nejsi","někde","někdo","nemají","nemáme","nemáte","neměl","němu",
            "není","nestačí","nevadí","než","nic","nich","ním","nimi","nula","od","ode","on","ona","oni","ono","ony",
            "osm","osmnáct","pak","patnáct","pět","po","pořád","potom","pozdě","před","přes","přese","pro","proč",
            "prosím","prostě","proti","protože","rovně","se","sedm","sedmnáct","šest","šestnáct","skoro","smějí",
            "smí","snad","spolu","sta","sté","sto","ta","tady","tak","takhle","taky","tam","tamhle","tamhleto",
            "tamto","tě","tebe","tebou","ted\u0027","tedy","ten","ti","tisíc","tisíce","to","tobě","tohle","toto",
            "třeba","tři","třináct","trošku","tvá","tvé","tvoje","tvůj","ty","určitě","už","vám","vámi","vás","váš",
            "vaše","vaši","ve","večer","vedle","vlastně","všechno","všichni","vůbec","vy","vždy","za","zač","zatímco",
            "ze","že","aby","aj","ani","az","budem","budes","by","byt","ci","clanek","clanku","clanky","coz","cz",
            "dalsi","design","dnes","email","ho","jako","jej","jeji","jeste","ji","jine","jiz","jses","kdyz","ktera",
            "ktere","kteri","kterou","ktery","ma","mate","mi","mit","muj","muze","nam","napiste","nas","nasi","nejsou",
            "neni","nez","nove","novy","pod","podle","pokud","pouze","prave","pred","pres","pri","proc","proto",
            "protoze","prvni","pta","re","si","strana","sve","svych","svym","svymi","take","takze","tato","tema",
            "tento","teto","tim","timto","tipy","toho","tohoto","tom","tomto","tomuto","tu","tuto","tyto","uz","vam",
            "vas","vase","vice","vsak","zda","zde","zpet","zpravy","a","aniž","až","být","což","či","článek","článku",
            "články","další","i","jenž","jiné","již","jseš","jšte","k","každý","kteři","ku","me","ná","napište","nechť",
            "ní","nové","nový","o","práve","první","přede","při","s","sice","své","svůj","svých","svým","svými","také",
            "takže","te","těma","této","tím","tímto","u","v","více","však","všechen","z","zpět","zprávy"
        ];

        if (in_array($term, $stopwords)){
            return true;
        }else{
            return false;
        }
    }

    public function getIndex($collection) {

        $dictionary = array();
        $docCount = array();

        foreach($collection as $docID => $doc) {
            $doc = str_replace(str_split('(),<>!?'),'', $doc);
            $terms = explode(' ', $doc);
            $docCount[$docID] = count($terms);

            foreach($terms as $term) {
                if ($this->stopwords($term)==false){
                    if(!isset($dictionary[$term])) {
                        $dictionary[$term] = array('df' => 0, 'postings' => array());
                    }
                    if(!isset($dictionary[$term]['postings'][$docID])) {
                        $dictionary[$term]['df']++;
                        $dictionary[$term]['postings'][$docID] = array('tf' => 0);
                    }

                    $dictionary[$term]['postings'][$docID]['tf']++;
                }

            }
        }
        return array('docCount' => $docCount, 'dictionary' => $dictionary);
    }

//    public function getTfidf(){
//        foreach ($users as $key => $query){
//            foreach($query as $qterm) {
//                $entry = $index['dictionary'][$qterm];
//                foreach($entry['postings'] as $docID => $posting) {
//                    $matchDocs[$key][$docID] += $posting['tf'] * log($docCount  / $entry['df'] , 2);
//                }
//            }
//        }
//    }


    public function access1($array1, $array2, $percent){

        foreach ($array1 as $key => $value){
            $array1[$key] = $value['rating'];
        }

        foreach ($array2 as $key => $value){
            $array2[$key] = floatval($value['rating']);
        }
//
//        $array1count = count($array1) * ($percent/100);
//        $array2count = count($array2) * (1 - $percent/100);

        $array1count = 40 * ($percent/100);
        $array2count = 40 * (1 - $percent/100);


        $array1 = array_slice($array1, 0, $array1count);
        $array2 = array_slice($array2, 0, $array2count);

        $access1 = $array1 + $array2;

        arsort($access1);

        return $access1;
    }

    public function access2($array1, $array2, $percent){

        $access2 = $this->access1($array1, $array2, $percent);

        return $access2;
    }


    public function createCollection($products){
        $collection = array();
        foreach ($products as $product){
            if (!empty($product->content)){
                $collection[$product->title] = $product->content;
            }
        }
        return $collection;
    }

    public function tfIdf($section, $index, $tfidf){
        $matchDocs = array();
        $docCount = count($index['docCount']);
        if (!empty($section)){
            foreach ($tfidf as $key => $item){
                $query = explode(' ', $item['content']);
                if ((key_exists('rating',$item) && $item['rating'] === 'nehodnoceno') || (key_exists('rating',$item) && $item['rating'] === 0)){
                    $rating = 0.5;
                } else{
                    if (key_exists('rating',$item) && $item['rating'] < 1){
                        $rating = $item['rating'];
                    }else{
                        $rating = 1;
                    }
                }
                foreach($query as $qterm) {
                    if (isset($index['dictionary'][$qterm])){
                        $entry = $index['dictionary'][$qterm];
                        foreach($entry['postings'] as $docID => $posting) {
                            if (isset($matchDocs[$key][$docID])){
                                $matchDocs[$key][$docID] += ($posting['tf'] * log($docCount  / $entry['df'] , 2)) * $rating;
                            }else{
                                $matchDocs[$key][$docID] = ($posting['tf'] * log($docCount  / $entry['df'] , 2)) * $rating;
                            }
                        }
                    }
                }
            }
        }

        return $matchDocs;
    }

    public function getFinal($matchDocs, $index){
        $final = array();
        foreach ($matchDocs as $k => $matchDoc){
            foreach($matchDoc as $docID => $score) {
                if (!isset($final[$docID])){
                    $final[$docID] = $score/$index['docCount'][$docID];
                }else{
                    $final[$docID] += $score/$index['docCount'][$docID];
                }
            }
        }
        return $final;
    }

    public function sortFinal($final, $products, $tfidf, $rank){
        foreach ($final as $key => $item){
            if ($item <= $rank){
                unset($final[$key]);
            }else{
                foreach ($products as $product){
                    if ($product->title == $key){
                        $final[$key] = array(
                            'similarity' => $item,
                            'rating' => $product->rating,
                            'type' => $product->category
                        );
                    }
                }
                if (array_key_exists($key, $tfidf)){
                    unset($final[$key]);
                }
            }
        }

        return $final;
    }



}