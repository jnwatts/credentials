<?php
namespace Models;

class Key
{
    function __construct()
    {
    }

    function __toString()
    {
        return $this->host . '(' . $this->id . ')';
    }
}

