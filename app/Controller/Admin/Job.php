<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\Job as EntityJob;
use \App\Model\Entity\Host as EntityHost;
use \App\Model\Entity\Relation as EntityRelation;
use WilliamCosta\DatabaseManager\Database;
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
            'jobs'          => !empty(self::getJobsItems($request,$obPagination)) ? '' : 'd-none',
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
        
        //Job Type
        $jobType = [
            'inc' => '',
            'full' => ''
        ];

        if($obJob->jobType == '1'){
            $jobType['inc'] = 'selected';
        }else{
            $jobType['full'] = 'selected';
        }

        //Job Recurrence
        $jobRecurrence = [
            '1' => '',
            '2' => '',
            '3' => '',
            '4' => '',
            '5' => ''
        ];
        foreach($jobRecurrence as $key => $value){
            
            if($obJob->jobRecurrence == $key){
                $jobRecurrence[$key] = 'selected';
            }
        }

        //Job Date
        $jobDate = self::setJobProgram($obJob->jobProgram, $obJob->jobRecurrence);

        //Job Time
        $jobTime = explode(':', $obJob->jobTime);



        //OBTEM HOST ID E HOSTNAME
        $hostID = EntityRelation::getRelationHost($id);
        
        $selectHosts = ''; 

        $hosts = EntityHost::getHosts(null,null,null,'id', null, 'id,hostName');

        while($obHost = $hosts->fetchObject(EntityJob::class)){
            //VIEW DA PÁGINA JOBS
            $select = '';
            if($obHost->id == $hostID->id){
                $select = 'selected';
            }
            $selectHosts .= '<option value="'.$obHost->id.'"'.$select.'>'.$obHost->hostName.'</option>';
        }

        //CONTEÚDO DA HOME
        $content = View::render('admin/modules/jobs/index',[
            'itens'         => self::getJobsItems($request,$obPagination),
            'pagination'    => parent::getPagination($request,$obPagination),
            'modalEdit'     => View::render('admin/modules/jobs/modal/editjob',[
                'title' => 'Editar Job',
                'Date'  => $jobDate,
                'select'=> $selectHosts
            ]),
            'jobName'           => $obJob->jobName,
            'inc'               => $jobType['inc'],
            'full'              => $jobType['full'],
            'diario'            => $jobRecurrence['1'],
            'semanal'           => $jobRecurrence['2'],
            'mensal'            => $jobRecurrence['3'],
            'semestral'         => $jobRecurrence['4'],
            'anual'             => $jobRecurrence['5'],
            'jobProgram'        => $obJob->jobProgram,
            'hora'              => $jobTime[0],
            'minutos'           => $jobTime[1],
            'hostID'            => $obJob->hostID,
            'jobDescription'    => $obJob->jobDescription,
            'jobRetention'      => $obJob->jobRetention,
            'modalNew'          => View::render('admin/modules/jobs/modal/newjob',[
                'title' => 'Novo Job',
                'select' => $selectHosts
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
        $obJob->jobName         = $postVars['jobName'] ?? $obJob->jobName; 
        $obJob->jobType         = $postVars['jobType'] ?? $obJob->jobType;
        $obJob->jobRecurrence   = $postVars['jobRecurrence'] ?? $obJob->jobRecurrence;
        $obJob->jobProgram      = $postVars['jobProgram'] ?? $obJob->jobProgram;
        $obJob->jobTime         = $postVars['jobTime'] ?? $obJob->jobTime;
        $obJob->hostID          = $postVars['hostID'] ?? $obJob->hostID;
        $obJob->jobDescription  = $postVars['jobDescription'] ?? $obJob->jobDescription;
        $obJob->jobRetention    = $postVars['jobRetention'] ?? $obJob->jobRetention;
        $obJob->atualizar();

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
        $obHost = EntityRelation::getRelationHost($id);
        

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

    public static function setJobProgram($program, $type){

        $diasDaSemana = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado'
        ];

        $mesesDoAno = [
            'Jan' => 'Janeiro',
            'Feb' => 'Fevereiro',
            'Mar' => 'Março',
            'Apr' => 'Abril',
            'Mai' => 'Maio',
            'Jun' => 'Junho',
            'Jul' => 'Julho',
            'Aug' => 'Agosto',
            'Sep' => 'Setembro',
            'Oct' => 'Outubro',
            'Nov' => 'Novembro',
            'Dec' => 'Dezembro'
        ];

        switch($type){
            case '1':
                $options = '<label for="jobProgram" class="col-sm-4 col-form-label">Programação</label>
                <div class="col-sm-8">';

                for($i = 0; $i < 7; $i++){

                    $arr = explode(',', $program);
                    $check = '';
                    
                    if(in_array($i, $arr, false)){
                        $check = 'checked';
                    }
                    
                    $options .= '<div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" id="checkbox0" name="jobProgram[days][]" value="'.$i.'"'.$check.'>
                    <label for="checkbox1" class="form-check-label">'.$diasDaSemana[$i].'</label></div>';
                }
                
                $options .= '</div>';
                
                return $options;
                break;
            
            case '2':
                $options = '<label for="jobProgram" class="col-sm-4 col-form-label">Selecione o dia</label>
                <div class="col-sm-8">
                    <select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[weekly]">
                        <option>Selecione...</option>';
                        
                for($i = 0; $i < 7; $i++){

                    $check = '';

                    if($program[0] == $i){
                        $check = 'selected';
                    }
                    
                    $options .= '<option value="'.$i.'"'.$check.'>'.$diasDaSemana[$i].'</option>';
                }
				
                $options .= '</select></div>';
                
                return $options;
                break;

            case '3':
                $options = '<label for="jobProgram" class="col-sm-4 col-form-label">Selecione o dia</label>
                <div class="col-sm-8">
                    <select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[monthly]">
                    <option value="0">Selecione...</option>';
                
                for($i = 0; $i <= 31; $i++){
                    $check = '';
                    
                    if($program == $i){
                        $check = 'selected';
                    }
                    
                    $options .= '<option value="'.$i.'"'.$check.'>'.$i.'</option>';
                }
                
                $options .= '</select></div>';
                
                return $options;
                break;
            
            case '4':
                $program = explode('/', $program);
                $options = '<label for="jobProgram" class="col-sm-4 col-form-label">Selecione o dia</label>
                <div class="col-sm-4">
                    <select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[semi-annual][day]">
                    <option value="0">Selecione...</option>';
                
                for($i = 0; $i <= 31; $i++){
                    $check = '';
                    
                    if($program[0] == $i){
                        $check = 'selected';
                    }
                    
                    $options .= '<option value="'.$i.'"'.$check.'>'.$i.'</option>';
                }
                
                $options .= '</select></div>';

                $options .= '<div class="col-sm-4">
                <select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[semi-annual][month]">
                    <option value="">Mês...</option>';

                foreach($mesesDoAno as $key => $value){
                    $check = '';
                    if($program[1] == $key){
                        $check = 'selected';
                    }
                    $options .= '<option value="'.$key.'"'.$check.'>'.$value.'</option>';
                }

                $options .= '</select></div>';

                return $options;
                break;

                case '5':
                    $program = explode('/', $program);
                    $options = '<label for="jobProgram" class="col-sm-4 col-form-label">Selecione o dia</label>
                    <div class="col-sm-4">
                        <select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[annually][day]">
                        <option value="0">Selecione...</option>';
                    
                    for($i = 0; $i <= 31; $i++){
                        $check = '';
                        
                        if($program[0] == $i){
                            $check = 'selected';
                        }
                        
                        $options .= '<option value="'.$i.'"'.$check.'>'.$i.'</option>';
                    }
                    
                    $options .= '</select></div>';
    
                    $options .= '<div class="col-sm-4">
                    <select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[annually][month]">
                        <option value="">Mês...</option>';
    
                    foreach($mesesDoAno as $key => $value){
                        $check = '';
                        if($program[1] == $key){
                            $check = 'selected';
                        }
                        $options .= '<option value="'.$key.'"'.$check.'>'.$value.'</option>';
                    }
    
                    $options .= '</select></div>';
    
                    return $options;
                    break;
        }
    }
    
}