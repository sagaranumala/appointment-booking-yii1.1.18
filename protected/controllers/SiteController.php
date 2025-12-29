<?php
class SiteController extends Controller
{

    public function beforeAction($action)
{
    // Only set it once
    // if (!headers_sent()) {
    //     header("Access-Control-Allow-Origin: http://localhost:3000");
    //     header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    //     header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
    // }

    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        Yii::app()->end();
    }

    return parent::beforeAction($action);
}

    /**
     * Declares class-based actions.
     */
    public function actionCsrf()
{
    header('Content-Type: application/json');
    echo CJSON::encode([
        'csrfToken' => Yii::app()->request->csrfToken,
        'csrfTokenName' => Yii::app()->request->csrfTokenName,
    ]);
    Yii::app()->end();
}

   public function actions()
{
    // Remove the CErrorAction reference completely
    return array();
}


// protected/controllers/SiteController.php

    // ... existing methods ...
    /**
     * GET /site/testJwt
     * Header: Authorization: Bearer <JWT_TOKEN>
     */
    public function actionTestJwt()
    {
        $jwt = Yii::app()->jwt; // JwtHelper component

        $userData = $jwt->getCurrentUser();

        if (!$userData) {
            $jwt->sendResponse(
                false,
                'Invalid or missing JWT token',
                null,
                401
            );
        }

        // Token is valid
        $jwt->sendResponse(
            true,
            'JWT token is valid',
            [
                'user' => $userData,
                'expires_in' => $jwt->getExpiryTime(),
            ],
            200
        );
    }

/**
 * Error action to handle errors
 */
public function actionError()
{
    if ($error = Yii::app()->errorHandler->error) {
        if (Yii::app()->request->isAjaxRequest) {
            echo $error['message'];
        } else {
            $this->render('error', $error);
        }
    }
}
    /**
     * This is the default 'index' action.
     * Redirects to auth/login if not authenticated, dashboard if logged in
     */
    public function actionIndex()
    {
        if (Yii::app()->user->isGuest) {
            $this->redirect(array('auth/login'));  // Redirect to AuthController
        } else {
            $this->redirect(array('site/dashboard'));
        }
    }

    /**
     * Simple Dashboard page (only for logged-in users)
     */
    public function actionDashboard()
    {
        // Check if user is logged in
        if (Yii::app()->user->isGuest) {
            $this->redirect(array('auth/login'));  // Redirect to AuthController
        }
        
        // Get current user
        $user = User::model()->findByPk(Yii::app()->user->id);
        
        // Simple dashboard data
        $dashboardData = array(
            'welcomeMessage' => 'Welcome to your Dashboard',
            'user' => $user,
            'loginTime' => date('Y-m-d H:i:s'),
            'totalUsers' => User::model()->count(),
        );
        
        $this->render('dashboard', $dashboardData);
    }

    /**
     * Logs out the current user and redirect to auth/login
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(array('auth/login'));  // Redirect to AuthController
    }
    
    /**
     * Simple Profile page
     */
    public function actionProfile()
    {
        // Check if user is logged in
        if (Yii::app()->user->isGuest) {
            $this->redirect(array('auth/login'));  // Redirect to AuthController
        }
        
        $user = User::model()->findByPk(Yii::app()->user->id);
        
        if (!$user) {
            throw new CHttpException(404, 'User not found.');
        }
        
        $this->render('profile', array(
            'user' => $user,
        ));
    }

    public function actionSetLanguage($lang)
    {
        $languages = array_keys(Yii::app()->params['languages']);
        
        if (in_array($lang, $languages)) {
            Yii::app()->language = $lang;
            Yii::app()->user->setState('language', $lang);
            setcookie('language', $lang, time() + 86400 * 365, '/'); // 1 year
        }

        // Redirect back to referrer or home page
        $this->redirect(Yii::app()->request->urlReferrer ?: Yii::app()->homeUrl);
    }
public function actionTranslationDebug()
{
    echo "<h1>Translation Debug</h1>";
    
    // Current language
    echo "<h2>Language Settings:</h2>";
    echo "Current language: " . Yii::app()->language . "<br>";
    echo "Source language: " . Yii::app()->sourceLanguage . "<br><br>";
    
    // Check translation file
    $arFile = Yii::getPathOfAlias('application.messages.ar.app') . '.php';
    echo "<h2>File Check:</h2>";
    echo "File path: " . $arFile . "<br>";
    echo "File exists: " . (file_exists($arFile) ? 'YES' : 'NO') . "<br><br>";
    
    if (file_exists($arFile)) {
        $translations = include($arFile);
        echo "<h2>Sample Translations from File:</h2>";
        echo "'Home' => '" . ($translations['Home'] ?? 'NOT FOUND') . "'<br>";
        echo "'Dashboard' => '" . ($translations['Dashboard'] ?? 'NOT FOUND') . "'<br>";
        echo "'Login' => '" . ($translations['Login'] ?? 'NOT FOUND') . "'<br><br>";
    }
    
    // Test Yii::t() directly
    echo "<h2>Direct Translation Test:</h2>";
    echo "Yii::t('app', 'Home'): " . Yii::t('app', 'Home') . "<br>";
    echo "Yii::t('app', 'Dashboard'): " . Yii::t('app', 'Dashboard') . "<br>";
    echo "Yii::t('app', 'Login'): " . Yii::t('app', 'Login') . "<br><br>";
    
    // Check messages component
    echo "<h2>Messages Component:</h2>";
    echo "Messages class: " . get_class(Yii::app()->messages) . "<br>";
    echo "Base path: " . Yii::app()->messages->basePath . "<br>";
    
    // List all available messages
    $messagesPath = Yii::getPathOfAlias('application.messages');
    echo "<h2>Messages Directory Structure:</h2>";
    if (is_dir($messagesPath)) {
        $dirs = scandir($messagesPath);
        foreach ($dirs as $dir) {
            if ($dir != '.' && $dir != '..' && is_dir($messagesPath . '/' . $dir)) {
                echo "Language: $dir<br>";
                $files = scandir($messagesPath . '/' . $dir);
                foreach ($files as $file) {
                    if (strpos($file, '.php') !== false) {
                        echo "&nbsp;&nbsp;- $file<br>";
                    }
                }
            }
        }
    }
}

// In SiteController.php
public function actionApiTest()
{
    error_log("[API TEST] Endpoint called");
    
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'API test works',
        'method' => $_SERVER['REQUEST_METHOD'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    Yii::app()->end();
}
}