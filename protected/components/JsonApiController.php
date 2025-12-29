<?php
// protected/components/JsonApiController.php
class JsonApiController extends CController
{
    // Disable Yii's automatic CSRF validation
    public $enableCsrfValidation = false;
    
    // Property to store parsed JSON data
    protected $jsonData = [];
    
    // Enable Docker logging
    protected $docker_log_enabled = true;
    
    protected function docker_log($message, $level = 'INFO')
    {
        if ($this->docker_log_enabled) {
            $time = date('Y-m-d H:i:s');
            error_log("[$time] [$level] $message");
        }
    }
    
   public function beforeAction($action)
{
    $this->docker_log("=== beforeAction for " . get_class($this) . "::{$action->id} ===");

    $request = Yii::app()->request;
    $method = $request->getRequestType();
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    $this->docker_log("Method: $method");
    $this->docker_log("Content-Type: $contentType");

    // Only validate CSRF for state-changing requests
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {

        /* ======================================================
         1️⃣ MULTIPART FORM DATA (FILE UPLOADS)
        ====================================================== */
        if (strpos($contentType, 'multipart/form-data') !== false) {
            $this->docker_log("Detected multipart/form-data request");

            // Validate CSRF using Yii (masked token works)
            if (!$request->validateCsrfToken()) {
                $this->docker_log("CSRF validation failed (multipart)", 'ERROR');
                $this->sendJsonError('CSRF validation failed', 400);
            }

            $this->docker_log("CSRF validation passed (multipart)");
            return parent::beforeAction($action);
        }

        /* ======================================================
         2️⃣ APPLICATION/JSON REQUESTS
        ====================================================== */
        if (strpos($contentType, 'application/json') !== false) {
            $this->docker_log("Detected JSON request");

            $raw = file_get_contents('php://input');
            if (!$raw) {
                $this->sendJsonError('Empty JSON body', 400);
            }

            $data = json_decode($raw, true);
            if ($data === null) {
                $this->sendJsonError('Invalid JSON', 400);
            }

            // CSRF token can come from header or JSON body
            $csrfName = $request->csrfTokenName;
            $headerToken = $request->getHeader('X-CSRF-Token');
            $bodyToken = $data[$csrfName] ?? null;

            // Inject token into $_POST for Yii validation
            $_POST[$csrfName] = $headerToken ?? $bodyToken ?? null;

            if (!$request->validateCsrfToken()) {
                $this->docker_log("CSRF validation failed (JSON)", 'ERROR');
                $this->sendJsonError('CSRF validation failed', 400);
            }

            $this->docker_log("CSRF validation passed (JSON)");

            // Remove token from data before populating $_POST
            unset($data[$csrfName]);
            foreach ($data as $key => $value) {
                $_POST[$key] = $value;
            }

            return parent::beforeAction($action);
        }

        /* ======================================================
         3️⃣ OTHER FORM POSTS (x-www-form-urlencoded)
        ====================================================== */
        if (!$request->validateCsrfToken()) {
            $this->docker_log("CSRF validation failed (form)", 'ERROR');
            $this->sendJsonError('CSRF validation failed', 400);
        }
    }

    return parent::beforeAction($action);
}


    
    protected function validateCsrfToken($data)
{
    $request = Yii::app()->request;
    $csrfName = $request->csrfTokenName;

    if (!isset($data[$csrfName])) {
        $this->sendJsonError('CSRF token missing', 400);
    }

    try {
        // Let Yii validate it properly
        if (!$request->validateCsrfToken()) {
            $this->sendJsonError('CSRF validation failed', 400);
        }
    } catch (Exception $e) {
        $this->sendJsonError('CSRF validation error', 400);
    }
}

    
    protected function sendJson($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        Yii::app()->end();
    }
    
    protected function sendJsonError($message, $statusCode = 400, $debug = [])
    {
        $response = ['success' => false, 'message' => $message];
        if (!empty($debug) && $this->docker_log_enabled) {
            $response['debug'] = $debug;
        }
        $this->sendJson($response, $statusCode);
    }
    
    // Helper to get JSON data
    protected function getJsonData($key = null, $default = null)
    {
        if ($key === null) {
            return $this->jsonData;
        }
        return isset($this->jsonData[$key]) ? $this->jsonData[$key] : $default;
    }
}