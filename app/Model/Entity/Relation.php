<?php

namespace App\Model\Entity;

use Exception;
use \WilliamCosta\DatabaseManager\Database;

class Relation{

    /**
     * ID do relação job-host
     * @var integer
     */
    public $id_relacao;

    /**
     * ID do job
     * @var integer
     */
    public $id_job;

    /**
     * ID do Host
     * @var integer
     */
    public $id_host;

    /**
     * Método responsável por cria a relação host - job
     * @param integer $hostID
     * @param integer $jobID
     * @return PDOStatement
     */
    public static function setRelation($hostID, $jobID){

        return (new Database('hosts_jobs'))->insert([
            'id_host'   => $hostID,
            'id_job'    => $jobID
        ]);   
    }

    /**
     * Método responsável por obter o host associado ao job
     * @param integer $id
     * @return PDOStatement
     */
    public static function getRelationHost($jobID){
        $obRelation = (new Database('hosts_jobs'))->select('id_job = '.$jobID,null,null,'id_host')
                                                ->fetchObject()->id_host;

        return (new Database('hosts'))->select('id = '.$obRelation)->fetchObject();
    }

    /**
     * Método responsável por atualizar o host associado ao job
     * @param integer $id
     * @return PDOStatement
     */
    public static function updateHost($id_job, $id_host){
        return (new Database('hosts_jobs'))->update('id_job = '.$id_job, [
            'id_host' => $id_host
        ]);
    }
}

