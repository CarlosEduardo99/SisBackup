<?php

use \App\Http\Response;
use \App\Controller\Admin;

//ROTA DE HOSTS
$obRouter->get('/admin/jobs',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request){
        return new Response(200,Admin\Job::getJobs($request));
    }
]);

//ROTA DE CADASTRO DE JOBS (POST)
$obRouter->post('/admin/jobs',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request){
        return new Response(200,Admin\Job::setNewJob($request));
    }
]);

//ROTA DE EDIÇÃO DE UM JOB
$obRouter->get('/admin/jobs/{id}/edit',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request,$id){
        return new Response(200,Admin\Job::getEditJob($request,$id));
    }
]);

//ROTA DE EDIÇÃO DE UM JOB (POST)
$obRouter->post('/admin/jobs/{id}/edit',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request,$id){
        return new Response(200,Admin\Job::setEditJob($request,$id));
    }
]);

//ROTA DE EXCLUSÃO DE UM JOB
$obRouter->get('/admin/jobs/{id}/delete',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request,$id){
        return new Response(200,Admin\Job::getDeleteJob($request,$id));
    }
]);

//ROTA DE EXCLUSÃO DE UM JOB (POST)
$obRouter->post('/admin/jobs/{id}/delete',[
    'middlewares' => [
        'require-admin-login'
    ],
    function($request,$id){
        return new Response(200,Admin\Job::setDeleteJob($request,$id));
    }
]);