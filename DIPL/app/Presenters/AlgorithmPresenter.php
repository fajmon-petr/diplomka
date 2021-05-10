<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 19.10.2020
 * Time: 9:24
 */

namespace App\Presenters;

use App\Model\MasterManager;
use Nette;
use App\Model\ProductManager;
use App\Model\UserManager;
use App\Model\VoteManager;
use App\Model\AlgorithmManager;
use App\Model\HistoryManager;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Form;
use App\Components\LikeControl;

use App\Services\MathService;
use App\Services\Svd;


class AlgorithmPresenter extends Nette\Application\UI\Presenter
{

    private $productManager;
    private $userManager;
    private $voteManager;
    private $algorithmManager;
    private $historyManager;
    private $masterManager;

    private $mathService;
    private $svd;


    public function __construct(ProductManager $productManager, UserManager $userManager, VoteManager $voteManager, AlgorithmManager $algorithmManager,
                                HistoryManager $historyManager, MathService $mathService, Svd $svd, MasterManager $masterManager)
    {
        $this->productManager = $productManager;
        $this->userManager = $userManager;
        $this->voteManager = $voteManager;
        $this->algorithmManager = $algorithmManager;
        $this->historyManager = $historyManager;
        $this->masterManager = $masterManager;

        $this->mathService = $mathService;
        $this->svd = $svd;

    }

    public function renderDefault(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        $this->template->userCategories = $this->userManager->getUserCategories($this->getUser()->id);

        $this->template->users = $this->userManager->getAll();

        $similarity = $this->userManager->getSimilarUserCategories($this->getUser()->id);

        $simU = array();
        foreach ($similarity as $i){
            $cat = $this->userManager->getUserCategories($i->similar_user);
            foreach ($cat as $c){
                $simU[$i->similar_user][] = $c->category;
            }
        }

        $this->template->similarity = $simU;

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);

        //dump($this->viewHistory());
        //dump($this->userManager->getSimilarUserCategories($this->getUser()->id));
        //$this->template->similarity = $this->handleSimilarityUsers();

    }

//    public function renderExpertSystem(){
//
//        $this->template->categories = $this->productManager->getCategories();
//
//        $this->masterManager->expertSystemInput($this->getUser()->id);
//
//        $market = $this->session->getSection('market');
//        $this->template->cartCount = count($market->products);
//    }

    public function renderLflc(){
        $this->template->categories = $this->productManager->getCategories();

        $session = $this->getSession();
        $sessionSection = $session->getSection('access5');
        $svd = $session->getSection('mySection');
//        dump($sessionSection->products['a']);
        if (!isset($sessionSection->products['a'])){
            $sessionSection->products['a'] = $this->masterManager->outputLflfc($this->getUser()->id,'a', $svd->svd);
        }
        if (!isset($sessionSection->products['b'])){
            $sessionSection->products['b'] = $this->masterManager->outputLflfc($this->getUser()->id,'b', $svd->svd);
        }
//        dump($sessionSection->products['a']['Kuře']);
//        dump($sessionSection->products['a']);
//        $output = $this->masterManager->outputLflfc($this->getUser()->id);
        $this->template->lflc = $sessionSection->products['a'];
        $this->template->lflc2 = $sessionSection->products['b'];
        $this->template->dateA = $this->lastDate(5,'a');
        $this->template->dateB = $this->lastDate(5,'b');

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);

//        $this->masterManager->lflc($this->getUser()->id);

//        $this->masterManager->outputLflfc($this->getUser()->id);
    }

    public function renderAllProductsGroup(): void
    {
        $products = $this->productManager->getAll();

        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $products;

        $topProduct = $this->userManager->getTopProducts($this->getUser()->id);

        $top100 = array();

        foreach ($topProduct as $product){
            $pr = $this->productManager->getById($product->product_id);
            $top100[$pr->title] = array(
                'count' => $this->voteManager->countRating($pr->product_id),
                'rating' => number_format($pr->rating, 1),
                'type' => $pr->category,
            );
        }

        array_multisort($top100, SORT_DESC);

        $this->template->topProducts = $top100;

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);

    }

    public function renderMyTopProducts(): void
    {

        $svd = $this->session->getSection('mySection');
        $categories = $this->productManager->getCategories();

        $exist = $this->productManager->svdExist($this->getUser()->id);

        $myTopProducts = [];
        $row = $this->voteManager->voteExists($this->getUser()->id);

        if ($row !== null){
            if ($exist === null){
                $this->masterManager->recreateSvd($this->getUser()->id, $svd->svd);
            }
            $myTopProducts = $this->myTopProductsSvd();
        }

        $this->template->myTopProducts = $myTopProducts;
        $this->template->categories = $categories;
        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);

    }

    public function renderRecentlyViewedProducts(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $this->productManager->getAll();
        $this->template->history = $this->historyManager->getAllView()->where('user_id = ?', $this->getUser()->id);
        $this->template->time = $this->historyManager->getTimeView($this->getUser()->id);

        $section = $this->session->getSection('mySection');
        $this->template->recentlyProducts = $section->products;

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);
    }

    public function renderTfidf(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $this->productManager->getAll();


        $tfidf = $this->handleTfIdf();
        $tfidf = $this->masterManager->tfIdfNorm($tfidf);

        $this->template->tfidf = $tfidf;
//        dump($this->handleTfIdf());
        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);

//        $section = $this->session->getSection('mySection');
//        dump($section->products);
    }

    public function renderPercent(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        //$this->template->products = $this->productManager->getAll();

        $session = $this->getSession();
        $sessionSection = $session->getSection('access1');

        $sessionSection->products['a'] = $this->createPercent(30, 'a');
        $this->template->percent30 = $sessionSection->products['a'];

        $sessionSection->products['b'] = $this->createPercent(50, 'b');
        $this->template->percent50 = $sessionSection->products['b'];

        //dump($sessionSection->products['b']);

        $sessionSection->products['c'] = $this->createPercent(70, 'c');
        $this->template->percent70 = $sessionSection->products['c'];

        //dump($sessionSection->products['c']);
//        $this->template->rating = $this->voteManager->getLikes($this->getUser()->id);
        //dump($this->createComponentLikeControl());

        //dump($sessionSection->products);
        //dump($this->createPercent(30));
        //$this->template->date = $this->algorithmManager->lastDate();

        $this->template->dateA = $this->lastDate(1,'a');
        $this->template->dateB = $this->lastDate(1,'b');
        $this->template->dateC = $this->lastDate(1,'c');

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);

        //dump($sessionSection->products);
    }

    public function renderRating(): void
    {

        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $this->productManager->getAll();

//        $this->template->rating = $this->voteManager->getLikes($this->getUser()->id);

        $session = $this->getSession();
        $sessionSection = $session->getSection('access2');

        $sessionSection->products['a'] = $this->createRating(5, 'a');
        $this->template->items1 = $sessionSection->products['a'];
        $sessionSection->products['b'] = $this->createRating(11, 'b');
        $this->template->items2 = $sessionSection->products['b'];
        $sessionSection->products['c'] = $this->createRating(21, 'c');
        $this->template->items3 = $sessionSection->products['c'];
        $sessionSection->products['d'] = $this->createRating(31, 'd');
        $this->template->items4 = $sessionSection->products['d'];

        $this->template->dateA = $this->lastDate(2,'a');
        $this->template->dateB = $this->lastDate(2,'b');
        $this->template->dateC = $this->lastDate(2,'c');
        $this->template->dateD = $this->lastDate(2,'d');

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);
        //dump($this->createRating());
    }

    public function renderSimilarHistory(): void
    {
        $this->template->categories = $this->productManager->getCategories();

        $session = $this->getSession();
        $sessionSection = $session->getSection('access3');

        $sessionSection->products['a'] = $this->access3('a');
        $this->template->similar = $sessionSection->products['a'];

        $this->template->dateA = $this->lastDate(3,'a');

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);
    }

    public function renderSimilarHistoryUpdated(): void
    {
        $this->template->categories = $this->productManager->getCategories();

        $session = $this->getSession();
        $sessionSection = $session->getSection('access4');

        $sessionSection->products['a'] = $this->access4('a');
        $sessionSection->products['a'] = $this->masterManager->access4Description($sessionSection->products['a']);
        $this->template->similarA = $sessionSection->products['a'];

        $sessionSection->products['b'] = $this->access5('b');
        $sessionSection->products['b'] = $this->masterManager->access4Description($sessionSection->products['b']);
//        dump($sessionSection->products['b']);
        $this->template->similarB = $sessionSection->products['b'];
        $this->template->dateA = $this->lastDate(4,'a');
        $this->template->dateB = $this->lastDate(4,'b');

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);
    }

    public function renderMyRateProducts(){

        $this->template->categories = $this->productManager->getCategories();
        $voteProducts = $this->voteManager->getUserProductsVote($this->getUser()->id);
        $products = $this->productManager->getAll();

        $final = array();

        foreach ($products as $product){
            foreach ($voteProducts as $vote){
                if ($product->product_id == $vote->product_id){
                    $final[$product->title] = $vote->rating;
                }
            }
        }
        ksort($final);

        $this->template->products = $final;

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);

    }

    public function renderControl(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $this->productManager->getAll();

        $test = $this->algorithmManager->getUserLikes($this->getUser()->id);
//        foreach ($test as $t){
//            dump($t);
//            dump($t->access);
//            dump($t->party);
//        }

        $this->template->control = $test;

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);

    }



    public function lastDate($access, $party){

        $time = array();
        //$this->algorithmManager->lastDate($this->getUser()->id, $access, $group);
        $date = $this->algorithmManager->lastDate($this->getUser()->id, $access, $party);
        //$date = date('Y.m.d');
        foreach ($date as $dat){
            $time[] = date_format($dat['time'], 'Y.m.d');
        }

        $time = array_unique($time);
        if (!empty($time)){
            $time = max($time);
        }else{
            $time = 'neodesláno';
        }

        return $time;

    }

    private function createBaseRelevant($final, $group){
        $user = $this->getUser()->id;
        $products = $this->productManager->getAll();

        $session = $this->getSession();
        $sessionSection = $session->getSection('access1');
        $sessionSection->userId = $user;

        foreach ($products as $product){
            foreach ($final as $key => $value) {

                if ($key == $product->title) {
                    if (!isset($sessionSection->products[$key]['vote'])){
                        $vote = 1;
                    } else{
                        $vote = $sessionSection->products[$key]['vote'];
                    }

                    $final[$key] = array(
                        'product_id' => $product->product_id,
                        'product_rate' => $product->rating,
                        'path' => $product->path,
                        'vote' => $vote,
                        'group' => $group,
                        'sim' => $value,
                    );
                }
            }
        }

        return $final;
    }

    public function createPercent($percent, $group){

        $tfidf = $this->handleTfIdf();
        $myTop = $this->myTopProductsSvd();

        $tfidf = $this->masterManager->tfIdfNorm($tfidf);
        $final = $this->algorithmManager->access1($tfidf, $myTop, $percent);
        $alg = $final['alg'];

        $final = $this->createBaseRelevant($final['access'], $group);

        $final = $this->algorithmManager->setTooltipAlg($final, $alg);

        return $final;
    }

    public function createRating($products, $group){


        $tfidf = $this->handleTfIdf();
        $myTop = $this->myTopProductsSvd();

        $tfidf = $this->masterManager->tfIdfNorm($tfidf);
        $vote = $this->voteManager->countUserVote($this->getUser()->id);

        if ($products <= $vote){
            //$percent = $this->mathService->access2Rating($vote);

            $final = $this->algorithmManager->access2($tfidf, $myTop, $products);
            $alg = $final['alg'];
            $final = $this->createBaseRelevant($final['access'], $group);

        }else {
            $final = array();
        }

        if (!empty($final)){
            $final = $this->algorithmManager->setTooltipAlg($final, $alg);
        }


        return $final;
    }


    /**
     * vraci matici top produktu z mych kategorii
     * @return array
     */

    public function createMyTopProducts(){

        $user = $this->getUser()->id;

        $crewProducts = $this->userManager->getTopProducts($user);

        $myCategories = $this->userManager->getUserCategories($user);
        $myCat = array();
        foreach ($myCategories as $cat){
            $myCat[] = $cat->category;
        }

        foreach ($crewProducts as $s){
            $a[] = $s;
        }

        $top100 = array();
        if (!empty($a)){
            foreach ($crewProducts as $product){
                $pr = $this->productManager->getById($product->product_id);
                if (in_array($pr->category,$myCat)){
                    $top100[$pr->title] = array(
                        'count' => $this->voteManager->countRating($pr->product_id),
                        'rating' => number_format($pr->rating, 1),
                        'type' => $pr->category,
                    );
                }
            }
            array_multisort($top100, SORT_DESC);
        }else{
            $products = $this->productManager->getAll();
            foreach ($products as $product){
                if (in_array($product->category, $myCat)){
                    $top100[$product->title] = array(
                        'count' => $this->voteManager->countRating($product->product_id),
                        'rating' => number_format($product->rating, 1),
                        'type' => $product->category,
                    );
                }
            }
        }

        return $top100;
    }


    public function handleTfIdf(){

        $section = $this->session->getSection('mySection');
        $categories = $this->productManager->getCategories();
        $final = array();

        if  (!empty($section->products)){
            foreach ($section->products as $key => $product){
                $content = $this->productManager->getByName($key);
                $tfidf[$content->title] = array(
                    'content' => $content->content,
                    'rating' => $product['rating'],
                    'test' => 1,
                );
            }

            $final = $this->sortFinal($section->products, $tfidf);
        }
//        dump($final);
        foreach ($final as $k => $fin){
//            $newSim = $final[$k]['similarity']/$final[$k]['koeficient'];
//            $final[$k]['similarity'] = $newSim;
            foreach ($categories as $cat){
                $ko = $fin['type'];
                if ($ko === $cat->name){
                    $final[$k]['type'] = $cat->full_name;
                }
            }
//            if ($final[$k]['similarity'] < 0.5){
//                unset($final[$k]);
//            }
        }


        return $final;

    }


    public function access3($group){

        $final = $this->similarToHistory($group);

        return $final;

    }

    public function access4($group){

        $final = $this->similarToHistory($group);
//        dump($final);
        $history = $this->historyManager->getAll()->where('user_id = ?', $this->getUser()->id);

        $koeficient = $this->mathService->access4koeficient(count($history));
//        dump($koeficient);
        $final = $this->historyKoeficient($final, $koeficient);
        arsort($final);
        foreach ($final as $name => $fin){
            $final[$name]['koeficient'] += $koeficient;
            $final[$name]['sim'] = $final[$name]['koeficient'];
        }
//        dump($final);
        return $final;


    }

    public function access5($group){

        $final = $this->similarToHistory($group);


        $history = $this->historyManager->getAll()->where('user_id = ?', $this->getUser()->id);
        foreach ($history as $his){
            $product = $this->productManager->getByName($his->product_id);
            $histr[] = $product->category;
//            $rating[$product->product_id] = $this->voteManager->getMyRating($product->product_id, $this->getUser()->id);
        }

//        dump($histr);
//        dump(array_unique($histr));
        foreach ($final as $name => $fin){

            $koeficient = $this->mathService->access5koeficient($fin['type'], $histr);

            $sim[$name]['similarity'] = ($fin['similarity']/$fin['count']) + $koeficient;
            $a = $sim[$name] + $final[$name];
//            dump($a);
            if ($koeficient === 0.5){
                $kof =  0.5;
                $a['alg'] = 'Stejná kategorie k nakoupené.';
            }else{
                $kof = 0;
                $a['alg'] = '';
            }
            $final[$name] = $a;
            $final[$name]['koeficient'] = $final[$name]['koeficient'] + $kof;
            $final[$name]['sim'] = $final[$name]['koeficient'];
//            dump($final[$name]);
            if ($final[$name]['similarity'] < 0.5){
                unset($final[$name]);
            }

        }

        arsort($final);
//        dump($final);
        return $final;
    }

    private function historyKoeficient($array, $koeficient){
        foreach ($array as $name => $fin){
            $sim[$name]['similarity'] = ($fin['similarity']/$fin['count']) + $koeficient;
            $a = $sim[$name] + $array[$name];
            //dump($a['koeficient']);
            if ($a['koeficient'] > 0.8){
                $a['alg'] = 'Koeficient produktu je větší než 0.8.';
            }else{
                $a['alg'] = '';
            }
            $array[$name] = $a;
            if ($array[$name]['similarity'] < 0.5){
                unset($array[$name]);
            }
        }

        return $array;
    }

    private function similarToHistory($group){

        $user = $this->getUser()->id;

        $history = $this->historyManager->getAll()->where('user_id = ?', $user);
        //dump($history);
//        foreach ($history as $his){
//            //dump($his->product_id);
//            $content = $this->productManager->getByName($his->product_id);
//            //dump($content->title);
//
//        }

//        $date = date('Y:m:d');
        $date = new Nette\Utils\DateTime('now');

        $d1 = strtotime($date);

        foreach ($history as $his){
            $histr[] = $his;
        }


        $final = array();

        if  (!empty($histr)){
            foreach ($history as $key => $product){
                $content = $this->productManager->getByName($product->product_id);
                $test = $this->voteManager->getMyRating($content->product_id, $this->getUser()->id);
//                if ($a != null){
//                    dump($a['rating']);
//                }
                $newDate =  new Nette\Utils\DateTime($product['buy_time']);
                $d2 = strtotime($newDate);
                $day = $this->mathService->historyRating($d1, $d2);
                $tfidf[$content->title] = array(
                    'content' => $content->content,
                    'rating' => $day,
                );
                if ($test != null){
                    $tfidf[$content->title]['test'] = $test['rating']*2/10;
                }else{
                    $tfidf[$content->title]['test'] = 0.5;
                }
            }

            $final = $this->sortFinal($history, $tfidf);

        }

        $sim = $final;
        $final = $this->createBaseRelevant($final, $group);


//        dump($sim);
        foreach ($final as $k => $fin){
            foreach ($sim as $ka => $s){
                if ($k == $ka){
                    $final[$k]['similarity'] = $s['similarity'];
                    $final[$k]['koeficient'] = $s['koeficient'];
                    $final[$k]['count'] = $s['count'];
                    $final[$k]['type'] = $s['type'];
                }
            }
        }


        return $final;

    }

    private function sortFinal($history, $tfidf){
        $products = $this->productManager->getAll();

        // vytvoreni kolekce pro TF-IDF (produkt - popis)
        $collection = $this->algorithmManager->createCollection($products);

        // vypocteni TF a DF pro danou kolekci
        $index = $this->algorithmManager->getIndex($collection);

        //vypocet tf-idf
        $matchDocs = $this->algorithmManager->tfIdf($history, $index, $tfidf);

        foreach ($matchDocs as $k => $val){
            arsort($val);
            $val = array_slice($val, 0 , 20);
            $matchDocs[$k] = $val;
        }
//        dump($matchDocs);
        //vypocet similarity
        $final = $this->algorithmManager->getFinal($matchDocs, $index);

        arsort($final); // high to low
//        dump($final);

        // vyber produktu s podobnosti vetsi nez 0.5
        $sortFinal = $this->algorithmManager->sortFinal($final, $products, $tfidf, 0.5);

        foreach ($sortFinal as $k => $val){
            $norm[$k] = $val['similarity'];
        }

//        arsort($sortFinal); // high to low

        $sortFinal = array_slice($sortFinal,0,100);

        return $sortFinal;
    }


    public function handleRecreateLflc(){

        $session = $this->getSession();
        $sessionSection = $session->getSection('access5');
        $svd = $session->getSection('mySection');

        $sessionSection->products['a'] = $this->masterManager->outputLflfc($this->getUser()->id, 'a', $svd->svd);
        $sessionSection->products['b'] = $this->masterManager->outputLflfc($this->getUser()->id, 'b', $svd->svd);

        $this->redirect('this');
    }

    public function handleLike($productId, $number)
    {

        //$this->voteManager->likeUpdate($productId, $this->user->id,1);

        $session = $this->getSession();

        if($number == 5){
            $sessionSection = $session->getSection('access5');
            $sessionSection->products['a'][$productId]['vote'] = 1;
        }else{
            $sessionSection = $session->getSection('access1');
            $sessionSection->products[$productId]['vote'] = 1;
        }

        if ($this->isAjax()) {
            $this->redrawControl("access2");
            // $this->redrawControl('article-' . $articleId); -- není potřeba
        } else {
            $this->redirect('this');
        }

    }


    public function handleUnlike($productId, $number)
    {
        //$this->voteManager->likeUpdate($productId, $this->user->id,0);

        $session = $this->getSession();


        if($number == 5){
            $sessionSection = $session->getSection('access5');
            $sessionSection->products['a'][$productId]['vote'] = 0;
        }else{
            $sessionSection = $session->getSection('access1');
            $sessionSection->products[$productId]['vote'] = 0;
        }


        if ($this->isAjax()) {
            $this->redrawControl("access2");
            // $this->redrawControl('article-' . $articleId); -- není potřeba
        } else {
            $this->redirect('this');
        }
    }

    public function handleSave($access, $group){

        $session = $this->getSession();

        if ($access == 1){
            $sessionSection = $session->getSection('access1');
        }elseif ($access == 2){
            $sessionSection = $session->getSection('access2');
        }elseif ($access == 3){
            $sessionSection = $session->getSection('access3');
        }elseif ($access == 4){
            $sessionSection = $session->getSection('access4');
        }elseif ($access == 5){
            $sessionSection = $session->getSection('access5');
        }


        if (!empty($sessionSection->products[$group])){
            $time = date('Y-m-d');

            $row = $this->algorithmManager->likeExists($this->getUser()->id, $time, $access, $group);
            if ($row) {
                $this->algorithmManager->deleteLikes($this->getUser()->id, $time, $access, $group);
            }


            foreach ($sessionSection->products[$group] as $key => $product){

                if ($product['group'] == $group){
                    $this->algorithmManager->saveGroup($this->getUser()->id, $key, $product['vote'], $time, intval($access), $group, floatval($product['sim']));
                }

            }

            $group = strtoupper($group);

            $this->flashMessage("Skupina {$group} uložena");

        }else{
            $this->flashMessage('Skupina je prazdna');
        }


        $this->redirect('this');

    }

    private function myTopProductsSvd(){
        $categories = $this->productManager->getCategories();
        $products = $this->productManager->getAll();
        $svd = $this->productManager->selectSvd($this->getUser()->id);
        $userCategory = $this->userManager->getUserCategories($this->getUser()->id);

        $cat = [];
        foreach ($userCategory as $us){
            $cat[] = $us->category;
        }

        foreach ($svd as $sim){
            $newSvd[$sim->product] = $sim->similarity;
        }
        arsort($newSvd);
//        dump($newSvd);

        $myTopProducts = [];
        foreach ($newSvd as $key => $rating){
            foreach ($products as $id => $product){
                if ($id == $key){
                    $myTopProducts[$product->title] = [
                        'category' => $product->category,
                        'rating' => $product->rating,
                        'similarity' => $rating,
                        'count' => $this->voteManager->countRating($product->product_id),
                    ];
                }
            }
        }
//        dump($myTopProducts);
//        die();
        foreach ($myTopProducts as $title => $product){
            foreach ($categories as $category){
                if ($product['category'] === $category->name){
                    $myTopProducts[$title]['category'] = $category->full_name;
                }
            }
        }
//        dump($myTopProducts);
        return $myTopProducts;
    }


}