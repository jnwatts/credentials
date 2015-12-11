<?php
namespace Models;

class AuditLogEntry
{
    function __construct()
    {
    }

    function __toString()
    {
        return json_encode($this);
    }

    function who_id()
    {
        if (preg_match('/^(?<login>[a-zA-Z0-9\.\_\-]+)\((?<user_id>[0-9]+)\)/', $this->_who, $m) > 0) {
            return $m['user_id'];
        } else {
            return NULL;
        }
    }

    function who_login()
    {
        if (preg_match('/^(?<login>[a-zA-Z0-9\.\_\-]+)\((?<user_id>[0-9]+)\)/', $this->_who, $m) > 0) {
            return $m['login'];
        } else {
            return $this->_who;
        }
    }

    function pretty_extra()
    {
        if ($this->_extra != NULL) {
            return json_encode(json_decode($this->_extra), JSON_PRETTY_PRINT);
        } else {
            return NULL;
        }
    }
}

