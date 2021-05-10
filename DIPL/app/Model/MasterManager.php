<?php
/**
 * Created by PhpStorm.
 * User: fajmo
 * Date: 19.03.2021
 * Time: 10:36
 */

namespace App\Model;

use App\Services\Lflc;
use App\Services\Svd;
use App\Services\MathService;


class MasterManager
{

    private $userManager;
    private $voteManager;
    private $svd;
    private $productManager;
    private $mathService;
    private $historyManager;
    private $lflc;

    public function __construct(UserManager $userManager, VoteManager $voteManager, Svd $svd, ProductManager $productManager,
                                MathService $mathService, HistoryManager $historyManager, Lflc $lflc)
    {
        $this->voteManager = $voteManager;
        $this->userManager = $userManager;
        $this->svd = $svd;
        $this->productManager = $productManager;
        $this->mathService = $mathService;
        $this->historyManager = $historyManager;
        $this->lflc = $lflc;

    }

    public function svd($user)
    {

        $similarity = $this->userManager->getSimilarUserCategories($user);
        $users = $this->userManager->getAll();
        $products = [];
        $matrix = [];
//        foreach ($similarity as $sim){
//            $user_votes = $this->voteManager->getUserProductsVote($sim->similar_user);
//            foreach ($user_votes as $uv){
//                $matrix[$sim->similar_user][$uv->product_id] = strval($uv->rating);
//                $products[] = $uv->product_id;
//            }
//        }
        foreach ($users as $simUser) {
            if ($simUser->user_id != $user) {
                $user_votes = $this->voteManager->getUserProductsVote($simUser->user_id);
                foreach ($user_votes as $uv) {
                    $matrix[$simUser->user_id][$uv->product_id] = strval($uv->rating);
                    $products[] = $uv->product_id;
                }
            }
        }

        $userInfo = $this->voteManager->getUserProductsVote($user);
        $matrix2 = [];
        foreach ($userInfo as $product) {
            $matrix2[$user][$product->product_id] = strval($product->rating);
            $products[] = $product->product_id;
        }



        $products = array_unique($products);
        $allProduct = $this->productManager->getAll();
        $newProducts = [];
        foreach ($allProduct as $allP){
            foreach ($products as $k => $product){
                if ($allP->product_id === $product){
                    $newProducts[] = [
                        'id' => $product,
                        'rating' => $allP->rating
                    ];
                }
            }
        }


        foreach ($newProducts as $product) {
            foreach ($matrix as $userM => $value)
                if (!array_key_exists($product['id'], $matrix[$userM])) {
                    $matrix[$userM][$product['id']] = '0';
//                    $matrix[$userM][$product['id']] = strval($product['rating']);
                }
            if (!array_key_exists($product['id'], $matrix2[$user])) {
                $matrix2[$user][$product['id']] = '0';
//                $matrix2[$user][$product['id']] = strval($product['rating']);
            }
        }

        $newMatrix = $matrix2 + $matrix;

        $restoreMatrix = $newMatrix;
//        dump( $newMatrix[4][213] - strval($this->productManager->getRateProduct($product)->rating));
//        die();

//        foreach ($newProducts as $product) {
//            foreach ($newMatrix as $user => $value) {
//                    $newMatrix[$user][$product['id']] = strval($newMatrix[$user][$product['id']] - strval($product['rating']));
//            }
//        }


        ksort($newMatrix);

        $i = 0;
        $userBackup = [];
        foreach ($newMatrix as $key => $value){
            unset($newMatrix[$key]);
            ksort($value);
            $newMatrix[$i] = $value;
            $userBackup[] = $key;
            $i++;
        }
//        dump($userBackup);
        $backup = array_keys($newMatrix[0]);
//        dump($backup);
//        dump($newMatrix);

        foreach ($newMatrix as $key => $value){
            $j = 0;
            foreach ($value as $k => $val){
                unset($value[$k]);
                $value[$j] = $val;
                $j++;
            }
            $newMatrix[$key] = $value;
        }

        $a = $this->svd->matrix($newMatrix);
//        dump($a);
//        die();
        foreach ($a as $key => $values){
            foreach ($userBackup as $k => $val){
                if ($key == $k){
                    $b[$val] = $values;
                }
            }
        }
//        dump($b[$user]);
//        dump($b);

        $userInfo = $b[$user];
//        dump($userInfo);
//        die();
        foreach ($userInfo as $k => $score){
            foreach ($backup as $kk => $item){
                if ($k == $kk){
                    $userRecomend[$item] = abs($score);
                }
            }
        }

        $userRecomend = $this->mathService->normalize($userRecomend);

        arsort($userRecomend);

        $userInfo = $this->voteManager->getUserProductsVote($user);
        foreach ($userInfo as $u){
            $userProducts[$u->product_id] = $u->product_id;
        }

        foreach ($userRecomend as $k => $product){
            if (array_key_exists($k, $userProducts)){
                unset($userRecomend[$k]);
            }

        }


//        dump($userRecomend);
//        die();
        return $userRecomend;
    }

    public function recreateSvd($user, $svd){
//        $svd = $this->svd($user);

        $svd = $this->mathService->normalize($svd);

        $svd = array_slice($svd, 0, 40, true);

        $this->productManager->deleteSvd($user);
        $this->productManager->saveSvd($user, $svd);
    }


    public function getNamesOfUserCategories($id, $categories){
        $userCategories = $this->userManager->getUserCategories($id);
        $newCategories = [];
        foreach ($categories as $category){
            foreach ($userCategories as $uCategory){
                if ($uCategory->category === $category->name){
                    $newCategories[] = $category->full_name;
                }
            }
        }
        return $newCategories;
    }

    public function getCrewNames($id){
        $users = $this->userManager->getAll();
        $crew = $this->userManager->getCrew($id);

        $newCrew = [];
        foreach ($users as $user){
            foreach ($crew as $member){
//                dump($member);
                if ($user->user_id === $member->similar_user){
                    $newCrew[] = $user->username;
                }
            }
        }
        return $newCrew;
    }

    public function phase2(){

//        $users = [46, 90, 80, 68, 94, 83, 61, 84, 75, 89, 93, 76, 92, 74, 88, 78, 82, 64, 104, 101, 95, 86];
        $users = [107];
        $products = [
            207 => 4,
            124 => 2,
            31 => 5,
            7 => 4,
            150 => 4,
            208 => 5,
            11 => 2,
            211 => 4,
            187 => 4,
            188 => 5,
            125 => 2,
            137 => 4,
            35 => 4,
            36 => 4,
            68 => 1,
            37 => 4,
            121 => 1,
            186 => 3,
            172 => 5,
            132 => 5,
            144 => 5,
            168 => 4,
            163 => 3,
            14 => 1,
            112 => 1,
            53 => 2,
            212 => 5,
            114 => 2,
            15 => 1,
            63 => 1,
            64 => 2,
            74 => 2,
            164 => 5,
            174 => 3,
            142 => 5,
            196 => 5,
            60 => 2,
            65 => 3,
            58 => 3,
            45 => 5,
//            'Absinth' => 4,
//            'Americké brambory' => 2,
//            'Ananas' => 5,
//            'Banán' => 4,
//            'Bio hovězí' => 4,
//            'Božkov Republica' => 5,
//            'Brambor' => 2,
//            'Chardonay Pays' => 4,
//            'Coca-cola' => 4,
//            'Fanta' => 5,
//            'Gyros' => 2,
//            'Hovězí mleté' => 4,
//            'Hrozno' => 4,
//            'Hruška' => 4,
//            'Hrách' => 1,
//            'Jahody' => 4,
//            'Kari' => 1,
//            'Kofola' => 3,
//            'Král sýrů' => 5,
//            'Kuřecí kousky' => 5,
//            'Kuřecí prsa' => 5,
//            'Lipánek' => 4,
//            'Mléko čerstvé' => 3,
//            'Mrkev' => 1,
//            'Paprika sladká' => 1,
//            'Paprika červená' => 2,
//            'Pavolín Pálava' => 5,
//            'Pepř černý' => 2,
//            'Rajče' => 1,
//            'Rýže loupaná' => 1,
//            'Rýře parboiled' => 2,
//            'Sojové nudličky' => 2,
//            'Sýr Ementál' => 5,
//            'Sýr kousky' => 3,
//            'Vepřové mleté' => 5,
//            'Zelený čaj' => 5,
//            'Zelí hlávkové' => 2,
//            'Čočka' => 3,
//            'Řepa' => 3,
//            'Švestky' => 5,
        ];

        foreach ($users as $user){
            $this->voteManager->deleteVotes($user);
            foreach ($products as $key => $product){
                $this->voteManager->insert($key, $user, $product);
            }
        }

        $svd = $this->productManager->selectSvd(4);

        foreach ($svd as $product){
            $reccomend[$product->product] = $product->similarity;
        }

        foreach ($users as $user){
            $this->productManager->deleteSvd($user);
            $this->productManager->saveSvd($user, $reccomend);
        }

//        $this->voteManager->deleteVotes(4);
//        foreach ($products as $key => $product){
//            $this->voteManager->insert($key, 4, $product);
//        }


    }

    public function phase2history(){

//        $users = [46, 90, 80, 68, 94, 83, 61, 84, 75, 89, 93, 76, 92, 74, 88, 78, 82, 64, 104, 101, 95, 86];
        $users = [107];

        $history = $this->historyManager->getHistoryById(4);
        $categories = $this->userManager->getUserCategories(4);
//        dump($categories);
        foreach ($categories as $category){
            $categ[] = $category->category;
        }

//        dump($categ);
        foreach ($users as $user){
            $this->userManager->deleteCategories($user);
            $this->userManager->insertCategories($user, $categ);
        }


        $histr = [];
        foreach ($history as $product){
            $histr[$product->product_id] = date_format($product->buy_time, 'Y:m:d');
        }

        foreach ($users as $user){
            $this->historyManager->deleteById($user);
            foreach ($histr as $id => $time){
                $this->historyManager->insert($user, $id, $time);
            }
        }

    }

    public function tfIdfNorm($tfidf){
        if (!empty($tfidf)){
            $norm = [];
            foreach ($tfidf as $k => $value){
                $norm[$k] = $value['similarity'];
            }

            $norm = $this->mathService->normalizeTfIdf($norm);

            foreach ($tfidf as $k => $value){
                foreach ($norm as $kk => $vvalue){
                    if ($k === $kk){
                        $tfidf[$k]['similarity'] = number_format($vvalue,4);
                    }
                }
            }
        }
        return $tfidf;
    }

    public function expertSystemInput($user){

        $allLikes = $this->voteManager->getLikes($user);
        $allProducts = $this->productManager->getAll();
//        $allLikes = $this->productManager->selectSvd($user);

        $time = [];
        foreach ($allLikes as $k => $like){
            if ($like->action === 0){
                unset($allLikes[$k]);
            }
            if ($like->access === 5){
                unset($allLikes[$k]);
            }
            $time[] = date_format($like->time,'Y:m:d');
        }

        $lastDate = max($time);

        $newLikes = [];

        foreach ($allLikes as $likes){
            if (date_format($likes->time, 'Y:m:d') === $lastDate){
                if (!array_key_exists($likes->product, $newLikes)){
                    if ($likes->access != 4){
                        $newLikes[$likes->product]['similarity'] = $likes->similarity;
                        $newLikes[$likes->product]['koeficient'] = 0;
                    }else{
                        $newLikes[$likes->product]['similarity'] = 0;
                        $newLikes[$likes->product]['koeficient'] = $likes->similarity;
                    }
                }else{
                    if ($likes->access != 4){
                        $newLikes[$likes->product]['similarity'] = $likes->similarity;
                    }else{
                        $newLikes[$likes->product]['koeficient'] = $likes->similarity;
                    }
                }
            }
        }


        foreach ($allProducts as $product){
            foreach ($newLikes as $title => $data){
                if ($product->title === $title){
                    $newLikes[$title]['rating'] = $product->rating;
                }
            }
        }

//        dump($newLikes);


        return $newLikes;
    }

    public function expertSystemInput2($user, $svd){

        $allLikes = $this->voteManager->getLikes($user);
        $allProducts = $this->productManager->getAll();

        $sim = [];
        foreach ($allLikes as $k => $like){
            if ($like->access != 4 || $like->party != 'b'){
                unset($allLikes[$k]);
            }
//            if ($like->action === 0){
//                unset($allLikes[$k]);
//            }
            if ($like->access === 1 && $like->party === 'b'){
                $sim[$like->product] = $like;
            }
            $time[] = date_format($like->time,'Y:m:d');
        }

//        dump($sim);

        $lastDate = max($time);

        $newLikes = [];

        foreach ($allLikes as $likes){
            if (date_format($likes->time, 'Y:m:d') === $lastDate) {
                if (!isset($newLikes[$likes->product], $newLikes)){
                    $newLikes[$likes->product]['koeficient'] = $likes->similarity;
                    $newLikes[$likes->product]['action'] = $likes->action;
                }
            }
        }


        $svd = $this->mathService->normalize($svd);

        foreach ($allProducts as $product){
            if (array_key_exists($product->title, $newLikes)){
                $newLikes[$product->title]['rating'] = $product->rating;
            }else{
                $newLikes[$product->title]['rating'] = $product->rating;
                $newLikes[$product->title]['koeficient'] = 0;
            }
            if (array_key_exists($product->title, $newLikes)){
                if (array_key_exists($product->product_id, $svd)){
                    $newLikes[$product->title]['similarity'] = $svd[$product->product_id];
                }else{
                    $newLikes[$product->title]['similarity'] = 0;
                }
            }
        }

        foreach ($sim as $likes){
            if (date_format($likes->time, 'Y:m:d') === $lastDate) {
                if ($newLikes[$likes->product]['similarity'] < $likes->similarity){
                    $newLikes[$likes->product]['similarity'] = $likes->similarity;
                }
                if (!isset($newLikes[$likes->product]['action'])){
                    $newLikes[$likes->product]['action'] = $likes->action;
                }else{
                    if ($newLikes[$likes->product]['action'] != 0){
                        $newLikes[$likes->product]['action'] = $likes->action;
                    }
                }
            }
        }

        foreach ($newLikes as $k => $like){
            if (isset($like['action'])){
                if ($like['action'] === 0){
                    unset($newLikes[$k]);
                }
            }
        }

//        dump($newLikes);
//        die();

        return $newLikes;

    }

    public function expertSystemInput3($user, $svd){

        $allLikes = $this->voteManager->getLikes($user);
        $allProducts = $this->productManager->getAll();


        $access = [];
        $access2 = [];
        foreach ($allLikes as $k => $like){
            if ($like->access === 1 && $like->party === 'b'){
                $access[] = $like;
                $time2[] = date_format($like->time,'Y:m:d');
            }
            if ($like->access === 4 && $like->party === 'b'){
                $access2[] = $like;
                $time[] = date_format($like->time,'Y:m:d');
            }
        }


        $lastDate = max($time);
        $lastDate2 = max($time2);

//        dump($lastDate);
//        dump($lastDate2);

        $newLikes = [];

        foreach ($access2 as $likes){
            if (date_format($likes->time, 'Y:m:d') === $lastDate) {
                if (!isset($newLikes[$likes->product], $newLikes)){
                    $newLikes[$likes->product]['koeficient'] = $likes->similarity;
                    $newLikes[$likes->product]['action'] = $likes->action;
                }
            }
        }


        $newLikes2 = [];
        foreach ($access as $item){
            if (date_format($item->time, 'Y:m:d') === $lastDate2) {
                if (!isset($newLikes2[$item->product], $newLikes2)){
                    $newLikes2[$item->product]['similarity'] = $item->similarity;
                    $newLikes2[$item->product]['action'] = $item->action;
                }
            }
        }


//        dump($newLikes);
//        dump($newLikes2);
//        die();
        $topLikes = [];

        foreach ($newLikes as $k => $like) {
            foreach ($newLikes2 as $k2 => $like2) {
                if ($k === $k2) {
                    $topLikes[$k]['similarity'] = $like2['similarity'];
                    $topLikes[$k]['koeficient'] = $like['koeficient'];
                    if ($topLikes[$k]['action'] > $like['action']) {
                        $topLikes[$k]['action'] = $like['action'];
                    }
                } else {
                    if (!isset($topLikes[$k])) {
                        $topLikes[$k]['koeficient'] = $like['koeficient'];
                        $topLikes[$k]['action'] = $like['action'];
                        $topLikes[$k]['similarity'] = 0;
                    }
                    if (!isset($topLikes[$k2])) {
                        $topLikes[$k2]['similarity'] = $like2['similarity'];
                        $topLikes[$k2]['action'] = $like2['action'];
                        $topLikes[$k2]['koeficient'] = 0;
                    }
                }
            }
        }

        foreach ($allProducts as $id => $product){
            foreach ($topLikes as $k => $like){
                if ($product->title === $k){
                    $topLikes[$k]['rating'] = $product->rating;
                }
                if ($topLikes[$k]['action'] === 0) {
                    unset($topLikes[$k]);
                }
            }
        }

        return $topLikes;

    }

    public function lflc($user, $group, $svd){

//        $exs = $this->expertSystemInput($user);
        $exs = $this->expertSystemInput3($user, $svd);
//        dump($exs);
//        die();
        $output = [];

        $output = $this->lflc->lflc($exs);

        $i = 0;
        if ($group === 'a'){
            foreach ($exs as $title => $item){
                $val = str_replace("\r", "", $output[$i]);
                $outputLflc[$title] = floatval($val) * (1 + $item['similarity']);
                $i++;
            }
        }elseif ($group === 'b'){
            foreach ($exs as $title => $item){
                $val = str_replace("\r", "", $output[$i]);
                $outputLflc[$title] = floatval($val) * $item['similarity'];
                $i++;
            }
        }

        arsort($outputLflc);

        $output = $outputLflc;
//        $output = array_slice($outputLflc, 0,20);

        return $output;
    }

    public function outputLflfc($user, $group, $svd){

        $output = $this->lflc($user, $group, $svd);
        $category = $this->userManager->getUserCategories($user);
        $kat = [];
        foreach ($category as $cat){
            $kat[] = $cat->category;
        }

        $products = $this->productManager->getAll();

        $final = [];
        foreach ($products as $product){
            foreach ($output as $title => $sim){
                if ($title === $product->title){
                    $final[$title] = [
                        'sim' => $sim,
                        'rating' => $product->rating,
                        'path' => $product->path,
                        'product_id' => $product->product_id,
                        'vote' => 1,
                        'group' => $group
                    ];
                    if (in_array($product->category, $kat)){
//                        dump($final[$title]['similarity']);
                        $final[$title]['sim'] += 0.2;
                    }
                }
            }
        }

        array_multisort($final, SORT_DESC);
//        arsort($final);
        return $final;

    }

    private function description($array){
        foreach ($array as $title => $item){
            if ($item['similarity'] > 2){
                $array[$title]['alg2'] = 'Velmi vysoká podobnost';
            }elseif($item['similarity'] > 0.6){
                $array[$title]['alg2'] = 'Vysoká podobnost';
            }else{
                $array[$title]['alg2'] = 'Podobnost k zakoupeným';
            }
        }
        return $array;
    }

    public function access4Description($array){

        $array = $this->description($array);

        foreach ($array as $title => $item){
            if ($item['koeficient'] > 1.4){
                $array[$title]['alg3'] = 'Podobné k dnešnímu nákupu.';
            }elseif ($item['koeficient'] > 1.1){
                $array[$title]['alg3'] = 'Podobné k nákupu do týdne.';
            }elseif ($item['koeficient'] > 0.9){
                $array[$title]['alg3'] = 'Podobné k nákupu do měsíce.';
            }elseif ($item['koeficient'] > 0.6){
                $array[$title]['alg3'] = 'Podobné k nákupu do 3 měsíců.';
            }else{
                $array[$title]['alg3'] = '';
            }
        }

        return $array;
    }
//
//    public function access5Description($array){
//
//        $array = $this->description($array);
//
//        foreach ($array as $title => $item){
//            if ($item['koeficient'] > 1.4){
//                $array[$title]['alg2'] = 'Podobné k dnešnímu nákupu.';
//            }elseif ($item['koeficient'] > 1.1){
//                $array[$title]['alg2'] = 'Podobné k nákupu do týdne.';
//            }elseif ($item['koeficient'] > 0.9){
//                $array[$title]['alg2'] = 'Podobné k nákupu do měsíce.';
//            }elseif ($item['koeficient'] > 0.6){
//                $array[$title]['alg2'] = 'Podobné k nákupu do 3 měsíců.';
//            }
//        }
//    }


    //administrativní funkce

    public function phase3(){

        $users = [46, 90, 80, 68, 94, 83, 61, 84, 75, 89, 93, 76, 92, 74, 88, 78, 82, 64, 104, 101, 95, 86, 104, 101, 103, 90];

        $history = $this->historyManager->getHistoryById(4);
        dump($history);
        $histr = [];
        $i = 0;
        foreach ($history as $product){
            $histr[$i][$product->product_id] = date_format($product->buy_time, 'Y:m:d');
            $i++;
        }

        foreach ($users as $user){
            $this->historyManager->deleteById($user);
            foreach ($histr as $id => $data){
                foreach ($data as $key => $value){
                    $this->historyManager->insert($user, $key, $value);
                }
            }
        }

    }




}