<?php
namespace Models;

class Key
{
    function __construct($db_result)
    {
        $vars = get_object_vars($db_result);
        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }
    }

    function __toString()
    {
        return $this->host . '(' . $this->id . ')';
    }
}

