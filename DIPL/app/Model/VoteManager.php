<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 9:27
 */

namespace App\Model;
use MongoDB\BSON\MaxKey;
use Nette;

class VoteManager
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;

    }

    /**
     * ukládá nový hlas
     * @param $productId
     * @param $userId
     * @param $rating
     */
    public function insert($productId, $userId, $rating) {
        $this->database->table('vote')->insert([
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => $rating,
        ]);
        $this->updateRating($productId);
    }

    /**
     * upravuje hlas
     * @param $productId
     * @param $userId
     * @param $rating
     * @return int
     */
    public function update($productId, $userId, $rating) {
        $count = $this->database->table('vote')
            ->where('product_id', $productId)
            ->where('user_id', $userId)
            ->update([
                'rating' => $rating,
            ]);

        $this->updateRating($productId);
        return $count;
    }

    /**
     * vrací hlas uživatele u souboru
     * @param $productId
     * @param $userId
     * @return false|Nette\Database\Table\ActiveRow
     */
    public function getMyRating($productId, $userId) {
        return $this->database->table('vote')
            ->select('rating')
            ->where('product_id', $productId)
            ->where('user_id', $userId)
            ->fetch();
    }

    /**
     * aktualizuje rating souboru
     * @param $productId
     * @return int
     */
    public function updateRating($productId) {
        $sum = $this->database->table('vote')
            ->where('product_id', $productId)
            ->sum('rating');
        $count = $this->database->table('vote')
            ->where('product_id', $productId)
            ->count();

        if($count==0) $rating = null;
        else $rating = $sum/$count;

        $files = $this->database->table('products')
            ->where('product_id', $productId)
            ->update([
                'rating' => number_format($rating, 1),

            ]);
        return $files;
    }


    public function countRating($productId){
        return $this->database->table('vote')
            ->where('product_id', $productId)
            ->count();
    }

    public function countUserVote($user){
        return $this->database->table('vote')
            ->where('user_id', $user)
            ->count();
    }


    public function generateVote($product, $user){
        $this->database->table('vote')
            ->insert([
                'product_id' => $product,
                'user_id' => $user,
                'rating' => rand(3,5),
            ]);
    }


    public function getUserProductsVote($userId){
        return $this->database->table('vote')
            ->select('*')
            ->where('user_id = ?', $userId)
            ->fetchAll();
    }

    public function userProductsVote($i){
        $k = array();
        foreach ($i as $value){
            $b = $this->getUserProductsVote($value);
            foreach ($b as $c){
                $k[] = $c->product_id;
            }
        }
        return $k;
    }

    public function saveLike($productId, $user){
        return $this->database->table('likes')
            ->insert([
                'user_id' => $user,
                'product' => $productId,
            ]);
    }

    public function deleteLike($productId, $user){
        return $this->database->table('likes')
            ->where('user_id', $user)
            ->where('product', $productId)
            ->delete();
    }

    public function deleteVotes($user){
        return $this->database->table('vote')
            ->where('user_id', $user)
            ->delete();
    }

    public function likeExists($productId, $user){
        return $this->database->table('likes')
            ->select('*')
            ->where('user_id', $user)
            ->where('product', $productId)
            ->fetch();
    }

    public function voteExists($user){
        return $this->database->table('vote')
            ->select('*')
            ->where('user_id', $user)
            ->fetch();
    }

    public function likeUpdate($productId, $user, $action){
        return $this->database->table('likes')
            ->where('user_id', $user)
            ->where('product', $productId)
            ->update([
                'action' => $action
            ]);
    }

    public function getLikes($user){
        return $this->database->table('likes')
            ->select('*')
            ->where('user_id = ?', $user)
            ->fetchAll();
    }

    public function top100($product, $myCat){
        $top100 = array();
        if (in_array($product->category,$myCat)){
            $top100[$product->title] = array(
                'count' => $this->countRating($product->product_id),
                'rating' => number_format($product->rating, 1),
                'type' => $product->category,
            );
        }
        return $top100;
    }
}