<?php
namespace Models;

class User
{
    function __construct($user)
    {
        $vars = get_object_vars($user);
        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }
    }

    function isAdmin()
    {
        return ($this->admin == 1);
    }
}

