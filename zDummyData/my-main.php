<?php

// Load environment variables (Docker)
$env = getenv();

defined('APP_ENV') or define('APP_ENV', isset($env['APP_ENV']) ? $env['APP_ENV'] : 'dev');
defined('DB_HOST') or define('DB_HOST', isset($env['DB_HOST']) ? $env['DB_HOST'] : 'localhost');
defined('DB_NAME') or define('DB_NAME', isset($env['DB_NAME']) ? $env['DB_NAME'] : '');
defined('DB_USER') or define('DB_USER', isset($env['DB_USER']) ? $env['DB_USER'] : '');
defined('DB_PASS') or define('DB_PASS', isset($env['DB_PASS']) ? $env['DB_PASS'] : '');

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(

		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),

		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
    'urlFormat'=>'path',
    'showScriptName'=>false,
    'rules'=>array(
        // Your API routes - CORRECTED
        'api/users' => 'api/getUsers',
        'api/users/<id:\d+>' => 'api/getUser',
        'api/users/email/<email:.*>' => 'api/getUserByEmail',
        'api/users/userId/<userId:.*>' => 'api/getUserByUserId',
        'api/users/create' => 'api/createUser',
        'api/users/update/<id:\d+>' => 'api/updateUser',
        'api/users/delete/<id:\d+>' => 'api/deleteUser',
        
        // OR using pattern-based rules:
        array('api/users', 'pattern'=>'api/users', 'verb'=>'GET'),
        array('api/user', 'pattern'=>'api/users/<id:\d+>', 'verb'=>'GET'),
        array('api/userByEmail', 'pattern'=>'api/users/email/<email:.*>', 'verb'=>'GET'),
        array('api/userByUserId', 'pattern'=>'api/users/userId/<userId:.*>', 'verb'=>'GET'),
        array('api/createUser', 'pattern'=>'api/users', 'verb'=>'POST'),
        array('api/updateUser', 'pattern'=>'api/users/<id:\d+>', 'verb'=>'PUT'),
        array('api/deleteUser', 'pattern'=>'api/users/<id:\d+>', 'verb'=>'DELETE'),
        
        // Default routes
        '<controller:\w+>/<id:\d+>'=>'<controller>/view',
        '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
        '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
    ),
),
		// database settings are configured in database.php
		'db'=>require(dirname(__FILE__).'/database.php'),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>YII_DEBUG ? null : 'site/error',
		),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),

	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
);
