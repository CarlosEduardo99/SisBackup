<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Model\Entity\Jobs as EntityJobs;
use \App\Model\Entity\Host as EntityHost;

class Jobs extends Page{

    /**
     * Método responsável por obter a renderização dos itens de jobs para a página
     * @return string
     */
    private static function getJobsItems(){
        //JOBS
        $itens = '';

        //RESULTADOS DA PÁGINA
        $results = EntityJobs::getJobs(
            null,
            'INNER JOIN jobRecurrence recurrence ON jobs.jobRecurrence = recurrence.id',
            'INNER JOIN hosts ON jobs.hostID = hosts.id',
            'jobs.id',
            null,
            'jobs.id,jobName,recurrence,jobType,jobProgram,jobTime,hostID,hosts.hostName,jobDescription,jobRetention,creationDate');
        
        //RENDERIZA O ITEM
        while($obJobs = $results->fetchObject(EntityJobs::class)){
            //VIEW DA PÁGINA HOSTS
            $itens .= View::render('pages/job/item', [
                'jobName'           => $obJobs->jobName,
                'jobRecurrence'     => $obJobs->recurrence,
                'jobType'           => $obJobs->jobType == '1' ? 'Incremental' : 'Full',
                'jobProgram'        => $obJobs->jobProgram,
                'jobTime'           => $obJobs->jobTime,
                'hostID'            => $obJobs->hostName,
                'jobDescription'    => $obJobs->jobDescription,
                'jobRetention'      => $obJobs->jobRetention,
                'user'              => 'Usuário',
                'creationDate'      => date('d/m/Y à\s H:i', strtotime($obJobs->creationDate))
            ]);
        }

        //RETORNA OS JOBS
        return $itens;
    }

    /**
     * Método responsável por retornar o conteúdo (view) da página jobs
     * @return string
     */
    public static function getJobs(){
        //OBTEM HOST ID E HOSTNAME
        $itens =''; 
            
        $results = EntityHost::getHosts(null,null,null,'id', null, 'id,hostName');

        while($obHost = $results->fetchObject(EntityJobs::class)){
            //VIEW DA PÁGINA HOSTS
            $itens .= '<option value="'.$obHost->id.'">'.$obHost->hostName.'</option>';
        }

        //VIEW DA PÁGINA JOBS
        $content = View::render('pages/jobs', [
            'itens'     => !empty(self::getJobsItems()) ? self::getJobsItems() : '<h4 class="text-center mt-3">Não existem Jobs de Backup configurados<h4>',
            'select'    => $itens
        ]);

        //RETORNA A VIEW DA PÁGINA
        return parent::getPage('SISBACKUP::Jobs', $content);
    }

    /**
     * Método responsávelpor criar o agendamento de um Job
     * @param Request $request
     * @return string
     */
    public static function insertJob($request){
        //DADOS DO POST
        $postVars = $request->getPostVars();

        //NOVA INSTANCIA DE JOB
        $obJob = new EntityJobs;
        $obJob->jobName         = $postVars['jobName'];
        $obJob->jobType         = $postVars['jobType'];
        $obJob->jobRecurrence   = $postVars['jobRecurrence'];
        $obJob->jobProgram      = $postVars['jobProgram'];
        $obJob->jobTime         = $postVars['jobTime'];
        $obJob->hostID          = $postVars['hostID'];
        $obJob->jobDescription  = $postVars['jobDescription'];
        $obJob->jobRetention    = $postVars['jobRetention'];
        $obJob->cadastrar();

        return self::getJobs();
    }

}

//formatar timestamp
//date('d/m/Y H:i:s', strtotime($timestamp))