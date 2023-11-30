<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Model\Entity\Host;

class Restore extends Page{

    /**
     * Método responsável por retornar o conteúdo (view) da página restore
     * @return string
     */
    public static function getRestore(){
        //HOSTS
        $obHost = new Host;
        //VIEW DA PÁGINA RESTORE
        $content = View::render('pages/restore', [
            'name'          => $obHost->name,
            'description'   => $obHost->description
        ]);

        //RETORNA A VIEW DA PÁGINA
        return parent::getPage('SISBACKUP::Restore', $content);
    }

}