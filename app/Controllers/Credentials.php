<?php
namespace Controllers;

use Core\View;
use Core\Controller;
use Core\Model;
use Core\Error;

class Credentials extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->users = new \Models\Users();
        $this->keys = new \Models\Keys();
    }

    /*TODO: This is really users/ index without slug...*/
    public function index()
    {
        $user = $this->users->currentUser();
        if ($user->isAdmin()) {
            // User is admin, show index of users
            $data['title'] = 'User overview';
            $data['current_user'] = $this->users->currentUser();
            $data['users'] = $this->users->getAll();
            $data['footer-logic'] = 'credentials/index-footer';

            View::renderTemplate('header', $data);
            View::render('credentials/index', $data);
            View::renderTemplate('footer', $data);
        } else { 
            // User is not admin, show their own page
            $users = new \Controllers\Users();
            $users->index($user->id);
        }

    }
}
