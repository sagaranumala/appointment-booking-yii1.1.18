<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\Cors;
use app\models\LoginForm;
use app\models\User;

class AuthController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 86400,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * API Login endpoint
     * POST /index.php?r=auth/login
     */
    public function actionLogin()
    {
        $request = Yii::$app->request;
        
        // Check if it's a POST request
        if (!$request->isPost) {
            return [
                'success' => false,
                'message' => 'Method not allowed. Please use POST.',
                'data' => null
            ];
        }

        // Check if login data is provided
        $email = $request->post('email');
        $password = $request->post('password');
        
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'No login data provided. Please provide email and password.',
                'data' => null
            ];
        }

        $model = new LoginForm();
        $model->email = $email;
        $model->password = $password;

        if ($model->validate() && $model->login()) {
            // Generate JWT token
            $token = $model->generateJwtToken();
            
            if ($token) {
                $user = $model->getUser();
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => Yii::$app->jwt->expireTime,
                        'user' => [
                            'id' => $user->id,
                            'userId' => $user->userId,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'phone' => $user->phone,
                            'createdAt' => $user->createdAt
                        ]
                    ]
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Invalid email or password',
            'data' => null
        ];
    }

    /**
     * API Register endpoint (optional)
     * POST /index.php?r=auth/register
     */
    public function actionRegister()
    {
        $request = Yii::$app->request;
        
        if (!$request->isPost) {
            return [
                'success' => false,
                'message' => 'Method not allowed'
            ];
        }

        $user = new User();
        $user->load($request->post(), '');
        
        if ($user->validate()) {
            $user->setPassword($request->post('password'));
            
            if ($user->save(false)) {
                // Generate JWT token for new user
                $token = Yii::$app->jwt->generateToken(
                    $user->id,
                    $user->email,
                    $user->role,
                    $user->name
                );
                
                return [
                    'success' => true,
                    'message' => 'Registration successful',
                    'data' => [
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'userId' => $user->userId,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'createdAt' => $user->createdAt
                        ]
                    ]
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Registration failed',
            'errors' => $user->errors
        ];
    }

    /**
     * API Validate token endpoint
     * GET /index.php?r=auth/validate
     */
    public function actionValidate()
    {
        $userData = Yii::$app->jwt->getCurrentUser();
        
        if ($userData) {
            return [
                'success' => true,
                'message' => 'Token is valid',
                'data' => $userData
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Invalid or expired token',
            'data' => null
        ];
    }

    /**
     * API Logout endpoint
     * POST /index.php?r=auth/logout
     */
    public function actionLogout()
    {
        // With JWT, logout is handled client-side by discarding the token
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
}