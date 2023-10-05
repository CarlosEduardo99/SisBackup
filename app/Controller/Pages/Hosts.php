<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Model\Entity\Host as EntityHost;
USE \WilliamCosta\DatabaseManager\Pagination;

class Hosts extends Page{

    /**
     * Método responsável por obter a renderização dos itens de hosts para a página
     * @param Request $request
     * @param Pagination $obPagination
     * @return string
     */
    private static function getHostsItems($request,&$obPagination){
        //HOSTS
        $itens = '';

        //QUANTIDADE TOTAL DE REGISTROS
        $quantidadeTotal = EntityHost::getHosts(null,null,null,null,null,'COUNT(*) as qtd')->fetchObject()->qtd;

        //PÁGINA ATUAL
        $queryParams = $request->getQueryParams();
        $paginaAtual = $queryParams['page'] ?? 1;        

        //INSTANCIA DE PAGINAÇÃO
        $obPagination = new Pagination($quantidadeTotal,$paginaAtual,20);

        //RESULTADOS DA PÁGINA
        $results = EntityHost::getHosts(null,null,null,'id',$obPagination->getLimit());

        //RENDERIZA O ITEM
        while($obHost = $results->fetchObject(EntityHost::class)){
            //VIEW DA PÁGINA HOSTS
            $itens .= View::render('pages/host/item', [
                'hostName'          => $obHost->hostName,
                'hostIP'            => $obHost->hostIP,
                'hostDescription'   => $obHost->hostDescription,
                'hasDB'             => $obHost->hasDB ? $obHost->dbNames : 'Não há',
                'directory'         => $obHost->directory
            ]);
        }

        //RETORNA OS HOSTS
        return $itens;
    }

    /**
     * Método responsável por retornar o conteúdo (view) da página hosts
     * @param Request $request
     * @return string
     */
    public static function getHosts($request){
        
        //VIEW DA PÁGINA HOSTS
        $content = View::render('pages/hosts', [
            'itens' => self::getHostsItems($request,$obPagination),
            'pagination' => parent::getPagination($request,$obPagination)
        ]);

        //RETORNA A VIEW DA PÁGINA
        return parent::getPage('SISBACKUP::Hosts', $content);
    }

    /**
     * Método responsávelpor criar um host
     * @param Request $request
     * @return string
     */
    public static function insertHost($request){
        //DADOS DO POST
        $postVars = $request->getPostVars();

        //NOVA INSTANCIA DE JOB
        $obHost = new EntityHost;
        $obHost->hostName           = $postVars['hostName'];
        $obHost->hostUser           = $postVars['hostUser'];
        $obHost->hostIP             = $postVars['hostIP'];
        $obHost->hostPass           = $postVars['hostPass'];
        $obHost->hostDescription    = $postVars['hostDescription'];
        $obHost->hasDB              = $postVars['hasDB'];
        $obHost->dbNames            = $postVars['dbNames'];
        $obHost->directory          = $postVars['directory'];
        $obHost->cadastrar();
        
        //RETORNA A PÁGINA DE LISTAGEM DE HOSTS
        return self::getHosts($request);
    }

}