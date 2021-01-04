<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\ProductManager;
use App\Model\VoteManager;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{

    private $productManager;
    private $voteManager;

    public function __construct( ProductManager $productManager, VoteManager $voteManager)
    {

        $this->productManager = $productManager;
        $this->voteManager = $voteManager;
    }

    public function renderDefault(): void
    {
        $this->template->products = $this->productManager->getAll();
        $this->template->categories = $this->productManager->getCategories();

        $this->template->topProducts = $this->handleTopProducts();

    }

    public function handleTopProducts(){

        $products = $this->productManager->getAll();


        $topProducts = array();
        foreach ($products as $product){
            $topProducts[$product->product_id] = array(
                'count' => $this->voteManager->countRating($product->product_id),
                'rating' => $product->rating,
                'id' => $product->product_id,
            );
        }

        array_multisort($topProducts, SORT_DESC);

        $topProducts = array_slice($topProducts,0,20);
        return $topProducts;
    }
}
