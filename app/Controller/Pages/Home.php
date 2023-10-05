<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Model\Entity\Host;

use Moccalotto\Ssh\Auth;
use Moccalotto\Ssh\Session;
use Moccalotto\Ssh\Connect;
use Moccalotto\Ssh\Terminal;

class Home extends Page{


    /**
     * Método responsável por retornar o conteúdo (view) da página home
     * @return string
     */
    public static function getHome(){
        //SSH TESTE
        $ip = '10.79.28.21';
        $username = 'sti';
        $port = 22;
        $password = 's&n@d0rx1ng@gener@l';

        //CREATE A SSH CONNECTION
        $ssh = new Session(
            Connect::to($ip, $port),
            Auth::viaPassword($username, $password)
        );

        //CONFIGURAÇÃO DO TERMINAL
        $terminal = Terminal::create()
            ->width(80, 'chars')
            ->height(25, 'chars');

        //HOST
        $obHost = new Host;

        //VIEW DA HOME
        $content = View::render('pages/home', [
            'name' => ''
        ]);

        //RETORNA A VIEW DA PÁGINA
        return parent::getPage('SISBACKUP::Início', $content.PHP_EOL);
    }

}
/**
 * 'ssh'           => $ssh->withTerminal($terminal)->shell(function ($shell) {
*
 *               $captured_output = $shell
  *                  ->writeline('echo The home dir is: $HOME')
   *                 ->writeline('echo the contents of $PWD is:; ls -lah')
    *                ->writeline('logout')
     *               ->wait(0.3) // give the shell time to execute the commands.
      *              ->readToEnd();
       *     
        *        return $captured_output;
         *   })
 */