<?php
namespace Controllers;

use Core\View;
use Core\Controller;
use Core\Model;
use Core\Error;
use Helpers\Hooks;
use Helpers\Audit;
use Helpers\Request;

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
        $current_user = $this->users->currentUser();
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

        $user = $this->users->getById($id);
        if ($user == NULL) {
            http_response_code(404);
            echo "User not found";
            return;
        }

        Audit::log($current_user, 'delete user '.$id, $user);
        $this->users->deleteById($id);
    }

    public function create()
    {
        $current_user = $this->users->currentUser();
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

        if (!preg_match('/^[a-zA-Z0-9\.\_\-]+$/', $data->login)) {
            http_response_code(409);
            echo "Invalid username";
            return;
        }

        if ($this->users->getByLogin($data->login) != NULL) {
            http_response_code(409);
            echo "User already exists";
            return;
        }

        $user = $this->users->createFromLogin($data->login);
        $this->users->update($user->id, get_object_vars($data));
        Audit::log($current_user, 'create user '.$user->id, $user);

        echo json_encode($user, JSON_PRETTY_PRINT);
    }

    public function update($id)
    {
        $current_user = $this->users->currentUser();
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
            Audit::log($current_user, 'update user '.$user->id, $update_data);
        }
    }

    public function index($id)
    {
        $current_user = $this->users->currentUser();
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
