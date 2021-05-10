<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 25.10.2020
 * Time: 7:15
 */

namespace App\Presenters;

use Nette;
use App\Model\ProductManager;
use App\Model\HistoryManager;


class CartPresenter extends Nette\Application\UI\Presenter
{

    private $productManager;
    private $historyManager;

    public function __construct(ProductManager $productManager, HistoryManager $historyManager)
    {
        $this->productManager = $productManager;
        $this->historyManager = $historyManager;
    }

    public function renderDefault(): void
    {
        $this->template->categories = $this->productManager->getCategories();
        $this->template->products = $this->productManager->getAll();

        $market = $this->session->getSection('market');

        $this->template->cart = $market->products;
        $this->template->cartCount = count($market->products);
    }

    public function handleDeleteFromCart($id){
        $market = $this->getSession()->getSection('market');

        unset($market->products[$id]);
    }

    public function handleBuy(){
        $market = $this->getSession()->getSection('market');
        $user = $this->user->getIdentity()->getId();
        $time = date('Y-m-d H:i:s');

        foreach ($market->products as $key => $item){
            $name = $this->productManager->getById($key)->title;
            $this->historyManager->insert($user, $name, $time);
            unset($market->products[$key]);
        }
        $this->flashMessage('Zboží zakoupeno.');
        $this->redirect("History:");
    }

}