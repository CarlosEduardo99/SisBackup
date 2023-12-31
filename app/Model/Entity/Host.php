<?php

namespace App\Model\Entity;

use Exception;
use \WilliamCosta\DatabaseManager\Database;

class Host{

    /**
     * ID do Host
     * @var integer
     */
    public $id;

    /**
     * Nome do Host
     * @var string
     */
    public $hostName;

    /**
     * Usuário para conexão SSH
     * @var string
     */
    public $hostUser;

    /**
     * IP do servidor/host
     * @var string
     */
    public $hostIP;

    /**
     * Senha de acesso ao servidor/host
     * @var string
     */
    public $hostPass;

    /**
     * Descrição/Informações do host
     * @var string
     */
    public $hostDescription;

    /**
     * Informa se tem banco de dados
     * @var boolean
     */
    public $hasDB;

    /**
     * Nome(s) do(s) banco(s) de dados
     * @var string
     */
    public $dbNames;

    /**
     * Caminhos dos diretórios para backup
     * @var array
     */
    public $directory = [];

    /**
     * Método responsável por formatar os diretórios para backup do Host
     * @var Host
     */
    private function setHostDirectory(){
        $this->directory = implode('<br>', $this->directory);
    }

    /**
     * Método responsável por cadastrar a instância atual no banco de dados
     * @return boolean
     */
    public function cadastrar(){

        //FORMATA OS DIRETÓRIOS PARA BACKUP
        self::setHostDirectory();
        
        //INSERE O JOB NO BANCO DE DADOS
        $this->id = (new Database('hosts'))->insert([
            'hostName'          => $this->hostName,
            'hostUser'          => $this->hostUser,
            'hostIP'            => $this->hostIP,
            'hostPass'          => $this->hostPass,
            'hostDescription'   => $this->hostDescription,       
            'hasDB'             => $this->hasDB,
            'dbNames'           => $this->dbNames,
            'directory'         => $this->directory
        ]);

        //SUCESSO
        return true;
    }

    /**
     * Método responsável por retornar hosts
     * @param string  $where
     * @param string  $order
     * @param string  $limit
     * @param string  $fields
     * @return PDOStatement
     */
    public static function getHosts($where = null, $join = null, $on = null,$order = null, $limit = null, $fields = '*'){
        return (new Database('hosts'))->select($where,$join,$on,$order,$limit,$fields);
    }
}