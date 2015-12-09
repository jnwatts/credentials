<?php
namespace Helpers;

class User
{
    private static $instance = NULL;
    private $current_user = NULL;

    private function __construct()
    {
        $this->config = new \Models\Config();
        $this->users = new \Models\Users();
    }

    public static function instance()
    {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function current()
    {
        $instance = self::instance();
        if ($instance->current_user == NULL) {
            $instance->current_user = $instance->get($_SERVER['PHP_AUTH_USER']);
        }
        return $instance->current_user;
    }

    public function find($login = "")
    {
        return $this->get($login, false);
    }

    public function findId($id)
    {
        return $this->users->getById($id);
    }

    public function get($login = "", $create_missing = true)
    {
        if ($login == "") {
            return $this->users->getAll();
        } else {
            $user = $this->users->getByLogin($login);
            if ($user == NULL && $create_missing) {
                $user = $this->create($login);
            }
            return $user;
        }
    }

    public function create($login, $details = NULL)
    {
        if (!preg_match('/^[a-zA-Z0-9\.\_\-]+$/', $login)) {
            http_response_code(409);
            echo "Invalid username";
            return NULL;
        }

        $user = $this->find($login);
        if ($user != NULL) {
            http_response_code(409);
            echo "User already exists";
            return NULL;
        }

        $user = $this->createFromLdap($login);
        if ($user == NULL) {
            if ($this->current_user != NULL && $this->current_user->isAdmin()) {
                if ($details == NULL) {
                    $details = array('login' => $login);
                }
                $user = $this->users->create($details);
                if ($user == NULL) {
                    http_response_code(500);
                    echo "Failed to create user";
                    die();
                } else {
                    Audit::log($this->current_user, 'created user '.$user, $user);
                }
            } else {
                http_response_code(403);
                echo "Access denied";
                die();
            }
        } else {
            Audit::log($this->current_user ? $this->current_user : $user, 'imported user '.$user, $user);
        }
        return $user;
    }

    private function getUserInfoFromLdap($login)
    {
        $config = $this->config;
        $admin_dn = $config->get('ldap_admin_dn');
        $bind_dn = $config->get('ldap_bind_dn');
        $bind_pw = $config->get('ldap_bind_pw');
        $base_dn = $config->get('ldap_base_dn');
        $ldap_url = $config->get('ldap_url');

        preg_match(
                '/(?<scheme>[a-z]+?):\/\/' // scheme
                .'(?<host>[a-z0-9\-\._]+)' // host
                .'(?::(?<port>[0-9]+))?' // port
                .'\/?(?<dn>[a-z0-9,=]*)' // dn
                .'(?:\?|$)?/si',
                $ldap_url, $m);
        $ldap_host = $m['scheme'].'://'.$m['host'];
        if (isset($m['port']) && $m['port'] != NULL) {
            $ldap_host .= ':'.$m['port'];
        }

        $h = ldap_connect($ldap_host); //or die('Could not connect to LDAP');
        if (!$h)
            return NULL;
        $b = ldap_bind($h, $bind_dn, $bind_pw); // or die('Failed to bind');
        if (!$b) {
            ldap_close($h);
            return NULL;
        }

        $results = ldap_search($h, $base_dn, '(samaccountname='.$login.')', array('memberof', 'mail', 'displayname'));
        $entries = ldap_get_entries($h, $results);

        if ($entries['count'] == 0) {
            return NULL; //die('No entries');
        }

        $result['login'] = $login;
        $result['fullname'] = $entries[0]['displayname'][0];
        $result['email'] = $entries[0]['mail'][0];
        $result['admin'] = (in_array($admin_dn, $entries[0]['memberof']) ? 1 : 0);
        $result['ldap'] = 1;

        ldap_close($h);
        return $result;
    }

    private function createFromLdap($login)
    {
        $result = $this->getUserInfoFromLdap($login);
        if ($result != NULL) {
            $result = $this->users->create($result);
        }
        return $result;
    }

}
