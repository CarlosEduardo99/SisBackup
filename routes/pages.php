<?php

use \App\Http\Response;
use \App\Controller\Pages;

//ROTA HOME
$obRouter->get('/',[
    function(){
        return new Response(200,Pages\Home::getHome());
    }
]);

//ROTA HOSTS
$obRouter->get('/hosts',[
    function($request){
        return new Response(200,Pages\Hosts::getHosts($request));
    }
]);

//ROTA HOSTS (INSERT)
$obRouter->post('/hosts',[
    function($request){
        return new Response(200,Pages\Hosts::insertHost($request));
    }
]);

//ROTA JOBS
$obRouter->get('/jobs',[
    function(){
        return new Response(200,Pages\Jobs::getJobs());
    }
]);

//ROTA JOBS (INSERT)
$obRouter->post('/jobs',[
    function($request){
        return new Response(200,Pages\Jobs::insertJob($request));
    }
]);

//ROTA RESTORE
$obRouter->get('/restore',[
    function(){
        return new Response(200,Pages\Restore::getRestore());
    }
]);

//ROTA CONFIGURAÇÕES
$obRouter->get('/configs',[
    function(){
        return new Response(200,Pages\Configs::getConfigs());
    }
]);

//ROTA DINÂMICA -- EXEMPLO
/**
*$obRouter->get('/pagina/{idPagina}/{acao}',[
*    function($idPagina,$acao){
*        return new Response(200,'Página '.$idPagina.' - '.$acao);
*    }
*]);
*/