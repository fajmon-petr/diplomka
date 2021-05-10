<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 10:43
 */

namespace App\Presenters;
use Nette;

use Nette\Utils\DateTime;
use Nette\Application\UI\Form;
use Nette\Application\UI\Control;

use App\Model\HistoryManager;
use App\Model\ProductManager;

class HistoryPresenter extends Nette\Application\UI\Presenter
{
    private $historyManager;
    private $productManager;

    public function __construct(HistoryManager $historyManager, ProductManager $productManager)
    {
        $this->historyManager = $historyManager;
        $this->productManager = $productManager;
    }

    public function renderDefault(): void {

        if($this->getUser()->loggedIn)
        {

            $user = $this->getUser()->id;

            $this->template->history = $this->historyManager->getAll()->where('user_id = ?', $user);
            $this->template->time = $this->historyManager->getTime($user);
            $this->template->categories = $this->productManager->getCategories();

        }

        $market = $this->session->getSection('market');
        $this->template->cartCount = count($market->products);
        // $this->template->products = $this->productManager->getAll();

    }

    protected function createComponentAddHistory()
    {
        $form = new Form();

        $productsFile = $this->historyManager->getProduct();
        //$products = $productsFile->fetchPairs('id', 'title');


        /*$form->addSelect('idp', 'Typ souboru:', $products)
            ->setPrompt('nevybráno')
            ->setRequired("Prosím vyberte produkt.");
        */
        $form->addText('rok', 'Rok/měsíc:')
            ->setType('Date');
        $form->addSubmit('send', 'Vygenerovat do historie');
        $form->onSuccess[] = [$this, 'addHistorySucceeded'];
        return $form;

    }

    public function addHistorySucceeded($form, $values)
    {

        $userId = $this->getUser()->id;
        //$idp = $values->idp;
        $time = $values->rok;
        for ($i = 0; $i < 10; $i++) {
            $idp = random_int(1, 250);
            $this->historyManager->insert($userId, $this->productManager->getById($idp)->title, $time);
        };

        $this->redirect('this');
    }

    public function handleDelete($time)
    {
        //    $tim = $time[0];
        $this->historyManager->delete($time);
        /*
              if($this->userManager->delete($id)==1) {
                        $this->flashMessage('Článek smazán.', 'alert alert-success');
                        $this->redirect('Homepage:');
                    }
              else{
                  $this->flashMessage('Nastala chyba.', 'alert alert-danger');
                  $this->redirect('Homepage:');
        */
    }
}