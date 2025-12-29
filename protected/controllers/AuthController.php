<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use RobThree\Auth\TwoFactorAuth;

class AuthController extends BaseApiController
{
    /**
     * Disable CSRF for API actions
     */
    public function beforeAction($action)
    {
        $apiActions = [
            'signup', 'login', 'refresh', 'check', 'profile',
            'logout', 'forgotPassword', 'resetPassword', 'updatePassword',
            'enable2FA', 'confirm2FA'
        ];
        if (in_array($action->id, $apiActions)) {
            Yii::app()->request->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionCsrf()
    {
        Yii::app()->request->enableCsrfValidation = true;

        $csrfName = Yii::app()->request->csrfTokenName;
        $csrfToken = Yii::app()->request->getCsrfToken();

        $this->sendJson([
            'csrfName' => $csrfName,
            'csrfToken' => $csrfToken,
        ]);
    }

    /**
     * POST /auth/enable2FA
     * Generates QR code & secret for 2FA setup
     */
// working 2fa
//     public function actionEnable2FA()
// {
//     $currentUser = Yii::app()->jwt->getCurrentUser();
//     if (!$currentUser) {
//         return $this->sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
//     }

//     $user = User::model()->findByPk($currentUser['userId']);
//     if (!$user) {
//         return $this->sendJson(['success' => false, 'message' => 'User not found'], 404);
//     }

//     // The class should be autoloaded by Composer
//     // $ga = new PHPGangsta_GoogleAuthenticator();
//     // Use RobThree/TwoFactorAuth library (better compatibility)
//     $tfa = new \RobThree\Auth\TwoFactorAuth('Inventory System', 6, 30, 'sha1');
    
//     $secret = $tfa->createSecret();
//     $user->twosecret2FA = $secret;
//     $user->two2FA = 0; // Not confirmed yet

//     if (!$user->save()) {
//         return $this->sendJson([
//             'success' => false,
//             'message' => 'Failed to enable 2FA',
//             'errors' => $user->getErrors()
//         ], 500);
//     }

//     // Generate QR code - this library creates better formatted QR codes
//     $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($user->email, $secret);

//     return $this->sendJson([
//         'success' => true,
//         'message' => '2FA setup created',
//         'data' => [
//             'qrCodeUrl' => $qrCodeUrl,
//             'secret' => $secret
//         ]
//     ]);
// }


    public function actionEnable2FA()
{
    $currentUser = Yii::app()->jwt->getCurrentUser();
    if (!$currentUser) {
        return $this->sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $user = User::model()->findByPk($currentUser['userId']);
    if (!$user) {
        return $this->sendJson(['success' => false, 'message' => 'User not found'], 404);
    }

    // The class should be autoloaded by Composer
    // $ga = new PHPGangsta_GoogleAuthenticator();
    // Use RobThree/TwoFactorAuth library (better compatibility)
    $tfa = new \RobThree\Auth\TwoFactorAuth(
    'Inventory System',
    6,
    30,
    'sha1'
);

$secret = $tfa->createSecret();
$user->twosecret2FA = $secret;
$user->two2FA = 0;

if (!$user->save()) {
    return $this->sendJson([
        'success' => false,
        'message' => 'Failed to enable 2FA',
    ], 500);
}

/**
 * Generate OTPAUTH URI (NOT QR)
 */
$qrCodeUrl = $tfa->getQRText(
        $user->email,        // LABEL (account)
        $secret,             // SECRET
        'Inventory System'   // ISSUER
    );

return $this->sendJson([
    'success' => true,
    'data' => [
        'qrCodeUrl' => $qrCodeUrl,
        'secret' => $secret
    ]
]);


}
    // public function actionEnable2FA()
    // {
    //      if (Yii::app()->request->getRequestType() !== 'POST') {
    //        $this->sendJsonError('POST request required', 405);
    //      }

    //     $currentUser = Yii::app()->jwt->getCurrentUser();
    //     if (!$currentUser) {
    //         return $this->sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $user = User::model()->findByPk($currentUser['userId']);
    //     if (!$user) {
    //         return $this->sendJson(['success' => false, 'message' => 'User not found'], 404);
    //     }

    //     // require_once('PHPGangsta/GoogleAuthenticator.php');
    //     $ga = new PHPGangsta_GoogleAuthenticator();

    //     $secret = $ga->createSecret();
    //     $user->twosecret2FA = $secret;
    //     $user->two2FA = 0; // Not confirmed yet

    //     if (!$user->save()) {
    //         return $this->sendJson([
    //             'success' => false,
    //             'message' => 'Failed to enable 2FA',
    //             'errors' => $user->getErrors()
    //         ], 500);
    //     }

    //     $qrCodeUrl = $ga->getQRCodeGoogleUrl('YourAppName', $secret, $user->email);

    //     return $this->sendJson([
    //         'success' => true,
    //         'message' => '2FA setup created',
    //         'data' => [
    //             'qrCodeUrl' => $qrCodeUrl,
    //             'secret' => $secret
    //         ]
    //     ]);
    // }

    /**
     * POST /auth/confirm2FA
     * Confirms the 2FA code
     * Params: two2FAcode
     */
    // public function actionConfirm2FA()
    // {
    //     $currentUser = Yii::app()->jwt->getCurrentUser();
    //     if (!$currentUser) {
    //         return $this->sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $user = User::model()->findByPk($currentUser['userId']);
    //     if (!$user) {
    //         return $this->sendJson(['success' => false, 'message' => 'User not found'], 404);
    //     }

    //     $two2FAcode = Yii::app()->request->getPost('two2FAcode');
    //     if (!$two2FAcode) {
    //         return $this->sendJson(['success' => false, 'message' => '2FA code required'], 400);
    //     }

    //     require_once('PHPGangsta/GoogleAuthenticator.php');
    //     $ga = new PHPGangsta_GoogleAuthenticator();

    //     if ($ga->verifyCode($user->twosecret2FA, $two2FAcode, 2)) {
    //         $user->two2FA = 1; // mark as confirmed
    //         $user->save();
    //         return $this->sendJson(['success' => true, 'message' => '2FA confirmed']);
    //     }

    //     return $this->sendJson(['success' => false, 'message' => 'Invalid 2FA code'], 401);
    // }


    public function actionVerify2FA()
{
    $currentUser = Yii::app()->jwt->getCurrentUser();
    if (!$currentUser) {
        return $this->sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $user = User::model()->findByPk($currentUser['userId']);
    if (!$user) {
        return $this->sendJson(['success' => false, 'message' => 'User not found'], 404);
    }

      $two2FAcode = Yii::app()->request->getPost('code');
    if (!$two2FAcode) {
        return $this->sendJson(['success' => false, 'message' => '2FA code required'], 400);
    } 

    // Use RobThree/TwoFactorAuth library
    $tfa = new \RobThree\Auth\TwoFactorAuth;

    if ($tfa->verifyCode($user->twosecret2FA, $two2FAcode)) {
        $user->two2FA = 1; // mark as confirmed
        $user->save();
        
        // Generate backup codes
        $backupCodes = [];
        for ($i = 0; $i < 10; $i++) {
            $randomString = bin2hex(random_bytes(4));
            $formattedCode = strtoupper(
                substr($randomString, 0, 4) . '-' . substr($randomString, 4, 4)
            );
            $backupCodes[] = $formattedCode;
        }
        
        return $this->sendJson([
            'success' => true, 
            'message' => '2FA confirmed',
            'data' => [
                'backupCodes' => $backupCodes
            ]
        ]);
    }

    return $this->sendJson(['success' => false, 'message' => 'Invalid 2FA code'], 401);
}


    /**
 * POST /auth/verify2FA
 * Verifies a 2FA code
 * Params: code
 */
public function actionConfirm2FA()
{
    $currentUser = Yii::app()->jwt->getCurrentUser();
    if (!$currentUser) {
        return $this->sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $user = User::model()->findByPk($currentUser['userId']);
    if (!$user) {
        return $this->sendJson(['success' => false, 'message' => 'User not found'], 404);
    }

    $code = Yii::app()->request->getPost('code');
    if (!$code) {
        return $this->sendJson(['success' => false, 'message' => '2FA code required'], 400);
    }

    // require_once('PHPGangsta/GoogleAuthenticator.php');
    // $ga = new PHPGangsta_GoogleAuthenticator();
      $tfa = new \RobThree\Auth\TwoFactorAuth;

    if ($tfa->verifyCode($user->twosecret2FA, $code, 2)) {
        return $this->sendJson(['success' => true, 'message' => '2FA code verified']);
    }

    return $this->sendJson(['success' => false, 'message' => 'Invalid code'], 401);
}


/**
 * GET /auth/check2FAStatus
 * Checks if 2FA is enabled for the user
 */
public function actionCheck2FAStatus()
{
    $currentUser = Yii::app()->jwt->getCurrentUser();
    if (!$currentUser) {
        return $this->sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $user = User::model()->findByPk($currentUser['userId']);
    if (!$user) {
        return $this->sendJson(['success' => false, 'message' => 'User not found'], 404);
    }

    return $this->sendJson([
        'success' => true,
        'data' => [
            'is2FAEnabled' => (bool)$user->two2FA
        ]
    ]);
}

    /**
     * Update login to support 2FA
     */
    public function actionLogin()
    {
        if (!Yii::app()->request->isPostRequest) {
            return $this->sendJson(['success' => false, 'message' => 'POST required'], 405);
        }

        $email = trim(Yii::app()->request->getPost('email', ''));
        $password = Yii::app()->request->getPost('password', '');
        $two2FAcode = Yii::app()->request->getPost('two2FAcode', null);

        if (!$email || !$password) {
            return $this->sendJson(['success' => false, 'message' => 'Email & password required'], 400);
        }

        $user = User::model()->findByAttributes(['email' => $email]);
        if (!$user || !$user->validatePassword($password)) {
            return $this->sendJson(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Check if 2FA is enabled
        if ($user->two2FA == 1) {
            if (!$two2FAcode) {
                return $this->sendJson([
                    'success' => false,
                    'message' => '2FA code required',
                    'require2FA' => true
                ], 401);
            }

            require_once('PHPGangsta/GoogleAuthenticator.php');
            $ga = new PHPGangsta_GoogleAuthenticator();

            if (!$ga->verifyCode($user->twosecret2FA, $two2FAcode, 2)) {
                return $this->sendJson([
                    'success' => false,
                    'message' => 'Invalid 2FA code'
                ], 401);
            }
        }

        $jwt = Yii::app()->jwt;
        $token = $jwt->generateToken([
            'iss' => 'your-app',
            'data' => [
                'userId' => $user->userId,
                'email'  => $user->email,
                'role'   => $user->role,
                'name'   => $user->name
            ]
        ]);

        return $this->sendJson([
            'success' => true,
            'data' => [
                'token' => $token,
                'user'  => array_merge($this->getSafeUserData($user), [
                'two2FA' => (bool)$user->two2FA
                ])
            ]
        ], 200);
        // return $this->sendJson([
        //     'success' => true,
        //     'data' => [
        //         'token' => $token,
        //         'user'  => $this->getSafeUserData($user)
        //     ]
        // ], 200);
    }
    /**
     * POST /auth/signup
     */
    
    /**
     * POST /auth/signup
     * Accepts FormData: SignupForm[name], SignupForm[email], SignupForm[password], etc.
     */
    public function actionSignup()
{
    if (!Yii::app()->request->isPostRequest) {
        return $this->sendJson([
            'success' => false,
            'message' => 'POST request required'
        ], 405);
    }

    // Get FormData array
    $signupData = Yii::app()->request->getPost('SignupForm', []);

    $name     = trim($signupData['name'] ?? '');
    $email    = strtolower(trim($signupData['email'] ?? ''));
    $password = $signupData['password'] ?? '';
    $phone    = $signupData['phone'] ?? '';
    $role     = $signupData['role'] ?? 'user';

    // Validate required fields
    if (!$name || !$email || !$password) {
        return $this->sendJson([
            'success' => false,
            'message' => 'Name, email, and password are required'
        ], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $this->sendJson([
            'success' => false,
            'message' => 'Invalid email format'
        ], 400);
    }

    if (strlen($password) < 6) {
        return $this->sendJson([
            'success' => false,
            'message' => 'Password must be at least 6 characters'
        ], 400);
    }

    if (User::model()->findByAttributes(['email' => $email])) {
        return $this->sendJson([
            'success' => false,
            'message' => 'Email already registered'
        ], 409);
    }

    // Create user
    $user = new User();
    $user->name = $name;
    $user->email = $email;
    $user->password = $password; // hashing should be done in setter
    $user->phone = $phone;
    $user->role = $role;
    $user->status = 1;

    if (!$user->save()) {
        return $this->sendJson([
            'success' => false,
            'message' => 'Registration failed',
            'errors' => $user->getErrors()
        ], 422);
    }

    // Generate JWT
    $jwt = Yii::app()->jwt;
    $token = $jwt->generateToken([
        'iss' => 'your-app',
        'data' => [
            'userId' => $user->userId,
            'email'  => $user->email,
            'role'   => $user->role,
            'name'   => $user->name,
        ]
    ]);

    // Optional: send welcome email (non-blocking)
    try {
        Yii::app()->email->sendWelcomeEmail($user);
    } catch (Exception $e) {
        Yii::log("Failed to send welcome email to {$user->email}: " . $e->getMessage(), 'error', 'application.email');
    }

    // Return success response
    return $this->sendJson([
        'success' => true,
        'message' => 'Registration successful',
        'data' => [
            'token' => $token,
            'user'  => $this->getSafeUserData($user)
        ]
    ], 201);
}



    /**
     * GET /auth/profile
     */
    public function actionProfile()
    {
        $currentUser = Yii::app()->jwt->getCurrentUser();
        if (!$currentUser) {
            Yii::app()->jwt->sendResponse(false, 'Unauthorized', null, 401);
        }
        Yii::app()->jwt->sendResponse(true, 'Profile fetched', $currentUser);
    }

    /**
     * POST /auth/logout
     */
    public function actionLogout()
    {
        Yii::app()->jwt->sendResponse(true, 'Logged out successfully');
    }

    /**
     * POST /auth/updatePassword
     */
    public function actionUpdatePassword()
{
    if (Yii::app()->request->getRequestType() !== 'POST') {
        $this->sendJsonError('POST request required', 405);
    }

    $currentUser = Yii::app()->jwt->getCurrentUser();
    if (!$currentUser) {
        $this->sendJsonError('Authentication required', 401);
    }

    // Get POST parameters
    $currentPassword = trim(Yii::app()->request->getPost('currentPassword', ''));
    $newPassword = trim(Yii::app()->request->getPost('newPassword', ''));

    if (!$currentPassword || !$newPassword) {
        $this->sendJsonError('currentPassword and newPassword are required', 400);
    }

    if (strlen($newPassword) < 6) {
        $this->sendJsonError('New password must be at least 6 characters', 400);
    }

    $user = User::model()->findByAttributes(['userId' => $currentUser['userId']]);
    if (!$user) {
        $this->sendJsonError('User not found', 404);
    }

    $userInfo = [
        'userId' => $user->userId,
        'name' => $user->name,
        'email' => $user->email,
    ];

    if (!$user->validatePassword($currentPassword)) {
        $this->sendJsonError('Current password is incorrect', 403, ['user' => $userInfo]);
    }

    if ($user->validatePassword($newPassword)) {
        $this->sendJsonError('New password must be different', 400, ['user' => $userInfo]);
    }

    // Optional: wrap in transaction
    $transaction = Yii::app()->db->beginTransaction();
    try {
        $user->password = $newPassword;

        if (!$user->save()) {
            throw new CException('Failed to update password: ' . json_encode($user->getErrors()));
        }

        $transaction->commit();

        $this->sendJson([
            'success' => true,
            'message' => 'Password updated successfully',
            'user' => array_merge($userInfo, ['updated_at' => date('Y-m-d H:i:s')])
        ]);
    } catch (Exception $e) {
        $transaction->rollback();
        $this->sendJsonError($e->getMessage(), 400, ['user' => $userInfo]);
    }
}



    /**
     * POST /auth/forgotPassword
     */
    /**
 * POST /auth/forgotPassword
 * Params: email
 */
public function actionForgotPassword()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $email = strtolower(trim($data['email'] ?? ''));

    if (!$email) {
        Yii::app()->jwt->sendResponse(false, 'Email is required', null, 400);
    }

    $user = User::model()->findByAttributes(['email' => $email]);
    if (!$user) {
        Yii::app()->jwt->sendResponse(false, 'Email not found', null, 404);
    }

    // Generate JWT reset token (expires in 1 hour)
    $resetToken = Yii::app()->jwt->generateToken([
        'iss'  => 'your-app',
        'exp'  => time() + 3600, // 1 hour expiry
        'data' => [
            'userId' => $user->userId,
            'email'  => $user->email
        ]
    ]);

    // TODO: Send email with reset link containing $resetToken
    // Example: https://yourdomain.com/reset-password?token=$resetToken

    Yii::app()->jwt->sendResponse(true, 'Password reset email sent', [
        'reset_token' => $resetToken
    ]);
}

    /**
     * POST /auth/resetPassword
     */
    /**
 * POST /auth/resetPassword
 * Params: token, new_password
 */
public function actionResetPassword()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $token       = $data['token'] ?? '';
    $newPassword = trim($data['new_password'] ?? '');

    if (!$token || !$newPassword) {
        Yii::app()->jwt->sendResponse(false, 'Token and new password are required', null, 400);
    }

    // Decode token
    $decoded = Yii::app()->jwt->validateToken($token);
    if (!$decoded) {
        Yii::app()->jwt->sendResponse(false, 'Invalid or expired token', null, 400);
    }

    // Validate new password length
    if (strlen($newPassword) < 6) {
        Yii::app()->jwt->sendResponse(false, 'New password must be at least 6 characters', null, 400);
    }

    // Fetch user from decoded token
    $user = User::model()->findByPk($decoded['userId'] ?? '');
    if (!$user) {
        Yii::app()->jwt->sendResponse(false, 'User not found', null, 404);
    }

    // Update password
    $user->password = $newPassword; // assuming hashing in setter
    if (!$user->save()) {
        Yii::app()->jwt->sendResponse(false, 'Failed to reset password', $user->getErrors(), 422);
    }

    Yii::app()->jwt->sendResponse(true, 'Password reset successfully', [
        'user' => [
            'userId' => $user->userId,
            'email'  => $user->email,
            'name'   => $user->name
        ]
    ]);
}


    /**
     * Helper: get safe user data
     */
    private function getSafeUserData($user)
    {
        return [
            'userId' => $user->userId,
            'name'   => $user->name,
            'email'  => $user->email,
            'role'   => $user->role
        ];
    }

    /**
     * Helper: log login (optional)
     */
    private function logLogin($user)
    {
        // Implement login logging if needed
    }

    /**
     * Helper: send JSON response (for internal calls)
     */
    protected function sendJson($response, $httpCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo json_encode($response);
        Yii::app()->end();
    }

    public function actionDebugRoutes()
{
    echo "<h2>Debug Routes</h2>";
    echo "<pre>";
    
    // Check if CategoryController file exists
    $categoryFile = Yii::app()->getControllerPath() . '/CategoryController.php';
    echo "CategoryController file: " . $categoryFile . "\n";
    echo "Exists: " . (file_exists($categoryFile) ? 'YES' : 'NO') . "\n\n";
    
    // List all controllers
    echo "All controllers:\n";
    $controllers = glob(Yii::app()->getControllerPath() . '/*Controller.php');
    foreach ($controllers as $c) {
        echo "- " . basename($c) . "\n";
    }
    
    echo "</pre>";
}
}