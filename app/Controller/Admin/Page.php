<?php

namespace App\Controller\Admin;

use \App\Utils\View;

class Page{

    /**
     * Método responsável por retornar o conteúdo (view) da estrutura genérica de página do painel
     * @param string $title
     * @param string $content
     * @return string
     */
    public static function getPage($title,$content){
        return View::render('admin/page',[
            'title'     => $title,
            'content'   => $content
        ]);
    }

    /**
     * Método responsável por renderizar a view do menu do painel
     * @param string $currentModule
     * @return string
     */
    private static function getMenu($currentModule){
        //RETORNA A RENDERIZAÇÃO DO MENU
        return View::render('admin/menu/box',[

        ]);
    }

    /**
     * Método responsável por renderizar a view do painel com conteúdos dinâmicos
     * @param string $title
     * @param string $content
     * @param string $currentModule
     * @return string
     */
    public static function getPanel($title,$content,$currentModule){
        //RENDERIZA A VIEW DO PAINEL
        $contentPanel = View::render('admin/panel',[
            'menu' => self::getMenu($currentModule),
            'content' => $content
        ]);
        //RETORNA A PÁGINA RENDERIZADA
        return self::getPage($title,$contentPanel);
    }

}