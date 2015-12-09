<?php
namespace Models;

use Core\Model;
use Helpers\Audit;

class Users extends Model
{
    private $usersTable;
    private $keysTable;

    function __construct()
    {
        parent::__construct();
        $this->usersTable = '`'.PREFIX.'users`';
        $this->keysTable = '`'.PREFIX.'keys`';
    }

    private function select($where = "")
    {
        $sql = 'SELECT
            u.id AS id,
            u.login AS login,
            u.email AS email,
            u.fullname AS fullname,
            u.admin,
            u.ldap,
            COUNT(k.id) AS numKeys
                FROM '.$this->usersTable.' AS u LEFT JOIN '.$this->keysTable.' AS k ON k.user_id = u.id';
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
        $result = $this->select('login='.$this->db->quote($login));
        if (count($result) >= 1) {
            $result = new \Models\User($result[0]);
        } else {
            $result = NULL;
        }
        return $result;
    }

    function getById($id)
    {
        $result = $this->db->select('SELECT * FROM '.$this->usersTable.' WHERE id='.$this->db->quote($id, \PDO::PARAM_INT));
        if (count($result) >= 1) {
            $result = new \Models\User($result[0]);
        } else {
            $result = NULL;
        }
        return $result;
    }

    function deleteById($id)
    {
        $this->db->delete($this->usersTable,  array('id' => $id));
    }

    function deleteAll()
    {
        $this->db->query('DELETE FROM '.$this->usersTable.'');
    }

    function update($id, $data)
    {
        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }
        $this->db->update($this->usersTable, $data, array('id' => $id));
    }

    function create($data)
    {
        $this->db->insert($this->usersTable, $data);
        return $this->getById($this->db->lastInsertId('id'));
    }

    function setAdmin($id, $val)
    {
        $user = $this->getById($id);
        if ($user != NULL) {
            $this->db->update($this->usersTable, array('admin' => $val), array('id' => $id));
        }
    }
}
