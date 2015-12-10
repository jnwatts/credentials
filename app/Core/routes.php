<?php
/**
 * Routes - all standard routes are defined here.
 *
 * @author David Carr - dave@daveismyname.com
 * @version 2.2
 * @date updated Sept 19, 2015
 */

/** Create alias for Router. */
use Core\Router;
use Helpers\Hooks;

/** Define routes. */
Router::any('', 'Controllers\Users@index');
Router::post('users', 'Controllers\Users@create');
Router::get('users', 'Controllers\Users@index');
Router::post('users/(:num)', 'Controllers\Users@update');
Router::get('users/(:num)', 'Controllers\Users@index');
Router::delete('users/(:num)', 'Controllers\Users@delete');

Router::post('keys', 'Controllers\Keys@create');
Router::post('keys/(:num)', 'Controllers\Keys@update');
Router::get('keys/(:num)', 'Controllers\Keys@index');
Router::delete('keys/(:num)', 'Controllers\Keys@delete');

Router::get('audit', 'Controllers\Audit@index');
Router::get('audit/(:num)', 'Controllers\Audit@index');
Router::get('audit/(:num)-(:num)', 'Controllers\Audit@index');


/** Module routes. */
$hooks = Hooks::get();
$hooks->run('routes');

/** If no route found. */
Router::error('Core\Error@index');

/** Turn on old style routing. */
Router::$fallback = false;

/** Execute matched routes. */
Router::dispatch();
