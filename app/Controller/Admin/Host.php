<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\Host as EntityHost;
use \WilliamCosta\DatabaseManager\Pagination;

class Host extends Page{

    /**
     * Método responsável por obter a renderização dos itens de hosts para a página
     * @param Request $request
     * @param Pagination $obPagination
     * @return string
     */
    private static function getHostsItems($request,&$obPagination){
        //HOSTS
        $itens = '';

        //QUANTIDADE TOTAL DE REGISTROS
        $quantidadeTotal = EntityHost::getHosts(null,null,null,null,null,'COUNT(*) as qtd')->fetchObject()->qtd;

        //PÁGINA ATUAL
        $queryParams = $request->getQueryParams();
        $paginaAtual = $queryParams['page'] ?? 1;        

        //INSTANCIA DE PAGINAÇÃO
        $obPagination = new Pagination($quantidadeTotal,$paginaAtual,20);

        //RESULTADOS DA PÁGINA
        $results = EntityHost::getHosts(null,null,null,'id',$obPagination->getLimit());

        //RENDERIZA O ITEM
        while($obHost = $results->fetchObject(EntityHost::class)){
            //VIEW DA PÁGINA HOSTS
            $itens .= View::render('admin/modules/hosts/item', [
                'id'                => $obHost->id,
                'hostName'          => $obHost->hostName,
                'hostUser'          => $obHost->hostUser,
                'hostIP'            => $obHost->hostIP,
                'hostDescription'   => $obHost->hostDescription,
                'dbNames'             => $obHost->hasDB == 'True' ? $obHost->dbNames : '- Não há -',
                'directory'         => $obHost->directory
            ]);
        }

        //RETORNA OS HOSTS
        return $itens;
    }

    /**
     * Método responsável por renderizar a view de listagem de hosts
     * @param Request $request
     * @return string
     */
    public static function getHost($request){
        //CONTEÚDO DA HOME
        $content = View::render('admin/modules/hosts/index',[
            'itens'         => self::getHostsItems($request,$obPagination),
            'pagination'    => parent::getPagination($request,$obPagination),
            'modalNew'     => View::render('admin/modules/hosts/modal/newhost',[
                'title' => 'Novo Host'
            ]),
            'modalEdit'     => '',
            'status'        => self::getStatus($request)
        ]);
        
        //RETORNA A PÁGINA COMPLETA
        return parent::getPanel('SISBACKUP::Hosts', $content,'hosts');
    }
    
     /**
     * Método responsávelpor criar um host
     * @param Request $request
     * @return string
     */
    public static function setNewHost($request){
        //DADOS DO POST
        $postVars = $request->getPostVars();

        self::createConf($postVars);

        //NOVA INSTANCIA DE JOB
        $obHost = new EntityHost;
        $obHost->hostName           = $postVars['hostName'];
        $obHost->hostUser           = $postVars['hostUser'];
        $obHost->hostIP             = $postVars['hostIP'];
        $obHost->hostPass           = $postVars['hostPass'];
        $obHost->hostDescription    = $postVars['hostDescription'];
        $obHost->hasDB              = $postVars['hasDB'];
        $obHost->dbNames            = $postVars['dbNames'];
        $obHost->directory          = $postVars['directory'];
        $obHost->cadastrar();
        
        //REDIRECIONA O USUÁRIO
        $request->getRouter()->redirect('/admin/hosts?status=created');
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
                return Alert::getSuccess('Host criado com sucesso!');
                break;
            case 'edited':
                return Alert::getSuccess('Host editado com sucesso!');
                break;
            case 'deleted':
                return Alert::getSuccess('Host exluído com sucesso!');
                break;
        }
    }

     /**
     * Método responsável por retornar o formulário de edição de um host
     * @param Request $request
     * @param integer $id
     * @return string
     */
    public static function getEditHost($request,$id){
        //OBTÉM O HOST DO BANCO DE DADOS
        $obHost = EntityHost::getHostById($id);

        //VALIDA A INSTÂNCIA
        if(!$obHost instanceof EntityHost){
            $request->getRouter()->redirect('/admin/hosts');
        }

        //CONFIGURA OS DIRETÓRIOS
        $directories = '';

        $dirs = explode('<br>', $obHost->directory);

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
        $content = View::render('admin/modules/hosts/index',[
            'itens'         => self::getHostsItems($request,$obPagination),
            'pagination'    => parent::getPagination($request,$obPagination),
            'modalEdit'     => View::render('admin/modules/hosts/modal/edithost',[
                'title' => 'Editar Host'
            ]),
            'hostName'          => $obHost->hostName,
            'hostUser'          => $obHost->hostUser,
            'hostPass'          => $obHost->hostPass,
            'hostIP'            => $obHost->hostIP,
            'hostDescription'   => $obHost->hostDescription,
            'selected'          => $obHost->hasDB == 'True' ? 'selected' : '',
            'dbNames'           => $obHost->dbNames,
            'directory'         => $directories ?? '',
            'modalNew'      => View::render('admin/modules/hosts/modal/newhost',[
                'title'     => 'Novo Host'
            ]),
            'status'            => self::getStatus($request)
        ]);

        //RETORNA A PÁGINA COMPLETA
        return parent::getPanel('SISBACKUP::Hosts', $content,'hosts');
        
    }

    /**
     * Método responsável por gravar a atualização de um host
     * @param Request $request
     * @param integer $id
     * @return string
     */
    public static function setEditHost($request,$id){
        //OBTÉM O HOST DO BANCO DE DADOS
        $obHost = EntityHost::getHostById($id);

        //VALIDA A INSTÂNCIA
        if(!$obHost instanceof EntityHost){
            $request->getRouter()->redirect('/admin/hosts');
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
        $request->getRouter()->redirect('/admin/hosts/'.$obHost->id.'/edit?status=edited');        
    
    }

    /**
     * Método responsável por renderizar página de exclusão de um host
     * @param Request $request
     * @param integer $id
     * @return string
     */
    public static function getDeleteHost($request,$id){
        //OBTÉM O HOST DO BANCO DE DADOS
        $obHost = EntityHost::getHostById($id);

        //VALIDA A INSTÂNCIA
        if(!$obHost instanceof EntityHost){
            $request->getRouter()->redirect('/admin/hosts');
        }

         //CONTEÚDO DA HOME
        $content = View::render('admin/modules/hosts/delete',[
            'hostName' => $obHost->hostName,
            'hostIP' => $obHost->hostIP,
            'dbNames' => $obHost->dbNames,
            'directory' => $obHost->directory,
            'jobs' => 'A ser implementado'
        ]);

        //RETORNA A PÁGINA COMPLETA
        return parent::getPanel('SISBACKUP::Hosts', $content,'hosts');

    }
    
    /**
     * Método responsável por excluir um host
     * @param Request $request
     * @param integer $id
     * @return string
     */
    public static function setDeleteHost($request,$id){
        //OBTÉM O HOST DO BANCO DE DADOS
        $obHost = EntityHost::getHostById($id);

        //VALIDA A INSTÂNCIA
        if(!$obHost instanceof EntityHost){
            $request->getRouter()->redirect('/admin/hosts');
        }
    
        //POST VARS
        $postVars = $request->getPostVars();

        //VALIDAÇÃO DA AÇÃO
        if(isset($postVars['action']) && $postVars['action'] == 'delete'){
            //EXCLUI O HOST
            $obHost->excluir();

            //REDIRECIONA O USUÁRIO
            $request->getRouter()->redirect('/admin/hosts?status=deleted'); 
        }else{
            //REDIRECIONA O USUÁRIO
            $request->getRouter()->redirect('/admin/hosts?status=erro'); 
        }

    }

    /**
     * Método responsável por criar arquivo de configuração do host
     */
    public function createConf($postVars){

        //CONFIGURA OS NOMES DOS BANCOS
        $dbNames = explode(' ', $postVars['dbNames']);
        $database_names = "( ";
        foreach($dbNames as $dbName){
            $database_names .= '"'.$dbName.'" ';
        }
        $database_names .= ")";

        $configuracoes = [
            "remote_server" => $postVars["hostUser"].'@'.$postVars["hostIP"],
            "backup_directory" => getenv("BACKUP_DIRECTORY"),
            "backup_prefix" => $postVars["hostName"],
            "mysql_user" => $postVars["dbUser"],
            "mysql_password" => $postVars["dbPass"],
            "mysql_host" => $postVars["hostIP"],
            "backup_databases" => $postVars["hasDB"],
            "database_names" => $database_names,
            "backup_type" => "",
            "source_directories" => "",
            "backup_count" => ""
        ];
        #$hostConfPath = getenv("DOCUMENT_ROOT").'backup/hosts/'.$postVars['hostName'].'.conf';
        #$confFile = fopen($hostConfPath, "w") or die("Unable to open file!"); 

        echo "<pre>";
        #$texto = file_get_contents($hostConfPath);
        print_r($configuracoes);
        echo "</pre>"; exit;
    }
}