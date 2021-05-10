<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 9:26
 */

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
class UserManager
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function insert($values)
    {
        $passwords = new Passwords(PASSWORD_BCRYPT, ['cost' => 12]);
        Nette\Utils\Validators::assert($values->email, 'email');
        try{
            $this->database->table('users')->insert([
                'email' => $values->email,
                'password' => $passwords->hash($values->password),
                'username' => $values->username,
            ]);

        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }


    public function getAll(){
        return $this->database->table('users')
            ->select('*')
            ->fetchAll();
    }

    public function getById($id){
        return $this->database->table('users')
            ->select('*')
            ->where('user_id = ?', $id);
    }

    /**
     * vrací kategorie z db
     * @return Nette\Database\Table\Selection
     */
    public function getCategories() {
        return $this->database->table('categories')
            ->order('full_name');
    }

    /**
     * maže verzi hry u daného souboru
     * @param $fileId
     * @return int
     */
    public function deleteCategories($userId) {
        $count = $this->database->table('user_category')
            ->where('user_id', $userId)
            ->delete();
        return $count;
    }

    /**
     * vkládá verzi hry u daného souboru
     * @param $fileId
     * @param $game_versions
     */
    public function insertCategories($userId, $categories) {
        foreach ($categories as $category){
            $this->database->table('user_category')
                ->insert([
                    'user_id' => $userId,
                    'category' => $category,
                ]);
        }
    }

    public function deleteVote($userId) {
        $count = $this->database->table('vote')
            ->where('user_id', $userId)
            ->delete();
        return $count;
    }


    public function getUserCategories($id){

        return $this->database->table('user_category')
            ->select('category')
            ->where('user_id', $id)
            ->fetchAll();

    }

    public function insertCrewTop($crew){
        foreach ($crew as $user) {
            $u = $this->getById($user);
            $uc = $this->getUserCategories($user);

            foreach ($u as $l){
                foreach ($uc as $a) {
                    $this->database->table('crew_topproducts')
                        ->insert([
                            'user' => $l->user_id,
                            'product_id' => $a->category,
                        ]);
                }
            }
        }
    }


    /**
     * vraci pole topUseru
     * @param $id
     * @return array
     */
    public function getOtherUsersCategories($id){
        $userCategory = $this->database->table('user_category');

        $matrix = array();
        $similarity = array();

        foreach ($userCategory as $cat){
            $name = $cat->user_id;
            $matrix[$name][] = $cat->category;
        }

        $client = $matrix[$id];
//        $client = implode(" ", $client);

        foreach ($matrix as $userId => $user){
            foreach ($user as $value){
                if (in_array($value, $client)) {
                    if (!isset($similarity[$userId]['sim'])) {
                        $similarity[$userId]['sim'] = 1;
                    } else {
                        $similarity[$userId]['sim']++;
                    }
                }
                if (!isset($similarity[$userId]['count'])){
                    $similarity[$userId]['count'] = 1;
                }else{
                    $similarity[$userId]['count']++;
                }
            }
        }


        $finalSim = array();
        foreach ($similarity as $userId => $sim){
            if (isset($sim['sim'])){
                $procento = $sim['sim']/$sim['count'];
                $finalSim[$userId] = $procento*100;
            }
        }

        arsort($finalSim);

        $topUser = array();
        foreach ($finalSim as $key => $rating) {
            if ($key != $id){
                if ($rating >= 50){
                    $topUser[] = $key;
                }
            }
        }

        return $topUser;
    }

    public function getCrew($user){
        return $this->database->table('crew')
            ->select('*')
            ->where('user = ? ', $user)
            ->fetchAll();
    }

    public function createUserCategory($id){
        return $this->database->table('user_category')
            ->insert([
                'user_id' => $id,
                'category' => 'none',
            ]);
    }

    public function getUserId($name, $email){
        return $this->database->table('users')
            ->select('user_id')
            ->where('username = ?', $name)
            ->where('email = ?', $email)
            ->fetch();
    }


    public function deleteSimilarUserCategories($user){
        return $this->database->table('crew')
            ->where('user = ?', $user)
            ->delete();
    }

    public function insertSimilarUsersCategories($user, $users_ids){
        foreach ($users_ids as $user_id) {
            $this->database->table('crew')
                ->insert([
                   'user' => $user,
                   'similar_user' => $user_id,
                ]);
        }
    }

    public function getSimilarUserCategories($user){
        return $this->database->table('crew')
            ->select('*')
            ->where('user = ?', $user)
            ->fetchAll();
    }

    public function insertTopProducts($user, $products){
        foreach ($products as $product){
            $this->database->table('crew_topproducts')
                ->insert([
                    'user' => $user,
                    'product_id' => $product['id'],
                ]);
        }
    }

    public function deleteTopProducts($user){
        return $this->database->table('crew_topproducts')
            ->where('user = ?', $user)
            ->delete();
    }

    public function getTopProducts($user){
        return $this->database->table('crew_topproducts')
            ->select('*')
            ->where('user = ?', $user);
    }

    public function createSimilarityUsers($user){

        $users_ids = $this->getOtherUsersCategories($user);

        $this->deleteSimilarUserCategories($user);
        $this->insertSimilarUsersCategories($user, $users_ids);
    }


    public function userLikeExists($user){
        return $this->database->table('likes')
            ->select('*')
            ->where('user_id', $user)
            ->fetch();
    }

//    public function getCountLikes($user, $access, $party){
//        return $this->database->table('likes')
//            ->select('*')
//            ->where('user_id', $user)
//            ->where('access', $access)
//            ->where('party', $party)
//            ->count();
//    }
//
//    public function getCountLikesNerelevant($user, $access, $party){
//        return $this->database->table('likes')
//            ->select('*')
//            ->where('user_id', $user)
//            ->where('access', $access)
//            ->where('party', $party)
//            ->where('action', 0)
//            ->count();
//    }

    public function getAllLikes($user, $access, $party){
        return $this->database->table('likes')
            ->select('*')
            ->where('user_id', $user)
            ->where('access', $access)
            ->where('party', $party);
    }

    public function getTime(){
        return $this->database->table('likes')
            ->select('DISTINCT time');
    }


    public function time(){
        $times = $this->getTime();
        foreach ($times as $time){
            $date[] = date_format($time->time, 'Y.m.d');
        }
        return $date;
    }

    public function results($id, $access, $party){

        $date = $this->time();

        $group = $this->getAllLikes($id, $access, $party);

        $pokus = array();
        foreach ($date as $datum){
            $count = 0;
            $nerel = 0;
            foreach ($group as $item){
                if ($datum == date_format($item->time, 'Y.m.d')){
                    $pokus[$datum]['group'] = $party;
                    $pokus[$datum]['count'] = ++$count;
                    if ($item->action == 0){
                        $nerel++;
                        $pokus[$datum]['nerel'] = $nerel;
                    }else{
                        $pokus[$datum]['nerel'] = $nerel;
                    }

                }
            }
        }

        return $pokus;

    }


    public function getTopUsersCategories($users){
        foreach ($users as $user){
            $categories = $this->database->table('user_category')
                ->select('category')
                ->where('user_id', $user)
                ->fetchAll();
            $allCat[] = $categories;
        }

        $a = [];
        foreach ($allCat as $cat){
            foreach ($cat as $c){
                if (!array_key_exists($c->category, $a)){
                    $a[$c->category] = 1;
                }else{
                    $a[$c->category] += 1;
                }

            }
        }
        arsort($a);
        return $allCat;

    }

}