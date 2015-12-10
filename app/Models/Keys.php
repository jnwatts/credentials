<?php
namespace Models;

use Core\Model;

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
        $result = $this->db->select('SELECT * FROM '.$this->keysTable.' WHERE `user_id`='.$user->id.' ORDER BY `host`');
        foreach ($result as $i => $k) {
            $result[$i] = new \Models\Key($k);
        }
        return $result;
    }

    function getById($id)
    {
        $result = $this->db->select('SELECT * FROM '.$this->keysTable.' WHERE `id`='.$id);
        if (count($result) >= 1) {
            $result = new \Models\Key($result[0]);
        } else {
            $result = NULL;
        }
    }

    function getByUserHost($user, $host)
    {
        $result = $this->db->select('SELECT * FROM '.$this->keysTable.' WHERE `host` LIKE \''.$host.'\' AND `user_id`='.$user->id);
        if (count($result) >= 1) {
            $result = new \Models\Keys($result[0]);
        } else {
            $result = NULL;
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
        $result = $this->db->select('SELECT `host` FROM '.$this->keysTable.' WHERE `user_id`='.$user->id);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $hosts[] = $row->host;
            }
        }
        return $hosts;
    }
}
