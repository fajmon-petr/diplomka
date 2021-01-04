<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 19.10.2020
 * Time: 9:24
 */

namespace App\Presenters;

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

    //komentik

    private $productManager;
    private $userManager;
    private $voteManager;
    private $algorithmManager;
    private $historyManager;

    private $mathService;
    private $svd;

    public function __construct(ProductManager $productManager, UserManager $userManager, VoteManager $voteManager, AlgorithmManager $algorithmManager, HistoryManager $historyManager, MathService $mathService, Svd $svd)
    {
        $this->productManager = $productManager;
        $this->userManager = $userManager;
        $this->voteManager = $voteManager;
        $this->algorithmManager = $algorithmManager;
        $this->historyManager = $historyManager;

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

        //dump($this->viewHistory());
        //dump($this->userManager->getSimilarUserCategories($this->getUser()->id));
        //$this->template->similarity = $this->handleSimilarityUsers();

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

    }

    public function renderMyTopProducts(): void
    {
        $this->template->categories = $this->productManager->getCategories();

        $this->template->myTopProducts = $this->createMyTopProducts();

        //$this->template->myTopProducts = $this->handleMyTopProducts();
    }

    public function renderRecentlyViewedProducts(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $this->productManager->getAll();
        $this->template->history = $this->historyManager->getAllView()->where('user_id = ?', $this->getUser()->id);
        $this->template->time = $this->historyManager->getTimeView($this->getUser()->id);

        $section = $this->session->getSection('mySection');
        $this->template->recentlyProducts = $section->products;
    }

    public function renderTfidf(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $this->productManager->getAll();

        $this->template->tfidf = $this->handleTfIdf();



//        $section = $this->session->getSection('mySection');
//        dump($section->products);
    }

    public function renderPercent(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        //$this->template->products = $this->productManager->getAll();

        $this->template->percent30 = $this->createPercent(30);
        $this->template->percent50 = $this->createPercent(50);
        $this->template->percent70 = $this->createPercent(70);

        $this->template->rating = $this->voteManager->getLikes($this->getUser()->id);
        //dump($this->createComponentLikeControl());

        //dump($this->createPercent(30));
    }

    public function renderRating(): void
    {

        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $this->productManager->getAll();

        $this->template->rating = $this->voteManager->getLikes($this->getUser()->id);
        $this->template->items1 = $this->createRating(5);
        $this->template->items2 = $this->createRating(11);
        $this->template->items3 = $this->createRating(21);
        $this->template->items4 = $this->createRating(31);

        //dump($this->createRating());
    }

    public function renderSimilarHistory(): void
    {
        $this->template->categories = $this->productManager->getCategories();

        $this->template->similar = $this->similarToHistory();

    }

    public function renderSvd(): void
    {
        $this->template->categories = $this->productManager->getCategories();

        $this->svd();
        //dump($this->svd());
    }

    private function createBaseRelevant($final){
        $user = $this->getUser()->id;
        $products = $this->productManager->getAll();
        $likes = $this->voteManager->getLikes($user);

        foreach ($products as $product){
            foreach ($final as $key => $value) {
                if ($key == $product->title) {
                    $final[$key] = array(
                        'product_id' => $product->product_id,
                        'path' => $product->path,
                    );
                    $row = $this->voteManager->likeExists($key, $user);
                    if (!$row) {
                        $this->voteManager->saveLike($key, $user);
                    }
                }
            }
        }

        foreach ($likes as $key => $like){
            if (array_key_exists($like->product, $final)){
                $final[$like->product]['vote'] = $like->action;
            }
        }

        return $final;
    }

    public function createPercent($percent){

        $tfidf = $this->handleTfIdf();
        $myTop = $this->createMyTopProducts();

        $final = $this->algorithmManager->access1($tfidf, $myTop, $percent);

        $final = $this->createBaseRelevant($final);

        return $final;
    }

    public function createRating($products){


        $tfidf = $this->handleTfIdf();
        $myTop = $this->createMyTopProducts();

        $vote = $this->voteManager->countUserVote($this->getUser()->id);

        if ($products <= $vote){
            //$percent = $this->mathService->access2Rating($vote);

            $final = $this->algorithmManager->access2($tfidf, $myTop, $products);

            $final = $this->createBaseRelevant($final);
        }else {
            $final = array();
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
                );
            }

            $final = $this->sortFinal($section->products, $tfidf);
        }


        foreach ($final as $k => $fin){
            foreach ($categories as $cat){
                $ko = $fin['type'];
                if ($ko === $cat->name){
                    $final[$k]['type'] = $cat->full_name;
                }
            }

        }

        return $final;

    }


    public function similarToHistory(){

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
                //dump($product);
                $newDate =  new Nette\Utils\DateTime($product['buy_time']);
                $d2 = strtotime($newDate);
                $day = $this->mathService->historyRating($d1, $d2);
                $tfidf[$content->title] = array(
                    'content' => $content->content,
                    'rating' => $day,

                );
            }

           $final = $this->sortFinal($history, $tfidf);

        }

        $final = $this->createBaseRelevant($final);

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

        //vypocet similarity
        $final = $this->algorithmManager->getFinal($matchDocs, $index);

        arsort($final); // high to low

        // vyber produktu s podobnosti vetsi nez 0.5
        $sortFinal = $this->algorithmManager->sortFinal($final, $products, $tfidf, 0.5);

        $sortFinal = array_slice($sortFinal,0,100);

        return $sortFinal;
    }
//    protected function createComponentLikeControl()
//    {
//        $products = $this->productManager->getAll();
//        $rating = $this->voteManager->getLikes($this->getUser()->id);
//        return new Nette\Application\UI\Multiplier(function ($productId) use ($products) {
//            return new LikeControl($products[$productId]);
//        });
//
//    }


    public function handleLike($productId)
    {

        $this->voteManager->likeUpdate($productId, $this->user->id,1);

        if ($this->isAjax()) {
            $this->redrawControl("access2");
            // $this->redrawControl('article-' . $articleId); -- není potřeba
        } else {
            $this->redirect('this');
        }

    }

    public function handleUnlike($productId)
    {
        $this->voteManager->likeUpdate($productId, $this->user->id,0);

        if ($this->isAjax()) {
            $this->redrawControl("access2");
            // $this->redrawControl('article-' . $articleId); -- není potřeba
        } else {
            $this->redirect('this');
        }
    }

    public function svd(){

        $similarity = $this->userManager->getSimilarUserCategories($this->getUser()->id);

        $products = array();
        $matrix = array();
        foreach ($similarity as $sim){
            $user_history = $this->historyManager->getAll()->where('user_id = ?', $sim->similar_user);
            foreach ($user_history as $us){
                $matrix[$sim->similar_user][$us->product_id] = strval($us->rating);
                $products[] = $us->product_id;
            }
        }


        $user = $this->historyManager->getAll()->where('user_id = ?', $this->getUser()->id);
        foreach ($user as $product){
            $matrix2[$this->getUser()->id][$product->product_id] = strval($product->rating);
            $products[] = $product->product_id;
        }


        $products = array_unique($products);
        foreach ($products as $product){
            foreach ($matrix as $user => $value)
            if (!array_key_exists($product, $matrix[$user])){
                $matrix[$user][$product] = "0";
            }

            if (!array_key_exists($product, $matrix2[$this->getUser()->id])){
                $matrix2[$this->getUser()->id][$product] = "0";
            }
        }

        foreach ($matrix as $user => $m){
            ksort($m);
            $matrix[$user] = $m;
        }

        foreach ($matrix2 as $user => $m){
            ksort($m);
            $matrix2[$user] = $m;
        }
        foreach ($matrix2 as $user => $value){
            foreach ($value as $hodnota){
                $array[0][] = $hodnota;
            }
        }
//        echo "toto";
//        dump($matrix + $matrix2);
//        echo "konec";

        $i = 1;
        foreach ($matrix as $user => $value){
            foreach ($value as $hodnota){
                $array[$i][] = $hodnota;
                if ($array[$i][0] == 0){
                    $array[$i][0] = 0.1;
                }
            }
            $i++;
        }

        dump($array);
        //dump($matrix3);
//        dump($matrix);
//        dump($matrix2);
//        dump($matrix + $matrix2);
//        $test = $matrix + $matrix2;

//        $test = array(array('4', '0', '0', '0', '0'),
//            array('1', '3', '0', '1', '0'),
//            array('1', '0', '0', '0', '4'),
//            array('1', '0', '0', '1', '0'),
//            array('1', '0', '0', '0', '0'),
//        );

//        dump($this->svd->matrix($test));
        $tokens = $this->svd->matrix($array);
//
        dump($tokens);
        return $similarity;
    }


}