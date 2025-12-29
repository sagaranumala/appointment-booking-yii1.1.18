<?php
class BaseApiController extends CController
{
    public $enableCsrfValidation = false; // disable CSRF for API

    protected function sendJson($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data, JSON_PRETTY_PRINT);
        Yii::app()->end();
    }

     /**
     * Send JSON error response
     */
    protected function sendJsonError($message, $status = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        $this->sendJson($response, $status);
    }
    
    protected function validateCsrf()
{
    $postToken = Yii::app()->request->getPost('YII_CSRF_TOKEN');
    $cookieToken = Yii::app()->request->getCsrfToken();

    if (!$postToken || $postToken !== $cookieToken) {
        $this->sendJsonError('CSRF validation failed', 403);
    }
}


     protected function sendResponse($success, $message, $data = null, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ]);

        Yii::app()->end();
    }
    /**
     * Get current authenticated user from JWT
     */
    protected function getCurrentUser()
    {
        $jwt = Yii::app()->jwt; // register JwtHelper as 'jwt' component
        return $jwt->getCurrentUser();
    }
}
