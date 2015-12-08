<?php
namespace Models;

use Core\Model;

class Config extends Model
{
    private $configTable;

    function __construct()
    {
        parent::__construct();
        $this->configTable = '`'.PREFIX.'config`';
    }

    private $config_names = array(
        'key_export_path',
        'key_export_cmd',
        'ldap_admin_dn',
        'ldap_bind_dn',
        'ldap_bind_pw',
        'ldap_base_dn',
        'ldap_url',
        );


    public function get($name) {
        $sql = 'SELECT `value` from '.$this->configTable.' WHERE `name`="'.$name.'"';
        $result = $this->db->select($sql);
        if (count($result) >= 1) {
            $result = $result[0]->value;
        } else {
            $result = NULL;
        }
        return $result;
    }

    public function getAll() {
        $sql = 'SELECT `name`, `value` from '.$this->configTable;
        $result = $this->db->select($sql);
        $results = array_fill_keys($this->config_names, NULL);
        if ($result != NULL) {
            if (count($result) > 0) {
                foreach ($result as $row) {
                    $results[$row->name] = $row->value;
                }
            }
        }
        return $results;
    }

    public function set($name, $value) {
        $result = $this->get($name);
        if (in_array($name, $this->config_names)) {
            if ($result == NULL) {
                $this->db->insert($this->configTable, array('name' => $name, 'value' => $value));
            } else {
                $this->db->update($this->configTable, array('value' => $value), array('name' => $name));
            }
        }
    }
}
