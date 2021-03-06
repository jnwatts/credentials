<?php
namespace Controllers;

use Core\View;
use Core\Controller;
use Core\Model;
use Core\Error;
use Helpers\Request;
use Helpers\Audit;
use Helpers\User;

use \phpseclib\Crypt\RSA;

class Keys extends Controller
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
        $key = $this->keys->getById($id);
        if (!$key) {
            http_response_code(404);
            echo 'Not found';
        } else if ($key->user_id != $current_user->id && !$current_user->isAdmin()) {
            http_response_code(403);
            echo 'Not allowed';
        } else {
            $user = User::instance()->findId($key->user_id);
            Audit::log($current_user, 'delete key '.$key->id.' for '.$user, $key);
            $this->keys->deleteById($id);
        }
    }

    public function create()
    {
        if (!Request::isPost()) {
            http_response_code(400);
            return;
        }
        $data = json_decode(file_get_contents('php://input'));

        if (is_array($data)) {
            $this->createMany($data);
            return;
        }

        $current_user = User::current();
        $user = User::instance()->findId($data->user_id);

        if ($current_user->isAdmin()) {
            if ($user == NULL) {
                http_response_code(409);
                echo 'Invalid user id';
                return;
            }
        } else {
            if ($current_user->id != $user->id) {
                http_response_code(403);
                echo 'User ID does not match current user';
                return;
            }
        }

        $data->user = $user->login;

        $this->sanitize_key($data);

        $result = [];
        if (!$this->validate_key($data, $result)) {
            http_response_code($result['status']);
            echo $result['message'];
            return;
        }

        $existing_key = $this->keys->getByUserHost($user, $data->host);
        if ($existing_key != NULL) {
            http_response_code(409);
            echo 'Host already exists for that user';
            return;
        }

        $key = $this->keys->create($user, $data->host, $data->hash);
        Audit::log($current_user, 'create key '.$key->id.' for '.$user, $key);
        http_response_code(200);
        echo json_encode($key, JSON_PRETTY_PRINT);
    }

    public function createMany($data)
    {
        $current_user = User::current();
        if (!$current_user->isAdmin()) {
            http_response_code(403);
            echo 'Not allowed';
            return;
        }
        $results = array();
        foreach ($data as $data) {
            $result = array(
                'user' => $data->user,
                'host' => $data->host
            );

            if ($this->validate_key($data, $result)) {
                $user = User::instance()->get($data->user);
                $result['user_id'] = $user->id;

                $key = $this->keys->getByUserHost($user, $data->host);
                if ($key != NULL) {
                    $result['status'] = 409;
                    $result['message'] = 'Host already exists for that user';
                    $result['key_id'] = $key->id;
                } else {
                    $key = $this->keys->create($user, $data->host, $data->hash);
                    Audit::log($current_user, 'create key '.$key->id.' for '.$user, $key);
                    $result['key_id'] = $key->id;
                    $result['status'] = 200;
                    $result['message'] = 'Ok';
                }
            }
            $results[] = $result;
        }

        echo json_encode($results, JSON_PRETTY_PRINT);
    }

    public function update($id)
    {
        http_response_code(404);
        echo 'Not implemented';
        return;
    }

    protected function validate_key($data, &$result)
    {
        $result['status'] = 200;
        $result['message'] = 'Ok';
        if (!preg_match('/\S/', $data->user)) {
            $result['status'] = 409;
            $result['message'] = 'User is empty';
        } else if (!preg_match('/\S/', $data->host)) {
            $result['status'] = 409;
            $result['message'] = 'Host is empty';
        } else if (!preg_match('/\S/', $data->hash)) {
            $result['status'] = 409;
            $result['message'] = 'Hash is empty';
        } else if (preg_match('/(BEGIN)?.*PRIVATE.*(KEY)?/i', $data->hash)) {
            $result['status'] = 409;
            $result['message'] = 'Looks like private key';
        } else {
            $rsa = new \phpseclib\Crypt\RSA();
            if (!$rsa->loadKey($data->hash)) {
                $result['status'] = 409;
                $result['message'] = 'Unable to parse key';
            }
        }

        return $result['status'] == 200;
    }

    protected function sanitize_key(&$data)
    {
        $data->host = preg_replace('/^(.*@)?(.*?)(\.pub)?$/', '$2', $data->host);
        $data->host = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data->host);
        if (preg_match('/.*BEGIN.*\n/', $data->hash)) {
            $data->hash = shell_exec('bash -c \'ssh-keygen -i -f /dev/stdin <<<"'.$data->hash.'"\'');
        }
    }
}
