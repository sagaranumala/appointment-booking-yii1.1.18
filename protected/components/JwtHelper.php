<?php
/**
 * Simple JWT Helper for Yii 1.x
 * composer require firebase/php-jwt
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper extends CApplicationComponent
{
    public $secretKey  = 'your-default-secret-key-change-me';
    public $algorithm  = 'HS256';
    public $expireTime = 86400; // 24 hours

    public function init()
    {
        parent::init();
        if (!class_exists('Firebase\JWT\JWT')) {
            throw new CException('Firebase JWT library not found');
        }
    }

    /**
     * Generate JWT token
     */
    public function generateToken(array $payload)
    {
        $tokenPayload = [
            'iat'  => time(),
            'exp'  => time() + $this->expireTime,
            'data' => $payload
        ];

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    /**
     * Validate and decode JWT token
     */
    public function validateToken($token)
{
    try {
        $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

        // Case 1: correct format -> data contains userId
        if (isset($decoded->data->userId)) {
            return (array) $decoded->data;
        }

        // Case 2: legacy / wrong format -> data->data contains userId
        if (isset($decoded->data->data->userId)) {
            return (array) $decoded->data->data;
        }

        Yii::log('JWT payload missing userId', 'error');
        return null;

    } catch (Exception $e) {
        Yii::log('JWT Error: ' . $e->getMessage(), 'error');
        return null;
    }
}

    /**
     * Extract token from Authorization header
     */
    public function extractToken()
    {
        $authHeader = null;

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strtolower($name) === 'authorization') {
                    $authHeader = $value;
                    break;
                }
            }
        }

        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Get current user from JWT
     */
    public function getCurrentUser()
    {
        $token = $this->extractToken();
        if (!$token) {
            return null;
        }
        return $this->validateToken($token);
    }

    /**
     * JSON response helper
     */
    public function sendResponse($success, $message, $data = null, $httpCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($httpCode);

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ]);

        Yii::app()->end();
    }
}
