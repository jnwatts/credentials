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
        return $this->db->select('SELECT * FROM '.$this->keysTable.' WHERE `user_id`='.$user->id.' ORDER BY `host`');
    }

    function getById($id)
    {
        return $this->db->select('SELECT * FROM '.$this->keysTable.' WHERE `id`='.$id)[0];
    }

    function getByUserHost($user, $host)
    {
        $result = $this->db->select('SELECT * FROM '.$this->keysTable.' WHERE `host` LIKE \''.$host.'\' AND `user_id`='.$user->id);
        if (count($result) >= 1) {
            $result = $result[0];
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
