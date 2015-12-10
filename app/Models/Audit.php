<?php
namespace Models;

use Core\Model;

class Audit extends Model
{
    private $auditTable;

    function __construct()
    {
        parent::__construct();
        $this->auditTable = '`'.PREFIX.'audit`';
    }

    function log($who, $what, $extra = NULL)
    {
        if ($extra != NULL) {
            $extra = json_encode($extra);
        }
        $this->db->insert($this->auditTable, array(
                    '_when' => time(),
                    '_who' => $who, 
                    '_what' => $what,
                    '_extra' => $extra));
    }

    function get($since = 0, $until = 0)
    {
        $sql = 'SELECT * FROM '.$this->auditTable;
        $where = array();
        if ($since != 0)
            $where[] = '_when >= ' . strval($since);
        if ($until != 0)
            $where[] = '_when <= ' . strval($until);
        if (count($where) > 0)
            $sql .= ' WHERE '.implode(' AND ', $where);
        $results = $this->db->select($sql);
        if (count($results) >= 1) {
            foreach ($results as $k => $v) {
                $results[$k] = new \Models\AuditLogEntry($v);
            }
        } else {
            $results = array();
        }
        return $results;
    }
}
