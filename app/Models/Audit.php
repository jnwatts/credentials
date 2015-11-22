<?php
namespace Models;

use Core\Model;

class Audit extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    function log($who, $what, $extra = NULL)
    {
        if ($extra != NULL) {
            $extra = json_encode($extra);
        }
        $this->db->insert(PREFIX.'audit', array(
                    '_when' => time(),
                    '_who' => $who, 
                    '_what' => $what,
                    '_extra' => $extra));
    }

    function get($since = 0)
    {
        $sql = 'SELECT * FROM '.PREFIX.'audit';
        if ($since != 0)
            $sql .= ' WHERE _when > ' . strval(time() - $since);
        return $this->db->select($sql);
    }
}
