<?php
/**
 * Created by PhpStorm.
 * User: Fajmy
 * Date: 14.10.2020
 * Time: 9:25
 */

namespace App\Model;

use Nette\Security as NS;
use Nette;

class MyAuthenticator implements NS\IAuthenticator
{
    public $database;
    private $passwords;

    function __construct(Nette\Database\Context $database, NS\Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    function authenticate(array $credentials):NS\IIdentity{
        [$username, $password] = $credentials;

        $row = $this->database->table('users')
            ->where('username', $username)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('Neplatný uživatel.');
        }

        if (!$this->passwords->verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Neplatné heslo.');
        }

        return new NS\Identity($row->user_id, [],['username' => $row->username, 'email' => $row->email] );

    }
}