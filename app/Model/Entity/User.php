<?php

namespace App\Model\Entity;

use \WilliamCosta\DatabaseManager\Database;

class User{

    /**
     * ID do usuário
     * @var integer
     */
    public $id;

    /**
     * Nome do Usuário
     * @var string
     */
    public $name;

    /**
     * Login do usuário
     * @var string
     */
    public $login;

    /**
     * Hash da senha do usuário
     * @var string
     */
    public $pass;

    /**
     * Método responsável por retornar um usuário com base em seu login
     * @param string $login
     * @return User
     */
    public static function getUserByLogin($login){
        return (new Database('users'))->select('login = "'.$login.'"')->fetchObject(self::class);
    }
}