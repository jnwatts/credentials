<?php
namespace Models;

use Core\Model;

class Config extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    private $config_names = array(
        'key_export_path',
        'key_export_cmd',
        'ldap_conf_file',
        'ldap_admin_dn',
        'ldap_bind_dn',
        'ldap_bind_pw',
        'ldap_base_dn',
        'ldap_url',
        );


    public function get($name) {
        $sql = 'SELECT `value` from `'.PREFIX.'config` WHERE `name`="'.$name.'"';
        $result = $this->db->select($sql);
        if (count($result) >= 1) {
            $result = $result[0]->value;
        } else {
            $result = NULL;
        }
        return $result;
    }

    public function getAll() {
        $sql = 'SELECT `name`, `value` from `'.PREFIX.'config`';
        $result = $this->db->select($sql);
        if (count($result) <= 0) {
            $result = NULL;
        }
        return $result;
    }

    public function set($name, $value) {
        $result = $this->get($name);
        if (in_array($name, $this->config_names)) {
            if ($result == NULL) {
                $this->db->insert(PREFIX.'config', array('name' => $name, 'value' => $value));
            } else {
                $this->db->update(PREFIX.'config', array('value' => $value), array('name' => $name));
            }
        }
    }
}
