<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\User;
use \App\Session\Admin\Login as SessionAdminLogin;

class Login extends Page{

    /**
     * Método responsável por retornar a renderização da página de login
     * @param Request $request
     * @param string $errorMessage
     * @return string
     */
    public static function getLogin($request,$errorMessage = null){
        //STATUS
        $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : '';

        //CONTEÚDO DA PÁGINA DE LOGIN
        $content = View::render('admin/login',[
            'status' => $status
        ]);

        //RETORNA A PÁGINA COMPLETA
        return parent::getPage('SISBACKUP::Login', $content);
    }

    /**
     * Método responsável por definir o login do usuário
     * @param Request $request
     */
    public static function setLogin($request){
        //POST VARS
        $postVars   = $request->getPostVars();
        $login      = $postVars['user'] ?? '';
        $pass       = $postVars['pass'] ?? '';

        //BUSCA O USUÁRIO PELO LOGIN
        $obUser = User::getUserByLogin($login);
        if(!$obUser instanceof User){
            return self::getLogin($request,'Usuário e/ou senha inválidos!');
        }

        //VERIFICA A SENHA DO USUÁRIO
        if(!password_verify($pass,$obUser->pass)){
            return self::getLogin($request,'Usuário e/ou senha inválidos!');
        }

        //CRIA A SESSÃO DE LOGIN
        SessionAdminLogin::login($obUser);

        //REDIRECIONA O USUÁRIO PARA A HOME DO ADMIN
        $request->getRouter()->redirect('/admin');
    }

    /**
     * Método responsável por deslogar o usuário
     * @param Request $request
     */
    public static function setLogout($request){

        //DESTRÓI A SESSÃO DE LOGIN
        SessionAdminLogin::logout();

        //REDIRECIONA O USUÁRIO PARA A TELA DE LOGIN
        $request->getRouter()->redirect('/admin/login');
    }
}