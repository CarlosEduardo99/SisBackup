<?php

use \App\Http\Response;
use \App\Controller\Admin;

//ROTA DE HOSTS
$obRouter->get('/admin/hosts',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request){
        return new Response(200,Admin\Host::getHost($request));
    }
]);

//ROTA DE CADASTRO DE HOSTS (POST)
$obRouter->post('/admin/hosts',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request){
        return new Response(200,Admin\Host::setNewHost($request));
    }
]);

//ROTA DE EDIÇÃO DE UM HOST
$obRouter->get('/admin/hosts/{id}/edit',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request,$id){
        return new Response(200,Admin\Host::getEditHost($request,$id));
    }
]);