<?php
namespace Models;

use Core\Model;
use Helpers\Audit;
use PDO;

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

    private function select($where = "", $array = array())
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
        return $this->db->select(
                $sql,
                $array,
                PDO::FETCH_CLASS,
                'Models\User'
                );
    }

    function getAll()
    {
        return $this->select();
    }

    function getAllAdmins()
    {
        return $this->select('u.admin = :admin', array(':admin' => 1));
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
        $result = $this->select('u.login = :login', array(':login' => $login));
        if (count($result) <= 0) {
            $result = NULL;
        } else {
            $result = $result[0];
        }
        return $result;
    }

    function getById($id)
    {
        $result = $this->select('u.id = :id', array(':id' => $id));
        if (count($result) <= 0) {
            $result = NULL;
        } else {
            $result = $result[0];
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
