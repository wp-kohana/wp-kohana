<?php
/**
 * WordPress did not find a page for this URI in its system.
 * Instead of showing a 404 page, we are actually going to 
 * send the request over to Kohana. It is up to the Kohana 
 * application to show a 404 page when necessary
 */

// Undo the escaping that WordPress does to superglobals
$backup = array(
	'get'    => $_GET,
	'post'   => $_POST,
	'cookie' => $_COOKIE,
	'server' => $_SERVER,
);

$_GET    = stripslashes_deep($_GET);
$_POST   = stripslashes_deep($_POST);
$_COOKIE = stripslashes_deep($_COOKIE);
$_SERVER = stripslashes_deep($_SERVER);

echo Request::factory()->execute()->send_headers()->body();

// Restore the superglobals to the way WordPress wants them
$_GET    = $backup['get'];
$_POST   = $backup['post'];
$_COOKIE = $backup['cookie'];
$_SERVER = $backup['server'];
