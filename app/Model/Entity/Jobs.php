<?php

namespace App\Model\Entity;

use Exception;
use \WilliamCosta\DatabaseManager\Database;

class Jobs{

    /**
     * ID do job
     * @var integer
     */
    public $id;

    /**
     * Nome do Job
     * @var string
     */
    public $jobName;

    /**
     * Tipo do Job (FULL, Incremental)
     * @var integer
     */
    public $jobType;

    /**
     * Recorrência do Job (Diário, Semanal, Mensal, Semestral e Anual)
     * @var array
     */
    public $jobRecurrence;

    /**
     * Programação do Job (Dias da semana, mês, etc)
     * @var array
     */
    public $jobProgram;

    /**
     * Horário a ser executado o Job (Formato HH:MM)
     * @var string
     */
    public $jobTime;

    /**
     * ID do host a ser executado o Job (Chave Estrangeira)
     * @var integer
     */
    public $hostID;

    /**
     * Descriçao do Job
     * @var string
     */
    public $jobDescription;

    /**
     * Retenção das cópias (Manter um número X de cópias de backup)
     * @var integer
     */
    public $jobRetention;

    /**
     * Data e hora de criação do Job
     * @var string
     */
    public $creationDate;

    /**
     * Método responsável por formatar a programação do Job de acordo com a recorrência (Diária, Semanal etc...)
     * @return Jobs
     */
    private function setJobProgram(){
        //CONFIGURA A HORA PARA O FORMATO HH:MM
        $this->jobTime = $this->jobTime['hour'].':'.$this->jobTime['minutes'];
        //VERIFICA O TIPO DE RECORRÊNCIA
        switch($this->jobRecurrence){
            //RECORRÊNCIA DIÁRIA
            case '1':
                $this->jobProgram = implode(',', $this->jobProgram['days']);
                break;
            
            //RECORRÊNCIA SEMANAL    
            case '2':
                $this->jobProgram = $this->jobProgram['weekly'];
                break;

            //RECORRÊNCIA MENSAL
            case '3':
                $this->jobProgram = $this->jobProgram['monthly'];
                break;

            //RECORRÊNCIA SEMESTRAL
            case '4':
                $this->jobProgram = $this->jobProgram['semi-annual']['day'].'/'.$this->jobProgram['semi-annual']['month'];
                break;

            //RECORRÊNCIA ANUAL
            case '5':
                $this->jobProgram = $this->jobProgram['annually']['day'].'/'.$this->jobProgram['annually']['month'];
                break;

            default:
                throw new Exception("Opa! Parece que alguns dados da requisição foram preenchidos incorretamento. Faça a requisição novamente!", 501);
                break;
        }
    }

    /**
     * Método responsável por cadastrar a instância atual no banco de dados
     * @return boolean
     */
    public function cadastrar(){
        //DEFINE A DATA
        $this->creationDate = date('Y-m-d H:i:s');
        self::setJobProgram();

        //INSERE O JOB NO BANCO DE DADOS
        $this->id = (new Database('jobs'))->insert([
            'jobName'       => $this->jobName,
            'jobType'       => $this->jobType,
            'jobRecurrence' => $this->jobRecurrence,
            'jobProgram'    => $this->jobProgram,
            'jobTime'       => $this->jobTime,
            'hostID'        => $this->hostID,
            'jobDescription'=> $this->jobDescription,
            'jobRetention'  => $this->jobRetention
        ]);
        
        //SUCESSO
        return true;
    }

    /**
     * Método responsável por retornar os jobs
     * @param string  $where
     * @param string  $join
     * @param string  $on
     * @param string  $order
     * @param string  $limit
     * @param string  $fields
     * @return PDOStatement
     */
    public static function getJobs($where = null, $join = null, $on = null, $order = null, $limit = null, $fields = '*'){
        return (new Database('jobs'))->select($where,$join,$on,$order,$limit,$fields);
    }
}