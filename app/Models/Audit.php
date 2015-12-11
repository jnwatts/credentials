<?php
namespace Models;

use Core\Model;
use PDO;

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

    function get($since = 0, $until = NULL)
    {
        if ($until == NULL) {
            $until = time();
        }
        $results = $this->db->select(
                'SELECT * FROM '.$this->auditTable.' WHERE _when >= :since AND _when <= :until',
                array(':since' => intval($since), ':until' => intval($until)),
                PDO::FETCH_CLASS,
                'Models\AuditLogEntry'
                );
        return $results;
    }
}
