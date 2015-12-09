#!/usr/bin/php
<?php

if (!posix_access(__FILE__, POSIX_X_OK))
	die("Access denied\n");

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

use \Helpers\Audit;

function dbg($msg) {
    if (DEBUG) {
        echo $msg."\n";
    }
}

function key_abs_path($filename) {
    global $export_path;
    return $export_path . '/' . $filename;
}

function key_has_changed($filename, $key) {
    $hash = file_get_contents($filename);
    return ($hash != $key->hash);
}

function get_user_from_filename($filename) {
    $matches = array();
    preg_match('/([^@\/]+)@[^.]+.pub$/', $filename, $matches);
    return $matches[1];
}

function get_host_from_filename($filename) {
    $matches = array();
    preg_match('/@([^.]+).pub$/', $filename, $matches);
    return $matches[1];
}

function find_existing_logins($export_path) {
    $filenames = glob($export_path . '/*@*.pub');
    $users = array();
    foreach ($filenames as $filename) {
        $users[] = get_user_from_filename($filename);
    }
    return array_unique($users);
}

function find_existing_keys($export_path, $login) {
    return glob($export_path . '/' . $login . '@*.pub');
}

function find_existing_hosts($export_path, $login) {
    $hosts = array();
    $filenames = find_existing_keys($export_path, $login);
    foreach ($filenames as $filename) {
        $hosts[] = get_host_from_filename($filename);
    }
    return $hosts;
}

function get_key_filename($user, $key) {
    return $user->login . '@' . $key->host . '.pub';
}

function update_keys($users, $keys, $export_path, $dry_run = false) {
    $num_changed = 0;
    
    /* Check for removed users */
    $db_logins = $users->getAllLogins();
    $fs_logins = find_existing_logins($export_path);
    $missing_logins = array_diff($fs_logins, $db_logins);
    foreach ($missing_logins as $login) {
        $fs_hosts = find_existing_hosts($export_path, $login);
        foreach ($fs_hosts as $host) {
            $filename = $login . '@' . $host . '.pub';
            $abs_filename = key_abs_path($filename);
            $log = ' REMOVED ' . $filename;
            dbg($log);
            $num_changed = $num_changed + 1;
            if (!$dry_run) {
                Audit::log('exportkeys', $log);
                unlink($abs_filename);
            }
        }
    }

    foreach ($users->getAll() as $user) {
        /* Check for removed keys */
        $db_hosts = $keys->getHostsByUser($user);
        $fs_hosts = find_existing_hosts($export_path, $user->login);
        $missing_hosts = array_diff($fs_hosts, $db_hosts);
        foreach ($missing_hosts as $host) {
            $filename = $user->login . '@' . $host . '.pub';
            $abs_filename = key_abs_path($filename);
            $log = ' REMOVED ' . $filename;
            dbg($log);
            $num_changed = $num_changed + 1;
            if (!$dry_run) {
                Audit::log('exportkeys', $log);
                unlink($abs_filename);
            }
        }

        /* Check for new and modified keys */
        $user_keys = $keys->getAllByUser($user);
        foreach ($user_keys as $key) {
            $filename = get_key_filename($user, $key);
            $abs_filename = key_abs_path($filename);
            if (!file_exists($abs_filename))
                $reason = 'NEW';
            else if (key_has_changed($abs_filename, $key))
                $reason = 'CHANGED';
            else
                continue;
            $log = ' '.$reason.' '.$filename;
            dbg($log);
            $num_changed = $num_changed + 1;
            if (!$dry_run) {
                $f = fopen($abs_filename, "w");
                if ($f) {
                    Audit::log('exportkeys', $log);
                    fwrite($f, $key->hash);
                    fclose($f);
                }
            }
        }
    }
    return $num_changed;
}

set_exception_handler(NULL);
set_error_handler(NULL);

$core_config = new \Core\Config();

$config = new \Models\Config();
$users = new \Models\Users();
$keys = new \Models\Keys();

$export_path = $config->get("key_export_path");
$export_cmd = $config->get("key_export_cmd");
ob_end_flush();


if (DEBUG) {
    dbg('Updating keys in ' . $export_path . '...');
    $num_changed = update_keys($users, $keys, $export_path, true);
    if ($num_changed <= 0) {
        dbg('No changes necessary');
        exit(0);
    }
    dbg("\n".'Continue? (y|N)');
    $answer = fgets(STDIN);
    if (trim(strtolower($answer)) != 'y') {
        exit(0);
    }
}

$num_changed = update_keys($users, $keys, $export_path);
if ($num_changed <= 0) {
    dbg('No changes necessary');
    exit(0);
} else {
    dbg('Invoking ' . $export_cmd . '...');
    system($export_cmd);
}

dbg('Done.');
