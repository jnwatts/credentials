<?php
namespace Models;

use Core\Model;
use PDO;

class Keys extends Model
{
    private $keysTable;

    function __construct()
    {
        parent::__construct();
        $this->keysTable = '`'.PREFIX.'keys`';
    }

    function getAllByUser($user)
    {
        $result = $this->db->select(
                'SELECT * FROM '.$this->keysTable.' WHERE `user_id` = :user_id ORDER BY `host`',
                array(':user_id' => $user->id),
                PDO::FETCH_CLASS,
                'Models\Key'
                );
        return $result;
    }

    function getById($id)
    {
        $result = $this->db->select(
                'SELECT * FROM '.$this->keysTable.' WHERE `id` = :id',
                array(':id' => $id),
                PDO::FETCH_CLASS,
                'Models\Key'
                );
        if (count($result) <= 0) {
            $result = NULL;
        } else {
            $result = $result[0];
        }
        return $result;
    }

    function getByUserHost($user, $host)
    {
        $result = $this->db->select(
                'SELECT * FROM '.$this->keysTable.' WHERE `host` LIKE :host AND `user_id` = :user_id',
                array(':host' => $host, ':user_id' => $user->id),
                PDO::FETCH_CLASS,
                'Models\Key'
                );
        if (count($result) <= 0) {
            $result = NULL;
        } else {
            $result = $result[0];
        }
        return $result;
    }

    function deleteById($id)
    {
        $this->db->delete($this->keysTable, array('id'=>$id));
    }

    function create($user, $host, $hash)
    {
        $this->db->insert($this->keysTable, array('user_id'=>$user->id, 'host'=>$host, 'hash'=>$hash));
        return $this->getById($this->db->lastInsertId('id'));
    }

    function getHostsByUser($user)
    {
        $hosts = array();
        $result = $this->db->select(
                'SELECT `host` FROM '.$this->keysTable.' WHERE `user_id` = :user_id',
                array(':user_id' => $user->id),
                PDO::FETCH_CLASS,
                'Models\Key'
                );
        if (count($result) > 0) {
            foreach ($result as $row) {
                $hosts[] = $row->host;
            }
        }
        return $hosts;
    }
}
