<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 9:31
 */

namespace App\Presenters;


use Nette;
use Nette\Application\UI\Form;
use App\Model\ProductManager;
use App\Model\VoteManager;
use App\Model\AlgorithmManager;
use Nette\Utils\DateTime;
class ProductPresenter extends Nette\Application\UI\Presenter
{
    private $productManager;
    private $voteManager;
    private $algorithmManager;


    public function __construct(ProductManager $productManager, VoteManager $voteManager, AlgorithmManager $algorithmManager)
    {
        $this->productManager = $productManager;
        $this->voteManager = $voteManager;
        $this->algorithmManager = $algorithmManager;

    }


    public function renderDefault(): void
    {
        $this->template->products = $this->productManager->getAll();
        $this->template->categories = $this->productManager->getCategories();
        /* $this->template->carts = $this->cartManager->getAll();*/
    }


    public function renderShow($name): void
    {

        $products = $this->productManager->getByName($name);
        $id = $products->product_id;
        $myRating = $this->voteManager->getMyRating($id, $this->user->id);
        $this->template->categories = $this->productManager->getCategories();
        $this->template->ratingCount = $this->voteManager->countRating($id);

        $this->template->recentlyViewed = $this->productManager->getAll();

        $this->template->similarProducts = $this->similarProducts($id);

        //dump($this->similarProducts($id));

        $this->template->products = $products;
        $this->template->myRating = $myRating;

        $section = $this->session->getSection('mySection');

        $id = $products->title;

        if (!isset($section->products[$id])){
            $section->products[$id] = array(
                'count' => 1,
            );
        } else{
            $section->products[$id]['count']++;
        }

        $section->products[$id]['time'] = date('d.m.Y H:i');

        if ($myRating === null){
            $section->products[$id]['rating'] = 'nehodnoceno';
        }else{
            $section->products[$id]['rating'] = $myRating->rating;
        }

        $section->products[$id]['type'] = $this->productManager->getByCategory($products->category)->full_name;

        arsort($section->products);
        //dump($section->products);

        $this->template->recentlyProducts = $section->products;

        $recentProduct = $section->products;
        $recentProduct = array_slice($recentProduct, 0 ,4);
        $this->template->recentProducts = $recentProduct;
    }


    public function renderCategory($category){
        $this->template->products = $this->productManager->getAll()->where('category', $category);
        $this->template->categories = $this->productManager->getCategories();
        $this->template->name = $this->productManager->getByCategory($category)->full_name;

    }

    /**
     * formulář pro hlasování
     * @return Form
     */
    protected function createComponentVoteForm()
    {
        $form = new Form;

        $votes=[1,2,3,4,5,6];

        $form->addRadioList('rating', 'Hlasování: ')
            ->setItems($votes);
        $form->addSubmit('send', 'Ohodnotit');


        $form->onSuccess[] = [$this, 'voteFormSucceeded'];
        return $form;
    }

    public function voteFormSucceeded(Form $form, $values)
    {
        $section = $this->session->getSection('mySection');
        if(!$this->getUser()->loggedIn){
            $this->flashMessage('Pro hlasování se přihlašte!', 'alert alert-danger');
            $this->redirect('Sign:signIn');

        }else{
            if($values->rating == null){
                $this->flashMessage('Nastala chyba.', 'alert alert-danger');
                $this->redirect('this');
            }else{
                $name = $this->getParameter('name');
                $productId = $this->productManager->getByName($name)->product_id;
                $userId = $this->getUser()->id;
                $vote = $this->voteManager->getMyRating($productId, $userId);
                if(!$vote){
                    $this->voteManager->insert($productId, $userId, $values->rating);
                    $this->flashMessage('Hlas byl vložen.', 'alert alert-success');
                    $section->products[$name]['count']--;
                    $this->redirect('this');

                }else{
                    $this->voteManager->update($productId, $userId, $values->rating);
                    $this->flashMessage('Hlas byl upraven.', 'alert alert-success');
                    $section->products[$name]['count']--;
                    $this->redirect('this');
                }
            }

        }
    }



    protected function createComponentBuy(){

        $form = new Form();

        $form->addInteger('count', 'Počet:')
            ->setDefaultValue(1);
        $form->addSubmit('send', 'Vložit do košíku');

        $form->onSuccess[] = [$this, 'buySucceeded'];
        return $form;
    }

    public function buySucceeded(Form $form, $values){

        $product = $this->getParameter('name');
        $this->handleAddToCart($product, $values->count);
        $this->flashMessage('Vloženo do košíku');
        $this->redirect('this');
    }


    public function handleAddToCart($productName, $count)
    {
        $market = $this->session->getSection('market');
        $section = $this->session->getSection('mySection');
        //$name = $this->productManager->getByName($productId)->title;
        $id = $this->productManager->getByName($productName)->product_id;

        //$id = $productId;

        if (!isset($market->products[$id])){
            $market->products[$id]['count'] = $count;
        } else{
            $market->products[$id]['count'] += $count;
        }

        $section->products[$productName]['count']--;
        //dump($market->products);
        //$this->flashMessage('Vloženo do košíku.');
        //$this->redirect('this');
    }

    public function similarProducts($id){

        $products = $this->productManager->getAll();
        $product = $this->productManager->getById($id);

        foreach ($products as $key => $item){
            if ($item->category != $product->category){
                unset($products[$key]);
            }
        }


        $tfidf[$product->title]['content'] = $product->content;

        // vytvoreni kolekce pro TF-IDF (produkt - popis)
        $collection = $this->algorithmManager->createCollection($products);

        // vypocteni TF a DF pro danou kolekci
        $index = $this->algorithmManager->getIndex($collection);

        //vypocet tf-idf
        $matchDocs = $this->algorithmManager->tfIdf($products, $index, $tfidf);

        //vypocet similarity
        $final = $this->algorithmManager->getFinal($matchDocs, $index);

        arsort($final); // high to low

        // vyber produktu s podobnosti vetsi nez 0
        $sortFinal = $this->algorithmManager->sortFinal($final, $products, $tfidf, 0);

        $sortFinal = array_slice($sortFinal,0,4);

        return $sortFinal;
    }


}