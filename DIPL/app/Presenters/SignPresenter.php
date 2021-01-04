<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 9:30
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model;
use App\Model\UserManager;
use App\Model\ProductManager;
use App\Model\HistoryManager;
use App\Model\VoteManager;

class SignPresenter extends Nette\Application\UI\Presenter
{
    private $productManager;
    private $userManager;
    private $historyManager;
    private $voteManager;

    public function __construct(UserManager $userManager, ProductManager $productManager, HistoryManager $historyManager, VoteManager $voteManager)
    {
        $this->userManager = $userManager;
        $this->productManager = $productManager;
        $this->historyManager = $historyManager;
        $this->voteManager = $voteManager;
    }

    public function renderIn(){
        $this->template->categories = $this->productManager->getCategories();
    }

    public function renderUp(){
        $this->template->categories = $this->productManager->getCategories();
    }

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->elementPrototype->novalidate = "novalidate";

        $form->addText('username', 'Uzivatleske jmeno:')
            ->setRequired('Vyplnte prosim uyivatelske jmeno.');

        $form->addPassword('password','Heslo:')
            ->setRequired('Vyplnte prosim heslo.');

        $form->addSubmit('send','Prihlasit');
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded($form,$values)
    {
        try{
            $this->getUser()->login($values->username, $values->password);
            $this->getUser()->setExpiration('1440 minutes');

            $session = $this->getSession();
            $sessionSection = $session->getSection('mySection');
            $sessionSection->userId = $values->username;
            $sessionSection->products = array();

            $sessionMarket = $session->getSection('market');
            $sessionMarket->userId = $values->username;
            $sessionMarket->products = array();

            $this->userManager->createSimilarityUsers($this->getUser()->id);
            $this->createMyTopProducts();


            $this->redirect('Homepage:');
            $this->flashMessage('Příhlášení proběhlo úspěšně');
        } catch (Nette\Security\AuthenticationException $e){
            $form->addError($e->getMessage());
        }
    }

    protected function createComponentSignUpForm()
    {
        $form = new Form;
        $form->elementPrototype->novalidate = "novalidate";

        $form->addText('username', 'Uzivatelske jmeno: ')
            ->setRequired('Vyplnte prosim uzivatelske jmeno.');

        $form->addEmail('email', 'Email: ')
            ->setRequired('Vyplnte prosim uzivatelsky email.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Vyplňte prosím heslo.')
            ->addRule(Form::MIN_LENGTH, "Heslo musí mít alespoň 6 znaků a musí obsahovat malé písmeno, velké písmeno a číslici.", 6)
            ->addRule(Form::PATTERN, "Heslo musí mít alespoň 6 znaků a musí obsahovat malé písmeno, velké písmeno a číslici.", '.*[A-Z].*')
            ->addRule(Form::PATTERN, "Heslo musí mít alespoň 6 znaků a musí obsahovat malé písmeno, velké písmeno a číslici.", '.*[a-z].*')
            ->addRule(Form::PATTERN, "Heslo musí mít alespoň 6 znaků a musí obsahovat malé písmeno, velké písmeno a číslici.", '.*[0-9].*');

        $form->addPassword('passwordVerify', 'Potvrzeni hesla')
            ->setRequired('Vyplnte prosim potvrzeni hesla.')
            ->addRule(Form::EQUAL, 'Vami vyplnena hesla se neshoduji', $form['password']);

        $form->addSubmit('send', 'Registrovat');
        $form->onSuccess[] = [$this, 'signUpFormSucceeded'];
        return $form;
    }

    public function signUpFormSucceeded($form, $values)
    {
        try{
            $this->userManager->insert($values);
            $this->flashMessage("Registrace byla uspesne provedena", 'alert alert-success');

        } catch (Model\DuplicateNameException $e){
            $form['email']->addError('Nespravne jmeno nebo heslo');
        }

        $this->getParameter('email');
        $this->getParameter('password');


        $this->redirect('Sign:in');
        //$this->redirect('Sign:in');
    }

    public function actionOut()
    {
        $view = $this->getSession()->getSection('mySection');
        $user = $this->user->getIdentity()->getId();
        $this->historyManager->saveView($view, $user);

        $this->getUser()->logout();
        $this->flashMessage('Odlaseni probehlo uspesne');
        $this->redirect('Homepage:');
    }

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
}