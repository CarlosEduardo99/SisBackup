<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\Job as EntityJob;
use \App\Model\Entity\Host as EntityHost;
use \WilliamCosta\DatabaseManager\Pagination;

class Job extends Page{

    /**
     * Método responsável por obter a renderização dos itens de hosts para a página
     * @param Request $request
     * @param Pagination $obPagination
     * @return string
     */
    private static function getJobsItems($request,&$obPagination){
        //HOSTS
        $itens = '';

        //QUANTIDADE TOTAL DE REGISTROS
        $quantidadeTotal = EntityJob::getJobs(null,null,null,null,null,'COUNT(*) as qtd')->fetchObject()->qtd;

        //PÁGINA ATUAL
        $queryParams = $request->getQueryParams();
        $paginaAtual = $queryParams['page'] ?? 1;        

        //INSTANCIA DE PAGINAÇÃO
        $obPagination = new Pagination($quantidadeTotal,$paginaAtual,20);

        //RESULTADOS DA PÁGINA
        $results = EntityJob::getJobs(null,null,null,'id',$obPagination->getLimit());
        
        //RENDERIZA O ITEM
        while($obJob = $results->fetchObject(EntityJob::class)){

            //VIEW DA PÁGINA JOBS
            $itens .= View::render('admin/modules/jobs/item', [
                'id'                => $obJob->id,
                'jobName'           => $obJob->jobName,
                'jobType'           => $obJob->jobType == '1' ? 'Incremental' : 'Full',
                'jobProgram'        => EntityJob::getProgram($obJob->jobRecurrence, $obJob->jobProgram),
                'jobTime'           => 'às '.$obJob->jobTime,
                'jobDescription'    => $obJob->jobDescription,
                'jobRetention'      => $obJob->jobRetention > 1 ? $obJob->jobRetention.' últimos' : $obJob->jobRetention
            ]);
        }

        //RETORNA OS JOBS
        return $itens;
    }

    /**
     * Método responsável por renderizar a view de listagem de jobs
     * @param Request $request
     * @return string
     */
    public static function getJobs($request){
        //OBTEM HOST ID E HOSTNAME
        $selectHosts = ''; 

        $hosts = EntityHost::getHosts(null,null,null,'id', null, 'id,hostName');

        while($obHost = $hosts->fetchObject(EntityJob::class)){
            //VIEW DA PÁGINA JOBS
            $selectHosts .= '<option value="'.$obHost->id.'">'.$obHost->hostName.'</option>';
        }

        //CONTEÚDO DA PÁGINA JOBS
        $content = View::render('admin/modules/jobs/index',[
            'itens'         => !empty(self::getJobsItems($request,$obPagination)) ? 
                            self::getJobsItems($request,$obPagination) : 
                            '<h4 class="text-center mt-3">Não existem Jobs de Backup configurados<h4>',
            'pagination'    => parent::getPagination($request,$obPagination),
            'modalNew'     => View::render('admin/modules/jobs/modal/newjob',[
                'title' => 'Novo Job'
            ]),
            'select'        => $selectHosts,
            'modalEdit'     => '',
            'status'        => self::getStatus($request)
        ]);

        //RETORNA A PÁGINA COMPLETA
        return parent::getPanel('SISBACKUP::Jobs', $content,'jobs');
    }
    
     /**
     * Método responsávelpor criar um host
     * @param Request $request
     * @return string
     */
    public static function setNewJob($request){
        //DADOS DO POST
        $postVars = $request->getPostVars();

        //NOVA INSTANCIA DE JOB
        $obJob = new EntityJob;
        $obJob->jobName         = $postVars['jobName'];
        $obJob->jobType         = $postVars['jobType'];
        $obJob->jobRecurrence   = $postVars['jobRecurrence'];
        $obJob->jobProgram      = $postVars['jobProgram'];
        $obJob->jobTime         = $postVars['jobTime'];
        $obJob->hostID          = $postVars['hostID'];
        $obJob->jobDescription  = $postVars['jobDescription'];
        $obJob->jobRetention    = $postVars['jobRetention'];
        $obJob->cadastrar();
        
        //REDIRECIONA O USUÁRIO
        $request->getRouter()->redirect('/admin/jobs?status=created');
    }

    /**
     * Método responsável por retornar a mensagem de estaus
     * @param Request $request
     * @return string
     */
    private static function getStatus($request){
        //QUERY PARAMS
        $queryParams = $request->getQueryParams();

        //STATUS
        if(!isset($queryParams['status'])) return '';

        //MENSAGENS DE STATUS
        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('Job criado com sucesso!');
                break;
            case 'edited':
                return Alert::getSuccess('Job editado com sucesso!');
                break;
            case 'deleted':
                return Alert::getSuccess('Job exluído com sucesso!');
                break;
        }
    }

     /**
     * Método responsável por retornar o formulário de edição de um host
     * @param Request $request
     * @param integer $id
     * @return string
     */
    public static function getEditJob($request,$id){
        //OBTÉM O HOST DO BANCO DE DADOS
        $obJob = EntityJob::getJobById($id);

        //VALIDA A INSTÂNCIA
        if(!$obJob instanceof EntityJob){
            $request->getRouter()->redirect('/admin/jobs');
        }

        //CONFIGURA OS DIRETÓRIOS
        $directories = '';

        $dirs = explode('<br>', $obJob->directory);

        foreach($dirs as $directory) {
            str_replace(' ', '',$directory);
            $directories .=<<<DIR
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="/caminho/do/diretorio/..." name="directory[]" value="$directory">
                        <a href="javascript:void(0);" class="remove_button" title="Remover diretório">
                            <button class="btn btn-outline-danger" type="button" id="button-addon2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-folder-minus" viewBox="0 0 16 16">
                                <path d="m.5 3 .04.87a1.99 1.99 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14H9v-1H2.826a1 1 0 0 1-.995-.91l-.637-7A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09L14.54 8h1.005l.256-2.819A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2zm5.672-1a1 1 0 0 1 .707.293L7.586 3H2.19c-.24 0-.47.042-.683.12L1.5 2.98a1 1 0 0 1 1-.98h3.672z"/>
                                    <path d="M11 11.5a.5.5 0 0 1 .5-.5h4a.5.5 0 1 1 0 1h-4a.5.5 0 0 1-.5-.5z"/>
                                </svg>
                            </button>
                        </a>
                    </div>
                DIR;
        }
        
        //CONTEÚDO DA HOME
        $content = View::render('admin/modules/jobs/index',[
            'itens'         => self::getJobsItems($request,$obPagination),
            'pagination'    => parent::getPagination($request,$obPagination),
            'modalEdit'     => View::render('admin/modules/jobs/modal/editjob',[
                'title' => 'Editar Job'
            ]),
            'hostName'          => $obHost->hostName,
            'hostUser'          => $obHost->hostUser,
            'hostPass'          => $obHost->hostPass,
            'hostIP'            => $obHost->hostIP,
            'hostDescription'   => $obHost->hostDescription,
            'selected'          => $obHost->hasDB == 'True' ? 'selected' : '',
            'dbNames'           => $obHost->dbNames,
            'directory'         => $directories ?? '',
            'modalNew'      => View::render('admin/modules/jobs/modal/newjob',[
                'title'     => 'Novo Job'
            ]),
            'status'            => self::getStatus($request)
        ]);

        //RETORNA A PÁGINA COMPLETA
        return parent::getPanel('SISBACKUP::Jobs', $content,'jobs');
        
    }

    /**
     * Método responsável por gravar a atualização de um host
     * @param Request $request
     * @param integer $id
     * @return string
     */
    public static function setEditJob($request,$id){
        //OBTÉM O HOST DO BANCO DE DADOS
        $obJob = EntityJob::getJobById($id);

        //VALIDA A INSTÂNCIA
        if(!$obJob instanceof EntityJob){
            $request->getRouter()->redirect('/admin/jobs');
        }
    
        //POST VARS
        $postVars = $request->getPostVars();

        //ATUALIZA A INSTÂNCIA
        $obHost->hostName           = $postVars['hostName'] ?? $obHost->hostName; 
        $obHost->hostUser           = $postVars['hostUser'] ?? $obHost->hostUser;
        $obHost->hostIP             = $postVars['hostIP'] ?? $obHost->hostIP;
        $obHost->hostPass           = $postVars['hostPass'] ?? $obHost->hostPass;
        $obHost->hostDescription    = $postVars['hostDescription'] ?? $obHost->hostDescription;
        $obHost->hasDB              = $postVars['hasDB'] ?? $obHost->hasDB;
        $obHost->dbNames            = $postVars['dbNames'] ?? $obHost->dbNames;
        $obHost->directory          = $postVars['directory'] ?? $obHost->directory;
        $obHost->atualizar();

        //REDIRECIONA O USUÁRIO
        $request->getRouter()->redirect('/admin/jobs/'.$obJob->id.'/edit?status=edited');        
    
    }

    /**
     * Método responsável por renderizar página de exclusão de um host
     * @param Request $request
     * @param integer $id
     * @return string
     */
    public static function getDeleteJob($request,$id){
        //OBTÉM O JOB DO BANCO DE DADOS
        $obJob = EntityJob::getJobById($id);

        //OBTÉM O HOST ASSOCIADO
        $obHost = EntityHost::getHostById($obJob->hostID);

        //VALIDA A INSTÂNCIA
        if(!$obJob instanceof EntityJob){
            $request->getRouter()->redirect('/admin/jobs');
        }

         //CONTEÚDO DA PÁGINA JOBS
        $content = View::render('admin/modules/jobs/delete',[
            'jobName' => $obJob->jobName,
            'jobType'           => $obJob->jobType == '1' ? 'Incremental' : 'Full',
            'jobProgram'        => EntityJob::getProgram($obJob->jobRecurrence, $obJob->jobProgram),
            'jobTime'           => 'às '.$obJob->jobTime,
            'jobDescription'    => $obJob->jobDescription,
            'jobRetention'      => $obJob->jobRetention > 1 ? $obJob->jobRetention.' últimos' : $obJob->jobRetention,
            'host'              => $obHost->hostName,
            'IP'                => $obHost->hostIP
        ]);

        //RETORNA A PÁGINA COMPLETA
        return parent::getPanel('SISBACKUP::Jobs', $content,'jobs');

    }
    
    /**
     * Método responsável por excluir um host
     * @param Request $request
     * @param integer $id
     * @return string
     */
    public static function setDeleteJob($request,$id){
        //OBTÉM O HOST DO BANCO DE DADOS
        $obJob = EntityJob::getJobById($id);

        //VALIDA A INSTÂNCIA
        if(!$obJob instanceof EntityJob){
            $request->getRouter()->redirect('/admin/jobs');
        }
    
        //POST VARS
        $postVars = $request->getPostVars();

        //VALIDAÇÃO DA AÇÃO
        if(isset($postVars['action']) && $postVars['action'] == 'delete'){
            //EXCLUI O HOST
            $obJob->excluir();

            //REDIRECIONA O USUÁRIO
            $request->getRouter()->redirect('/admin/jobs?status=deleted'); 
        }else{
            //REDIRECIONA O USUÁRIO
            $request->getRouter()->redirect('/admin/jobs?status=erro'); 
        }      
    
    }
    
}