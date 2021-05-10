<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\MasterManager;
use Nette;
use App\Model\ProductManager;
use App\Model\VoteManager;
use App\Model\UserManager;
use App\Model\AlgorithmManager;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{

    private $productManager;
    private $voteManager;
    private $userManager;
    private $algorithmManager;
    private $masterManager;

    public function __construct(ProductManager $productManager, VoteManager $voteManager, UserManager $userManager, AlgorithmManager $algorithmManager, MasterManager $masterManager)
    {

        $this->productManager = $productManager;
        $this->voteManager = $voteManager;
        $this->userManager = $userManager;
        $this->algorithmManager = $algorithmManager;
        $this->masterManager = $masterManager;
    }

    public function renderDefault(): void
    {
        $this->template->products = $this->productManager->getAll();
        $this->template->categories = $this->productManager->getCategories();

        $this->template->myTopProducts = $this->handleTopProducts();
        $this->template->topProducts = $this->handleProducts();


        //dump($this->handleTopProducts());
        //dump($this->handleProducts());

        if ($this->getUser()->loggedIn){
            $market = $this->session->getSection('market');
            $this->template->cartCount = count($market->products);
        }

    }

    public function crewProducts(){

        $user = $this->getUser()->id;

        if ($user == null){
            $user = 1;
        }

        $topProduct = $this->userManager->getTopProducts($user);

        $top100 = array();

        foreach ($topProduct as $product){
            $pr = $this->productManager->getById($product->product_id);
            $top100[$pr->title] = array(
                'count' => $this->voteManager->countRating($pr->product_id),
                'rating' => number_format($pr->rating, 1),
                'type' => $pr->category,
                'koeficient' => 0.5,
            );
        }

        array_multisort($top100, SORT_DESC);

        return $top100;
    }

    public function myProducts(){

        $user = $this->getUser()->id;

        if ($user == null){
            $user = 1;
        }

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
                        'koeficient' => 0.7,
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
                        'koeficient' => 0.7,
                    );
                }
            }
        }

        return $top100;

    }

    public function tfidf(){
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
            $final[$k]['koeficient'] = 1;
            foreach ($categories as $cat){
                $ko = $fin['type'];
                if ($ko === $cat->name){
                    $final[$k]['type'] = $cat->full_name;
                }
            }

        }

        return $final;
    }

    public function handleTopProducts(){

        $top20 = array();

        $tfidf = $this->tfidf();
        $myProducts = $this->myProducts();
        $crewProducts = $this->crewProducts();

        foreach ($tfidf as $title => $product){
            $top20[$title] = ([
                'koeficient' => $product['koeficient'],
                'rating' => $product['rating']
                ]);
        }

        foreach ($myProducts as $title => $product){
            if (array_key_exists($title, $top20)){
                $top20[$title]['koeficient'] += $product['koeficient'];
            } else{
                $top20[$title]['koeficient'] = $product['koeficient'];
                $top20[$title]['rating'] = $product['rating'];
            }

        }

        foreach ($crewProducts as $title => $product){
            if (array_key_exists($title, $top20)){
                $top20[$title]['koeficient'] += $product['koeficient'];
            } else{
                $top20[$title]['koeficient'] = $product['koeficient'];
                $top20[$title]['rating'] = $product['rating'];
            }
        }

        arsort($top20);

        $top20 = array_slice($top20, 0, 20);

        foreach ($top20 as $title => $value){
            $top20[$title]['totalRating'] = number_format($value['koeficient']*$value['rating'], 2);
        }


        return $top20;
    }

    public function handleProducts(){

        $products = $this->productManager->getAll();

        foreach ($products as $product){
            $pr = $this->productManager->getById($product->product_id);
            $top100[$pr->title] = array(
                'count' => $this->voteManager->countRating($pr->product_id),
                'rating' => number_format($pr->rating, 1),
            );
        }

        arsort($top100);
        $top20 = array_slice($top100, 0, 20);

        foreach ($top20 as $title => $value){
            $top20[$title]['totalRating'] = number_format($value['count']*$value['rating'], 2);
        }

        return $top20;
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
}
