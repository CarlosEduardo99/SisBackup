<?php

namespace App\Model\Entity;

use Exception;
use \WilliamCosta\DatabaseManager\Database;
use ICanBoogie\DateTime;

date_default_timezone_set('America/Manaus');

class Job{

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

        if($this->id){
            self::setRelation($this->hostID,$this->id);

            //SUCESSO
            return true;
        }else{
            return false;
        }
        
        
    }

    /**
     * Método responsável por cria a relação host - job
     * @param integer $hostID
     * @param integer $jobID
     * @return PDOStatement
     */
    private static function setRelation($hostID, $jobID){
        return (new Database('hosts_jobs'))->insert([
            'id_host'   => $hostID,
            'id_job'    => $jobID
        ]);   
    }

    /**
     * Método responsável por deletar uma instância banco de dados
     * @return boolean
     */
    public function excluir(){
        
        //DELETA HOST NO BANCO DE DADOS
        return (new Database('jobs'))->delete('id = '.$this->id);

    }

    /**
     * Método responsável por retornar um host com base no seu id
     * @param integer $id
     * $return Host
     */
    public static function getJobById($id){
        return self::getJobs('id = '.$id)->fetchObject(self::class);
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

     /**
     * Método responsável por retornar os tipos de recorrência dos jobs
     * @param string $id
     * @return PDOStatement  
     */
    public static function getRecurrence($where = null, $join = null, $on = null, $order = null, $limit = null, $fields = '*'){
        return (new Database('jobRecurrence'))->select($where,$join,$on,$order,$limit,$fields);
    }

    /**
     * Método responsável por retornar a programação dos jobs
     * @param string $recurrence
     * @param string $program
     * @return string 
     */
    public static function getProgram($recurrence, $program){

        switch($recurrence){
            case '1': 
            case '2':
                $dias = self::setWeekDays($program);
                $saida = explode(',', $dias);
                if($saida[0] == 'Domingo' || $saida[0] == 'Sábado'){
                    return 'Todo '.$dias;
                    break;
                }else{
                    return 'Toda '.$dias;
                    break;
                }
                break;

            case '3':
                return 'Todo dia '.$program.' de cada mês';
                break;
            
            case '4':
            case '5':
                return self::setMonths($recurrence, $program);
                break;
        }
        
    }

    /**
     * Método responsável por retornar os dias da semana
     * @param string $program
     * @return string 
     */
    private static function setWeekDays($program){

        $diasDaSemana = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado'
          ];

        $dias = explode(',', $program);
        $saida = '';

        foreach($dias as $dia ){
            $saida .= $diasDaSemana[$dia].', ';
        }

        //REMOVE A VÍRGULA EXTRA NO FINAL
        $saida = rtrim($saida, ', ');

        if(count($dias) == 1){
            //SE HOUVER APENAS 1 DIA
            return $saida;    
        }else{
            //ADICIONA "e" PARA SEPARAR O ÚLTIMO DIA
            $ultimaVirgulaPos = strrpos($saida, ',');
            $saida = substr_replace($saida, ' e', $ultimaVirgulaPos, 1);
            return $saida;
        }
    }

    /**
     * Método responsável por retornar os meses do ano
     * @param string $program
     * @return string 
     */
    private static function setMonths($recurrence, $program){
        
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

        $data = explode('/', $program);
        $dia = $data[0];
        $mes = $data[1];

        if($recurrence == '4'){
            // Cria um objeto DateTime com a data de entrada
            $data_entrada = new DateTime(date("Y") . "-" . $mes . "-" . $dia);

            // Adiciona 6 meses à data de entrada
            $data_saida = clone $data_entrada;
            $data_saida->modify('+6 months');
            
            // Formata as datas de saída
            $saida_formatada = $data_saida->format('d'). ' de '.$mesesDoAno[$data_saida->format('M')];

            return 'Todo dia '.$dia.' de '.$mesesDoAno[$mes]. ' e '.$saida_formatada;
        }else{
            return 'Todo dia '.$dia.' de '.$mesesDoAno[$mes];
        }
    }
}