#!/usr/bin/php
<?php
chdir(realpath(dirname(__FILE__)));

if (posix_isatty(STDIN)) {
    define('DEBUG', true);
} else {
    define('DEBUG', false);
}

/**
 * SimpleMVC specifed directory default is '.'
 * If app folder is not in the same directory update it's path
 */
$smvc = realpath(dirname(__FILE__));

/** Set the full path to the docroot */
define('ROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

/** Make the application relative to the docroot, for symlink'd index.php */
if (!is_dir($smvc) and is_dir(ROOT.$smvc)) {
    $smvc = ROOT.$smvc;
}

/** Define the absolute paths for configured directories */
define('SMVC', realpath($smvc).DIRECTORY_SEPARATOR);

/** Unset non used variables */
unset($smvc);

require SMVC.'vendor/autoload.php';

function dbg($msg) {
    if (DEBUG) {
        echo $msg."\n";
    }
}

set_exception_handler(NULL);
set_error_handler(NULL);

use Helpers\Audit;

$core_config = new \Core\Config();

$config = new \Models\Config();
$users = new \Models\Users();
$keys = new \Models\Keys();
$audit = new \Models\Audit();
use Helpers\User;

ob_end_flush();

function usage() {
    dbg('Usage: ' . $argv[0] . ' <action> [<args...>]');
    dbg(' get [<name>]          Get config <name> (or all if <name> empty)');
    dbg(' set <name> <value>    Set config <name> to <value>');
	dbg(' import <json>			Import config from json string');
    dbg(' admin [<login>]       Make <login> an admin (or list admins if <login> empty)');
    dbg(' user [<login>]        Show user <login> (or list all if <login> empty)');
    dbg(' keys <login>          Show keys of user <login>');
    dbg(' delete_all_users_keys Delete ALL users and keys (DANGER!!)');
    dbg(' audit [<since>]       Show audit log (or limit to <since>: [<days>D][<hours>H][<minutes>M])');
    dbg(' init_database         Initial database tables (DANGER!! This includes audit and config!!)');
    exit(1);
}

function show_vars($vars) {
	dbg(json_encode($vars, JSON_PRETTY_PRINT));
}

function show_user($user) {
	dbg(json_encode($user));
}

function show_key($key) {
	dbg(json_encode($key));
}

function show_audit($log) {
    $info = date('c', $log->_when);
    $info .= ' ' . $log->_who;
    $info .= ': ' . $log->_what;
    if ($log->_extra != "")
        $info .= ' context:' . $log->_extra;
    dbg($info);
}

$argn = count($argv);
if ($argn < 2)
    usage();

$action = $argv[1];

if ($argn >= 3)
    $var = $argv[2];
else
    $var = NULL;

if ($argn >= 4)
    $val = $argv[3];
else
    $val = NULL;

if ($action == "set") {
    if ($var == NULL) {
        dbg("Missing config argument");
        usage();
    } else if ($val == NULL) {
        dbg("Missing value argument");
        usage();
    }
    $config->set($var, $val);
    Audit::log('console', 'set config', array('name' => $var, 'value' => $val));
} else if ($action == "import") {
	if ($var == NULL) {
		dbg("Missing data");
		usage();
	}
	$data = json_decode($var, true);
	foreach ($data as $name => $value) {
		if ($value != NULL)
			$config->set($name, $value);
	}
} else if ($action == "get") {
    if ($var == NULL) {
        $result = $config->getAll();
        if ($result != NULL) {
			show_vars($result);
        }
    } else {
        $result = $config->get($var);
        show_var($var, $result);
    }
} else if ($action == "admin") {
    if ($var == NULL) {
        $result = $users->getAllAdmins();
        foreach ($result as $user) {
            show_user($user);
        }
    } else {
        $user = $users->getByLogin($var);
        if ($user != NULL) {
            Audit::log('console', 'set user admin ' . $user->login.'('.$user->id.')');
            $users->setAdmin($user->id, 1);
        } else {
            dbg('Login not found');
            exit(1);
        }
    }
} else if ($action == "user") {
    if ($var == NULL) {
        $result = User::instance()->get();
        foreach ($result as $user) {
            show_user($user);
        }
    } else {
		$user = User::instance()->find($var);
        if ($user != NULL) {
            show_user($user);
        } else {
            dbg('Login not found');
            exit(1);
        }
    }
} else if ($action == "keys") {
	if ($var == NULL) {
		dbg("Missing login");
		usage();
	}
	$user = $users->getByLogin($var);
	if ($user == NULL) {
		dbg('Login not found');
		exit(1);
	}
	$keys = $keys->getAllByuser($user);
	foreach ($keys as $key) {
		show_key($key);
	}
} else if ($action == "delete_all_users_keys") {
    $users->deleteAll();
    Audit::log('console', 'delete all users');
} else if ($action == "audit") {
    $since = 0;
    if ($var != NULL) {
        preg_match_all('/([0-9]+[DHM])/i', strtolower($var), $matches);
        foreach ($matches[0] as $m) {
            $var = substr($m, -1);
            $val = (substr($m, 0, strlen($m) - 1));
            switch ($var) {
                case 'd':
                    $val *= 24;
                case 'h':
                    $val *= 60;
                case 'm':
                    $val *= 60;
            }
            $since += $val;
        }
    }
    $result = $audit->get($since);
    foreach ($result as $row) {
        show_audit($row);
    }
} else if ($action == "init_database") {
    $database = Helpers\Database::get();

    $sql = <<<SQL
DROP TABLE IF EXISTS `%PREFIX%audit`;
CREATE TABLE `%PREFIX%audit` (
  `id` int(11) NOT NULL,
  `_when` int(11) NOT NULL,
  `_who` varchar(32) NOT NULL,
  `_what` varchar(64) NOT NULL,
  `_extra` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%PREFIX%config`;
CREATE TABLE `%PREFIX%config` (
  `name` varchar(32) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%PREFIX%keys`;
CREATE TABLE `%PREFIX%keys` (
  `id` int(11) NOT NULL,
  `host` varchar(32) NOT NULL,
  `hash` text,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%PREFIX%users`;
CREATE TABLE `%PREFIX%users` (
  `id` int(11) NOT NULL,
  `login` varchar(32) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `fullname` varchar(128) DEFAULT NULL,
  `admin` int(11) NOT NULL DEFAULT '0',
  `ldap` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `%PREFIX%audit`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `%PREFIX%config`
  ADD PRIMARY KEY (`name`);

ALTER TABLE `%PREFIX%keys`
  ADD PRIMARY KEY (`id`,`user_id`) USING BTREE,
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `%PREFIX%users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `%PREFIX%audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `%PREFIX%keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `%PREFIX%users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
SQL;

    $sql = str_replace('%PREFIX%', PREFIX, $sql);

    $result = $database->raw($sql);
    if ($result === false) {
        echo "Failed to init database";
    }
} else {
    echo "Unexpected command";
    usage();
}
