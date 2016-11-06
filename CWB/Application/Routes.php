<?php

// Main pages
$route->get('/', "indexController@index");

/*
$route->group('GET', '/admin', [
	'/' => "adminController@index",
	'/dashboard' => "adminController@dashboard",
]);
*/

$route->get('/404', "index@index");