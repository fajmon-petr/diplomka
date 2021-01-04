<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 4.12.2020
 * Time: 11:38
 */

namespace App\Components;

use Nette;

class LikeControl extends Nette\Application\UI\Control
{
    private $product;
    private $rate;


    public function __construct($product)
    {
        $this->product = $product;
        //$this->rate = $rate;

    }

    public function render(){

        //$this->template->rating = $this->rate;
        $this->template->render(__DIR__ . '/like.latte');
    }

    public function handleLike()
    {
        $this->voteManager->likeUpdate($this->product->id, $this->presenter->user->id,1);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();
        } else {
            $this->presenter->redirect('this');
        }
    }

    public function handleUnlike()
    {
        $this->voteManager->likeUpdate($this->product->id, $this->presenter->user->id,0);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();
            // $this->redrawControl('article-' . $articleId); -- není potřeba
        } else {
            $this->presenter->redirect('this');
        }
    }
}