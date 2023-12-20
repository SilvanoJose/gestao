<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;
use \src\handlers\PostHandler;

class ProfileController extends Controller {

    private $loggedUser;
    
    public function __construct(){

        $this->loggedUser = UserHandler::checkLogin();
        
        if($this->loggedUser === false){
            
            $this->redirect('/login');

        }



    }

    public function index($atts = []){
        if(!empty($atts['id'])){
            $id = $atts['id'];

        }else{
            $id = $this->loggedUser->id;
        }

        echo "ID: ".$id;

        $this->render('profile', [
            'loggedUser' => $this->loggedUser
        ]);

    }  
}