<?php
namespace Models;

use Core\Model;
use Helpers\Audit;

class Users extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    function currentUser()
    {
        //TODO: This should be in the controller... then can decouple Models\Config and Models\User (and LDAP and Audit too)
        $user = $this->getByLogin($_SERVER['PHP_AUTH_USER']);
        if ($user == NULL) {
            $user = $this->createFromLogin($_SERVER['PHP_AUTH_USER']);
            Audit::log($user, 'self initialization', $user);
        }
        return $user;
    }

    private function select($where = "")
    {
        $sql = 'SELECT
            u.id AS id,
            u.login AS login,
            u.email AS email,
            u.fullname AS fullname,
            u.admin,
            COUNT(k.id) AS numKeys
                FROM '.PREFIX.'users AS u LEFT JOIN '.PREFIX.'keys AS k ON k.user_id = u.id';
        if (strlen($where) > 0) {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' GROUP BY u.id ORDER BY u.login';
        return $this->db->select($sql);
    }

    function getAll()
    {
        $result = $this->select();
        foreach ($result as $i => $u) {
            $result[$i] = new \Models\User($u);
        }
        return $result;
    }

    function getAllAdmins()
    {
        $result = $this->select('u.admin = 1');
        foreach ($result as $i => $u) {
            $result[$i] = new \Models\User($u);
        }
        return $result;
    }

    function getAllLogins()
    {
        $result = $this->select();
        $logins = array();
        foreach ($result as $row) {
            $logins[] = $row->login;
        }
        return $logins;
    }

    function getByLogin($login)
    {
        $result = $this->select('login="'.$login.'"');
        if (count($result) >= 1) {
            $result = new \Models\User($result[0]);
        } else {
            $result = NULL;
        }
        return $result;
    }

    function getById($id)
    {
        $result = $this->db->select('SELECT * FROM '.PREFIX.'users WHERE id='.$id);
        if (count($result) >= 1) {
            $result = new \Models\User($result[0]);
        } else {
            $result = NULL;
        }
        return $result;
    }

    function getUserInfoFromLdap($login) {
        $config = new Config();
        $ldap_conf_file = $config->get('ldap_conf_file');
        $admin_dn = $config->get('ldap_admin_dn');
        $bind_dn = $config->get('ldap_bind_dn');
        $bind_pw = $config->get('ldap_bind_pw');
        $base_pw = $config->get('ldap_base_dn');
        $ldap_url = $config->get('ldap_url');

/*
        $lines = file($ldap_conf_file);
        foreach ($lines as $line) {
            preg_match('/^([^\s]+)\s+"?([^"]*)"?$/', $line, $m);
            $ldap_conf[strtoupper($m[1])] = trim($m[2]);
        }
        $ldap_url = $ldap_conf['AUTHLDAPURL'];
        $bind_dn = $ldap_conf['AUTHLDAPBINDDN'];
        $bind_pw = $ldap_conf['AUTHLDAPBINDPASSWORD'];
        */
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

        ldap_close($h);
        return $result;
    }

    function createFromLogin($login)
    {
        $result = $this->getUserInfoFromLdap($login);
        if ($result != NULL) {
            $this->db->insert(PREFIX.'users', $result);
            $result = $this->getById($this->db->lastInsertId('id'));
        }
            /*
            $this->db->insert(PREFIX.'users', array('login' => $login));
            $result = $this->getById($this->db->lastInsertId('id'));
            */
        
        return $result;
    }

    function deleteById($id)
    {
        $this->db->delete(PREFIX.'users',  array('id' => $id));
    }

    function deleteAll()
    {
        $this->db->query('DELETE FROM '.PREFIX.'users');
    }

    function update($id, $data)
    {
        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }
        $this->db->update(PREFIX.'users', $data, array('id' => $id));
    }

    function setAdmin($id, $val)
    {
        $user = $this->getById($id);
        if ($user != NULL) {
            $this->db->update(PREFIX.'users', array('admin' => $val), array('id' => $id));
        }
    }
}
