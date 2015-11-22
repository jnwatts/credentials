<?php
namespace Models;

use Core\Model;

class Keys extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    function getAllByUser($user)
    {
        return $this->db->select('SELECT * FROM `'.PREFIX.'keys` WHERE `user_id`='.$user->id.' ORDER BY `host`');
    }

    function getById($id)
    {
        return $this->db->select('SELECT * FROM `'.PREFIX.'keys` WHERE `id`='.$id)[0];
    }

    function getByUserHost($user, $host)
    {
        $result = $this->db->select('SELECT * FROM `'.PREFIX.'keys` WHERE `host` LIKE \''.$host.'\' AND `user_id`='.$user->id);
        if (count($result) >= 1) {
            $result = $result[0];
        } else {
            $result = NULL;
        }
        return $result;
    }

    function deleteById($id)
    {
        $result = $this->db->delete(PREFIX.'keys', array('id'=>$id));
    }

    function create($user, $host, $hash)
    {
        $this->db->insert(PREFIX.'keys', array('user_id'=>$user->id, 'host'=>$host, 'hash'=>$hash));
        return $this->getById($this->db->lastInsertId('id'));
    }

    function getHostsByUser($user)
    {
        $hosts = array();
        $result = $this->db->select('SELECT `host` FROM `'.PREFIX.'keys` WHERE `user_id`='.$user->id);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $hosts[] = $row->host;
            }
        }
        return $hosts;
    }
}
