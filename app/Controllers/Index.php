<?php
namespace Controllers;

use Core\View;
use Core\Controller;
use Core\Model;
use Core\Error;
use Helpers\Hooks;
use Helpers\Audit;
use Helpers\Request;
use Helpers\User;

class Index extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // User is admin, show index of users
        $data['title'] = 'Credentials';
        $data['current_user'] = $current_user;
        $data['footer-logic'] = 'credentials/index-footer';

        View::renderTemplate('header', $data);
        View::render('credentials/index', $data);
        View::renderTemplate('footer', $data);
    }
}
