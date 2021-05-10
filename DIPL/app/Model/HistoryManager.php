<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 10:31
 */

namespace App\Model;
use Nette;

class HistoryManager
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getAll(){

        return $this->database->table('history')
            ->select('*');

    }

    public function getAllView(){

        return $this->database->table('view_history');

    }

    public function getViewById($id){
        return $this->database->table('view_history')
            ->select('*')
            ->where('user_id', $id)
            ->fetchAll();
    }

    public function getProduct()
    {
        return $this->database->table('products');
    }

    public function getTime($user){

        return $this->database->query('SELECT DISTINCT buy_time FROM history WHERE user_id=? ORDER BY buy_time DESC', $user);
    }

    public function getTimeView($user){

        return $this->database->query('SELECT DISTINCT view_time FROM view_history WHERE user_id=? ORDER BY view_time DESC', $user);
    }

    public function getById($id) {
        $row = $this->database->table("history")
            ->where('user_id = ?', $id)
            ->fetch();
        return $row;
    }

    public function getHistoryById($id){
        return $this->database->table('history')
            ->select('*')
            ->where('user_id', $id)
            ->fetchAll();
    }

    public function insert($userId, $product, $time){

        $this->database->table('history')->insert([
            'user_id' => $userId,
            'product_id' => $product,
            'buy_time' => $time,
        ]);
    }

    public function insertView($userId, $product, $time, $rating, $count){

        $this->database->table('view_history')->insert([
            'user_id' => $userId,
            'product_id' => $product,
            'view_time' => $time,
            'rating' => $rating,
            'count' => $count,
        ]);
    }

    public function delete($time){

        return $this->database->query('DELETE FROM history WHERE user_id=?', $time);
    }

    public function deleteById($id){

        return $this->database->table('history')
            ->where('user_id', $id)
            ->delete();
    }


    public function saveView($view, $user){
        foreach ($view->products as $name => $data){
            if ($data['rating'] == 'nehodnoceno'){
                $rating = 0;
            }else{
                $rating = $data['rating'];
            }
            $this->insertView($user, $name, date('Y-m-d H:i:s',strtotime($data['time'])), $rating, $data['count']);
            unset($view->products[$name]);
        }
    }

}