<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 9:28
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserManager;
use App\Model\ProductManager;
use App\Model\VoteManager;
class UserPresenter extends Nette\Application\UI\Presenter
{
    private $userManager;
    private $productManager;
    private $voteManager;

    public function __construct(UserManager $userManager, ProductManager $productManager, VoteManager $voteManager)
    {
        $this->userManager = $userManager;
        $this->productManager = $productManager;
        $this->voteManager = $voteManager;
    }

    public function renderDefault(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        $this->template->userCategories = $this->userManager->getUserCategories($this->getUser()->id);

        $this->template->title = $this->productManager->getCategories();


        //$this->userManager->getOtherUsersCategories($this->getUser()->id);
        //$this->voteManager->getUserProductsVote();
        //$this->handleSimilarity();
        //$section = $this->session->getSection('mySection');
       // dump($section->userId);
    }


    /**
     * vraci podobne uzivatele
     * @return array
     */

    public function createSimilarityUsers():void{

        $user = $this->getUser()->id;
        $this->userManager->createSimilarityUsers($user);
    }

    /**
     * vraci matici top produktu z me skupiny
     * @return array
     *
     */

    public function createTopProducts(): void{

        $user = $this->getUser()->id;

        $crew = $this->userManager->getCrew($this->getUser()->id);

        $j = array();

        foreach ($crew as $key => $value){
            $j[] = $value->similar_user;
        }

        $products = $this->voteManager->userProductsVote($j);
        $products = array_unique($products);


        $name = array();
        $rating = array();
        foreach ($products as $id){
            $product = $this->productManager->getById($id);

            $name[] = $product->title;
            $rating[$product->title] = array(
                'id' => $id,
                'count' => $this->voteManager->countRating($product->product_id),
                'rating' => number_format($product->rating, 1),
                'type' => $product->category,
            );
        }

        array_multisort($rating, SORT_DESC);

        $rating = array_slice($rating,0,100);

        $this->userManager->deleteTopProducts($user);
        $this->userManager->insertTopProducts($user, $rating);

    }

    protected function createComponentCategoryForm()
    {
        $form = new Form();

        $productTypes = $this->userManager->getCategories();
        $category = $productTypes->fetchPairs('name', 'full_name');

        $form->addCheckboxList('category','Kategorie:', $category);
        $form->addSubmit('send', 'Upravit');
        $form->onSuccess[] = [$this, 'categoryFormSucceeded'];
        return $form;

    }

    public function categoryFormSucceeded(Form $form, $values)
    {

        $userId = $this->getUser()->id;

        $this->userManager->deleteCategories($userId);
        $this->userManager->insertCategories($userId, $values->category);

        $this->flashMessage("Kategorie byly aktualizovany.",'alert alert-success');

        $this->createSimilarityUsers();
        $this->createTopProducts();

        $this->redirect('User:');
    }

//    public function handleSimilarity(){
//
//        $us = $this->userManager->getOtherUsersCategories($this->getUser()->id);
//
//        $i = $this->userManager->similarUserId($us);
//        //dump($i);
//
//        $k = $this->voteManager->userProductsVote($i);
//        //dump($k);
//
//        //$l = array_unique($k);
//        //sort($l);
//
//        //$x = array();
//        $x = $this->productManager->productRating($k);
//
//        arsort($x);
//        $x = array_slice($x,0, 10);
//        foreach ($x as $product => $value){
//            echo $product."</br>";
//        }
//        //$this->voteManager->getUserProductsVote();
//    }


    protected function createComponentGenerateVote(){

        $form = new Form;

        $form->addSubmit('send', 'Vygenerovat vote');

        $form->onSuccess[] = [$this, 'generateVoteSucceeded'];
        return $form;
    }

    public function generateVoteSucceeded(){

        if(!$this->getUser()->loggedIn){
            $this->flashMessage('Pro hlasování se přihlašte!', 'alert alert-danger');
            $this->redirect('Sign:signIn');

        }else{
            $users = $this->userManager->getAll();
            $products = $this->productManager->getAll();

            foreach ($users as $user){

                $this->userManager->deleteVote($user->user_id);

                $uCategories = $this->userManager->getUserCategories($user->user_id);

                $categor = array();
                foreach ($uCategories as $cat){
                    $a = $user->username;
                    $categor[$a][] = $cat->category;
                }


                foreach ($categor as $c => $value){
                    foreach ($value as $item) {
                        foreach ($products as $product){
                            if ($product->category === $item){

                                $vote = $this->voteManager->getMyRating($product->product_id, $user->user_id);
                                if (!$vote){
                                    $this->voteManager->generateVote($product->product_id, $user->user_id);
                                }
                            }
                        }
                    }

                }
            }
            $this->flashMessage('Hlasovani vygenerovano');
        }
    }

    protected function createComponentGenerateCategory()
    {
        $form = new Form;

        $form->addSubmit('send', 'Vygenerovat category');

        $form->onSuccess[] = [$this, 'generateCategorySucceeded'];
        return $form;
    }


    public function generateCategorySucceeded(){

        if(!$this->getUser()->loggedIn){
            $this->flashMessage('Pro hlasování se přihlašte!', 'alert alert-danger');
            $this->redirect('Sign:signIn');

        }else{
            $users = $this->userManager->getAll();
            $categories = $this->productManager->getCategories();


            foreach ($users as $user){

                $categor = array();

                foreach ($categories as $category){
                    $categor[] = $category->name;
                }

                $rand = rand(2,5);
                shuffle($categor);

                for ($i = 0; $i < $rand; $i++){
                    $generate = $this->productManager->getMyCategory($categor[$i], $user->user_id);
                    if (!$generate){
                        $this->productManager->generateCategory($categor[$i], $user->user_id);
                    }

                }

            }
            $this->flashMessage('Hlasovani vygenerovano');
        }
    }

    protected function createComponentGenerateRate()
    {
        $form = new Form;

        $form->addSubmit('send', 'Vygenerovat product rate');

        $form->onSuccess[] = [$this, 'generateRateSucceeded'];
        return $form;
    }

    public function generateRateSucceeded(){

        if(!$this->getUser()->loggedIn){
            $this->flashMessage('Pro hlasování se přihlašte!', 'alert alert-danger');
            $this->redirect('Sign:signIn');

        }else {
            $products = $this->productManager->getAll();

            foreach ($products as $product) {
                $generate = $this->productManager->getRateProduct($product->product_id);
                if ($generate->rating === NULL){
                    $this->productManager->generateRateProduct($product->product_id, rand(30,50)/10);
                }
            }
            $this->flashMessage('Hlasovani vygenerovano');
        }
    }
}