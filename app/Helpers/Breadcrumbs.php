<?php
namespace Helpers;

class Breadcrumbs
{
    private static $instance = NULL;
    private $crumbs = array();

    private function __construct()
    {
    }

    public static function instance()
    {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function add($href = "", $name = "")
    {
        self::instance()->_add($href, $name);
    }

    public static function get()
    {
        return self::instance()->crumbs;
    }

    private function _add($href, $name)
    {
        $crumb = new \stdClass();
        $crumb->href = $href;
        $crumb->name = $name;
        $crumb->active = ($crumb->href == "");
        $this->crumbs[] = $crumb;
    }
}
