<?php
// Load environment variables (Docker)
$env = getenv();

defined('APP_ENV') or define('APP_ENV', $env['APP_ENV'] ?? 'dev');
defined('DB_HOST') or define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
defined('DB_NAME') or define('DB_NAME', $env['DB_NAME'] ?? '');
defined('DB_USER') or define('DB_USER', $env['DB_USER'] ?? '');
defined('DB_PASS') or define('DB_PASS', $env['DB_PASS'] ?? '');
defined('SMTP_EMAIL') or define('SMTP_EMAIL', $env['SMTP_EMAIL'] ?? '');
defined('SMTP_PASSWORD') or define('SMTP_PASSWORD', $env['SMTP_PASSWORD'] ?? '');

// ---------------------------------------------------
// MAIN CONFIG
// ---------------------------------------------------
return array(

    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'My Web Application',

    'sourceLanguage' => 'en',
    'language' => 'en',

    // ---------------------------------------------------
    // IMPORTS
    // ---------------------------------------------------
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.controllers.*',
        'application.vendor.sonata-project.google-authenticator.*',
        // 'application.behaviors.*' only if using behaviors
        // 'application.extensions.*' only if using extensions
    ),

    // ---------------------------------------------------
    // MODULES
    // ---------------------------------------------------
    'modules' => array(
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => 'gii123',
            'ipFilters' => array('*'), // Allow all IPs for dev; restrict in production
        ),
    ),

    // ---------------------------------------------------
    // COMPONENTS
    // ---------------------------------------------------
    'components' => array(

        // User - optional for API-only backend
        'user' => array(
            'allowAutoLogin' => true, 
        ),

        // JWT helper
        'jwt' => array(
            'class' => 'application.components.JwtHelper',
            'secretKey' => getenv('JWT_SECRET') ?: 'your-super-secret-jwt-key-2024',
            'expireTime' => 86400,
        ),

        // Email
        'email' => array(
            'class' => 'application.components.EmailComponent',
            'smtpHost' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
            'smtpUsername' => SMTP_EMAIL,
            'smtpPassword' => SMTP_PASSWORD,
            'fromEmail' => 'noreply@yourdomain.com',
            'fromName' => 'Digital systems',
        ),

        // URL Manager
        'urlManager' => array(
            'urlFormat' => 'get', // Use 'path' for pretty URLs if needed
            'showScriptName' => true,
            'rules' => array(
                // Blog
                'blogs' => 'blog/index',
                'blog/<id:\d+>' => 'blog/view',
                'blog/create' => 'blog/create',
                'blog/update/<id:\d+>' => 'blog/update',
                'blog/delete/<id:\d+>' => 'blog/delete',
                'my-blogs' => 'blog/myBlogs',

                // Aliases
                'article/<id:\d+>' => 'blog/view',
                'posts' => 'blog/index',
                'write' => 'blog/create',

                // Pages
                'calculator' => 'site/simpleCalc',
                'calc' => 'site/simpleCalc',
                'test-db' => 'site/testDb',

                // API
                'api/users' => 'api/getUsers',
                'api/users/<id:\d+>' => 'api/getUser',
                'api/users/email/<email:.*>' => 'api/getUserByEmail',
                'api/users/userId/<userId:.*>' => 'api/getUserByUserId',
                'api/users/create' => 'api/createUser',
                'api/users/update/<id:\d+>' => 'api/updateUser',
                'api/users/delete/<id:\d+>' => 'api/deleteUser',

                // Default fallback - MUST BE LAST
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),

        // Messages / translations - optional
        'messages' => array(
            'class' => 'CPhpMessageSource',
            'basePath' => dirname(__FILE__) . '/../messages', 
            'forceTranslation' => true,
            'cachingDuration' => 0, // Disable cache for dev
        ),

// lat working
//         'request' => [
//     'enableCsrfValidation' => true,
//     'csrfCookie' => [
//       'httpOnly' => false, // JS must read it
//       'secure' => false,   // true in HTTPS
//       'sameSite' => 'Lax',
//     ],
//   ],

        'request' => [
    'enableCsrfValidation' => true,
    'csrfTokenName' => 'YII_CSRF_TOKEN',
    'csrfCookie' => [
        'httpOnly' => true,     // ✅ correct
        'secure' => false,      // true in HTTPS
        // 'sameSite' => 'Lax',
    ],
],


        // Database
        'db' => require(dirname(__FILE__) . '/database.php'),

        // Error handler
        'errorHandler' => array(
            'errorAction' => 'site/error', // Customize JSON output if needed
        ),

        // Logging
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            ),
        ),
    ),

    // ---------------------------------------------------
    // PARAMETERS
    // ---------------------------------------------------
    'params' => array(
        'adminEmail' => 'webmaster@example.com',
        'salt' => 'your-secret-salt-string',
        'jwtSecret' => 'your-super-secret-jwt-key-32-chars-minimum!',

        'languages' => array(
            'en' => 'English',
            'ar' => 'العربية',
        ),
        'defaultLanguage' => 'en',
    ),
);
