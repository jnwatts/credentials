<?php
namespace Helpers;

class Audit
{
    private static $instance = NULL;

    private function __construct()
    {
        $this->audit = new \Models\Audit();
    }

    public static function get()
    {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function log($user, $what, $extra = NULL)
    {
        $who = (string)$user;
        self::get()->audit->log($who, $what, $extra);
    }
}
