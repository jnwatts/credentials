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

class Users extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->users = new \Models\Users();
        $this->keys = new \Models\Keys();
    }

    public function delete($id)
    {
        $current_user = User::current();
        if (!$current_user->isAdmin()) {
            http_response_code(403);
            echo "Not allowed";
            return;
        }

        if ($current_user->id == $id) {
            http_response_code(409);
            echo "Can't delete yourself";
            return;
        }

        $user = User::instance()->findId($id);
        if ($user == NULL) {
            http_response_code(404);
            echo "User not found";
            return;
        }

        Audit::log($current_user, 'delete user '.$user, $user);
        $this->users->deleteById($id);
    }

    public function create()
    {
        $current_user = User::current();
        if (!$current_user->isAdmin()) {
            http_response_code(403);
            echo "Not allowed";
            return;
        }

        if (!Request::isPost()) {
            http_response_code(400);
            return;
        }
        $data = json_decode(file_get_contents('php://input'));

        $user = User::instance()->create($data->login, get_object_vars($data));

        if ($user != NULL)
            echo json_encode($user, JSON_PRETTY_PRINT);
    }

    public function update($id)
    {
        $current_user = User::current();
        if ($current_user->id != $id && !$current_user->isAdmin()) {
            http_response_code(403);
            echo "Not allowed";
            return;
        }

        if (!Request::isPost()) {
            http_response_code(400);
            return;
        }
        $data = json_decode(file_get_contents('php://input'));
        $update_data = array();

        $valid_keys = array();
        if ($current_user->isAdmin()) {
            $valid_keys[] = "admin";
        }

        $user = $this->users->getById($id);
        $vars = get_object_vars($user);
        foreach ($vars as $k => $v) {
            if (in_array($k, $valid_keys) && isset($data->$k)) {
                if ($data->$k != $v) {
                    $update_data[$k] = $data->$k;
                }
            }
        }

        if (count($update_data) > 0) {
            $this->users->update($user->id, $update_data);
            Audit::log($current_user, 'update user '.$user, $update_data);
        }
    }

    public function index($id = NULL)
    {
        $current_user = User::current();
        if ($id == NULL) {
            if ($current_user->isAdmin()) {
                // User is admin, show index of users
                $data['title'] = 'User overview';
                $data['current_user'] = $current_user;
                $data['users'] = $this->users->getAll();
                $data['footer-logic'] = 'credentials/users-footer';

                View::renderTemplate('header', $data);
                View::render('credentials/users', $data);
                View::renderTemplate('footer', $data);
            } else {
                // User is not admin, redirect to their page
                $this->index($current_user->id);
            }
        } else {
            $user = $this->users->getById($id);

            if ($user == NULL) {
                http_response_code(404);
                echo "Not found";
                return;
            }

            if ($current_user->id != $user->id && !$current_user->isAdmin()) {
                http_response_code(403);
                echo "Not allowed";
                return;
            }

            $data['title'] = 'User ' . $user->login;
            $data['current_user'] = $current_user;
            $data['user'] = $user;
            $data['keys'] = $this->keys->getAllByUser($user);
            $data['footer-logic'] = 'credentials/user-footer';

            View::renderTemplate('header', $data);
            View::render('credentials/user', $data);
            View::renderTemplate('footer', $data);
        }
    }
}
