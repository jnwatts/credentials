<?php
namespace Controllers;

use Core\View;
use Core\Controller;
use Core\Model;
use Core\Error;
use Helpers\Hooks;
use Helpers\Request;
use Helpers\User;
use Helpers\Breadcrumbs;

class Audit extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->audit = new \Models\Audit();
        $this->users = new \Models\Users();
        $this->keys = new \Models\Keys();
    }

    private function validate_date($date)
    {
        if (preg_match('/[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}/', $date)) {
            return strtotime($date);
        } else {
            return false;
        }
    }

    public function index()
    {
        $current_user = User::current();
        if (!$current_user->isAdmin()) {
            http_response_code(403);
            echo "Access denied";
            return;
        }

        $since = $_GET['start'];
        $until = $_GET['end'];

        if ($since == NULL) {
            $since = strtotime("midnight");
        } else {
            $since = $this->validate_date($since);
            if (!$since) {
                http_response_code(409);
                echo 'Invalid start date';
                return;
            }
            $since = strtotime("midnight", $since);
        }

        if ($until == NULL) {
            $until = strtotime("tomorrow");
        } else {
            $until = $this->validate_date($until);
            if (!$until) {
                http_response_code(409);
                echo 'Invalid end date';
                return;
            }
            $until = strtotime("midnight", $until);
        }

        Breadcrumbs::add(DIR, 'Credentials');
        Breadcrumbs::add('', 'Audit');
        $data['breadcrumbs'] = Breadcrumbs::get();
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
