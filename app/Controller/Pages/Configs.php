<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Model\Entity\Host;


class Configs extends Page{

    /**
     * Método responsável por retornar o conteúdo (view) da página configurações
     * @return string
     */
    public static function getConfigs(){
        //HOSTS
        $obHost = new Host;
        //VIEW DA PÁGINA CONFIGURAÇÕES
        $content = View::render('pages/configs', [
            'name' => 'Configurações'
        ]);

        //RETORNA A VIEW DA PÁGINA
        return parent::getPage('SISBACKUP::Configurações', $content);
    }

}