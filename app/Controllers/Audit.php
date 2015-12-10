<?php
namespace Controllers;

use Core\View;
use Core\Controller;
use Core\Model;
use Core\Error;
use Helpers\Hooks;
use Helpers\Request;
use Helpers\User;

class Audit extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->audit = new \Models\Audit();
        $this->users = new \Models\Users();
        $this->keys = new \Models\Keys();
    }

    public function index($since = NULL, $until = 0)
    {
        $current_user = User::current();
        if (!$current_user->isAdmin()) {
            http_response_code(403);
            echo "Access denied";
            return;
        }

        if ($since == NULL) {
            $since = strtotime("-1 days");
        } else {
            $since = intval($since);
        }
        if ($until == NULL) {
            $until = strtotime("now");
        } else {
            $until = intval($until);
        }

        $data['title'] = 'Audit';
        $data['current_user'] = $current_user;
        $data['footer-logic'] = 'credentials/audit-footer';
        $data['logs'] = $this->audit->get($since, $until);
        $data['since'] = $since;
        $data['until'] = $until;
        $data['span'] = $until - $since;
        View::renderTemplate('header', $data);
        View::render('credentials/audit', $data);
        View::renderTemplate('footer', $data);
    }
}
