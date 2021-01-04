<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 9:26
 */


namespace App\Model;
use Nette;


class ProductManager
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getAll(){

        return $this->database->table('products')
            ->select('*');
    }

    public function getCategories(){

        return $this->database->table('categories')
            ->select('*');
    }

    public function getByCategory($name){
        return $this->database->table('categories')
            ->select('full_name')
            ->where('name = ?', $name)
            ->fetch();
    }

    public function getById($id){

        return $this->database->table('products')->get($id);
    }

    public function getByName($name){
        return $this->database->table('products')
            ->select('*')
            ->where('title = ?', $name)
            ->fetch();
    }

    public function getUserId($id){

        return $this->database->table('cart')->get($id);
    }

    public function insertHistory($userId, $productId){

        $this->database->table('history')->insert([
            'user_Id' => $userId,
            'product_id' => $productId,

        ]);
    }

    public function getMyCategory($category, $user) {
        return $this->database->table('user_category')
            ->select('category')
            ->where('category', $category)
            ->where('user_id', $user)
            ->fetch();
    }

    public function generateCategory($category, $user){
        $this->database->table('user_category')
            ->insert([
                'category' => $category,
                'user_id' => $user,
            ]);
    }

    public function generateRateProduct($product, $rating){
        $this->database->table('products')
            ->where('product_id = ?', $product)
            ->update([
                'rating' => $rating,
            ]);
    }

    public function getRateProduct($product){
        return $this->database->table('products')
            ->select('rating')
            ->where('product_id = ?', $product)
            ->fetch();
    }

    public function productRating($l){
        $x = array();
        foreach ($l as $m){
            $em = $this->getById($m);
            $x[$em->title] = $em->rating;
        }
        return $x;
    }


}