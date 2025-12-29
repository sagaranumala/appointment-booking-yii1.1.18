<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class EmployeeController extends JsonApiController
{

   public function beforeAction($action)
{
    // Handle JSON requests for POST/PUT/DELETE
    $request = Yii::app()->request;
    
    // Check if it's a POST, PUT, or DELETE request
    if ($request->getIsPostRequest() || 
        $request->getIsPutRequest() || 
        $request->getIsDeleteRequest()) {
        
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // Get raw JSON data
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            
            if ($data !== null) {
                // Log for debugging (temporary)
                error_log("=== beforeAction for {$action->id} ===");
                error_log("Content-Type: $contentType");
                error_log("Raw input length: " . strlen($raw));
                error_log("Decoded data keys: " . implode(', ', array_keys($data)));
                
                // CRITICAL: Populate $_POST with JSON data BEFORE CSRF validation
                foreach ($data as $key => $value) {
                    $_POST[$key] = $value;
                }
                
                // Also populate $_REQUEST for compatibility
                $_REQUEST = array_merge($_REQUEST, $data);
                
                error_log("Populated \$_POST with JSON data");
                error_log("\$_POST keys: " . implode(', ', array_keys($_POST)));
            }
        }
    }
    
    // List of API actions to skip CSRF
    $apiActions = ['findByUser']; 
    if (in_array($action->id, $apiActions)) {
        // Only disable for GET requests to findByUser
        // Check if it's a GET request using request type
        if ($action->id === 'findByUser' && $request->getRequestType() === 'GET') {
            $request->enableCsrfValidation = false;
        }
        return true;
    }
    
    return parent::beforeAction($action);
}

// public function actionFindByUser()
// {
//     header('Content-Type: application/json');

//     try {
//         $currentUser = Yii::app()->jwt->getCurrentUser();
//         if (!$currentUser) {
//             echo json_encode(['success' => false, 'message' => 'Authentication required']);
//             Yii::app()->end();
//         }

//         // Adjust depending on JWT payload
//         $userId = $currentUser['userId'] ?? ($currentUser['data']['userId'] ?? null);
//         if (!$userId) {
//             echo json_encode(['success' => false, 'message' => 'User ID not found in JWT']);
//             Yii::app()->end();
//         }

//         $employee = Employee::model()
//             ->with(['user', 'department'])
//             ->find('t.userId=:userId AND t.status=1', [':userId' => $userId]);


//         if (!$employee) {
//             echo json_encode(['success' => false, 'message' => 'Employee not found']);
//             Yii::app()->end();
//         }

//         $employeeData = [
//             'employeeId' => $employee->employeeId,
//             'userId' => $employee->userId,
//             'departmentId' => $employee->departmentId,
//             'status' => $employee->status,
//             'profilePicture' => $employee->profilePicture ?? null,
//             'role' => $employee->user->role ?? null,
//             'name' => $employee->user->name ?? null,
//             'email' => $employee->user->email ?? null,
//             'phone' => $employee->user->phone ?? null,
//             'departmentName' => $employee->department->name ?? null,
//         ];

//         echo json_encode(['success' => true, 'data' => $employeeData]);
//         Yii::app()->end();

//     } catch (Exception $e) {
//         echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
//         Yii::app()->end();
//     }
// }

private function generatePresignedUrl($profilePath)
    {
        try {
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => getenv('R2_REGION') ?: 'auto',
                'endpoint'=> getenv('R2_ENDPOINT'),
                'credentials' => [
                    'key'    => getenv('R2_ACCESS_KEY'),
                    'secret' => getenv('R2_SECRET_KEY'),
                ],
                'suppress_php_deprecation_warning' => true, 
            ]);

            $bucket = getenv('R2_BUCKET');

            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => ltrim($profilePath, '/')
            ]);

            // Presigned URL valid for 20 minutes
            return (string) $s3->createPresignedRequest($cmd, '+20 minutes')->getUri();
        } catch (AwsException $e) {
            Yii::log("S3 Error: " . $e->getMessage(), 'error');
            return null;
        }
    }

// public function actionFindByUser()
// {
//     header('Content-Type: application/json');

//     try {
//         $currentUser = Yii::app()->jwt->getCurrentUser();
//         if (!$currentUser) {
//             echo json_encode(['success' => false, 'message' => 'Authentication required']);
//             Yii::app()->end();
//         }

//         // Adjust depending on JWT payload
//         $userId = $currentUser['userId'] ?? ($currentUser['data']['userId'] ?? null);
//         if (!$userId) {
//             echo json_encode(['success' => false, 'message' => 'User ID not found in JWT']);
//             Yii::app()->end();
//         }

//         $employee = Employee::model()
//             ->with(['user', 'department'])
//             ->find('t.userId=:userId AND t.status=1', [':userId' => $userId]);

//         if (!$employee) {
//             echo json_encode(['success' => false, 'message' => 'Employee not found']);
//             Yii::app()->end();
//         }

//         // Generate presigned URL for profile picture if exists
//         $profilePicUrl = null;
//         if (!empty($employee->profilePicture)) {
//             $profilePicUrl = $this->generatePresignedUrl($employee->profilePicture);
//         }

//         $employeeData = [
//             'employeeId' => $employee->employeeId,
//             'userId' => $employee->userId,
//             'departmentId' => $employee->departmentId,
//             'status' => $employee->status,
//             'profilePicture' => $profilePicUrl, // <-- use presigned URL
//             'role' => $employee->user->role ?? null,
//             'name' => $employee->user->name ?? null,
//             'email' => $employee->user->email ?? null,
//             'phone' => $employee->user->phone ?? null,
//             'departmentName' => $employee->department->name ?? null,
//         ];

//         echo json_encode(['success' => true, 'data' => $employeeData]);
//         Yii::app()->end();

//     } catch (Exception $e) {
//         echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
//         Yii::app()->end();
//     }
// }

public function actionFindByUser()
{
    header('Content-Type: application/json');

    try {
        // Verify the request method is GET
        if (Yii::app()->request->getRequestType() !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            Yii::app()->end();
        }

        $currentUser = Yii::app()->jwt->getCurrentUser();
        if (!$currentUser) {
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            Yii::app()->end();
        }

        // Adjust depending on JWT payload
        $userId = $currentUser['userId'] ?? ($currentUser['data']['userId'] ?? null);
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'User ID not found in JWT']);
            Yii::app()->end();
        }

        $employee = Employee::model()
            ->with(['user', 'department'])
            ->find('t.userId=:userId AND t.status=1', [':userId' => $userId]);

        if (!$employee) {
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
            Yii::app()->end();
        }

        // Generate presigned URL for profile picture if exists
        $profilePicUrl = null;
        if (!empty($employee->profilePicture)) {
            $profilePicUrl = $this->generatePresignedUrl($employee->profilePicture);
        }

        $employeeData = [
            'employeeId' => $employee->employeeId,
            'userId' => $employee->userId,
            'departmentId' => $employee->departmentId,
            'status' => $employee->status,
            'profilePicture' => $profilePicUrl, // <-- use presigned URL
            'role' => $employee->user->role ?? null,
            'name' => $employee->user->name ?? null,
            'email' => $employee->user->email ?? null,
            'phone' => $employee->user->phone ?? null,
            'departmentName' => $employee->department->name ?? null,
        ];

        echo json_encode(['success' => true, 'data' => $employeeData]);
        Yii::app()->end();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        Yii::app()->end();
    }
}



    // public function relations()
    // {
    //     return [
    //         'user' => [self::BELONGS_TO, 'User', 'userId'],
    //         'department' => [self::BELONGS_TO, 'Department', 'departmentId'],
    //         'address' => [self::HAS_ONE, 'Address', 'userId'],
    //     ];
    // }

    /**
 * Fetch all employees with user & department data
  */
//     public function actionIndex()
// {
// //     $criteria = new CDbCriteria();
// //     // $criteria->with = ['user', 'department', 'address'];
// //     // $criteria = new CDbCriteria();
// // $criteria->with = [
// //     'user',
// //     'department',
// //     'address' => [
// //         'joinType' => 'LEFT JOIN',
// //         'scopes' => false, // disable defaultScope of Address
// //     ],
// // ];

// $criteria = new CDbCriteria();
// $criteria->with = ['user', 'department', 'address'];
// $criteria->together = false; // allows LEFT JOIN instead of INNER JOIN
// $employees = Employee::model()->resetScope()->findAll($criteria);


//     // $criteria->order = 't.createdAt DESC'; // optional

//     $employees = Employee::model()->findAll($criteria);

//     // $employees = Employee::model()
//     // ->resetScope()
//     // ->with(['user', 'department', 'address'])
//     // ->findAll();



//     $data = [];

//     foreach ($employees as $emp) {
//         $data[] = [
//             // Employee
//             'employeeId' => $emp->employeeId,
//             'designation' => $emp->designation,
//             'status' => (int)$emp->status,
//             'createdAt' => $emp->createdAt,

//             // User
//             'userId' => $emp->user ? $emp->user->userId : null,
//             'name' => $emp->user ? $emp->user->name : null,
//             'email' => $emp->user ? $emp->user->email : null,
//             'phone' => $emp->user ? $emp->user->phone : null,
//             'role' => $emp->user ? $emp->user->role : null,

//             // Department
//             'departmentId' => $emp->department ? $emp->department->departmentId : $emp->departmentId,
//             'departmentName' => $emp->department ? $emp->department->name : null,

//             // Address
//             'addressLine1' => $emp->address ? $emp->address->addressLine1 : "Missing",
//             'addressLine2' => $emp->address ? $emp->address->addressLine2 : null,
//             'city' => $emp->address ? $emp->address->city : null,
//             'state' => $emp->address ? $emp->address->state : null,
//             'country' => $emp->address ? $emp->address->country : null,
//             'postalCode' => $emp->address ? $emp->address->postalCode : null,
//         ];
//     }

//     $this->sendJson([
//         'success' => true,
//         'employees' => $data
//     ]);
// }


public function actionIndex()
{
    // Reset default scope to include all employees (active + inactive)
    $employees = Employee::model()
    ->resetScope()
    ->with([
        'user',
        'department',
        'address' => [
            'together' => true
        ]
    ])
    ->findAll();

    // ->resetScope()
    // ->with([
    //     'user',
    //     'department',
    //     'address' => [
    //         'alias' => 'a',
    //         'together' => true,
    //     ],
    // ])
    // ->findAll();


    $data = [];

    foreach ($employees as $emp) {
        $address = $emp->address;

        $data[] = [
            // Employee
            'employeeId' => $emp->employeeId,
            'designation' => $emp->designation,
            'status' => (int)$emp->status,
            'createdAt' => $emp->createdAt,

            // User
            'userId' => $emp->user ? $emp->user->userId : null,
            'name' => $emp->user ? $emp->user->name : null,
            'email' => $emp->user ? $emp->user->email : null,
            'phone' => $emp->user ? $emp->user->phone : null,
            'role' => $emp->user ? $emp->user->role : null,

            // Department
            'departmentId' => $emp->department ? $emp->department->departmentId : $emp->departmentId,
            'departmentName' => $emp->department ? $emp->department->name : null,

            // Address (handle missing data)
            'addressLine1' => $address ? $address->addressLine1 : 'Missing',
            'addressLine2' => $address ? $address->addressLine2 : null,
            'city' => $address ? $address->city : null,
            'state' => $address ? $address->state : null,
            'country' => $address ? $address->country : null,
            'postalCode' => $address ? $address->postalCode : null,
        ];
    }

    $this->sendJson([
        'success' => true,
        'employees' => $data
    ]);
}


    /**
     * Create Employee with Address
     */
   public function actionCreate()
{
    // Get raw JSON input
    $raw = file_get_contents('php://input');
    $post = json_decode($raw, true);

    if (!$post) {
        $this->sendJson([
            'success' => false,
            'message' => 'Invalid JSON input'
        ], 400);
    }

    $transaction = Yii::app()->db->beginTransaction();
    try {
        // 1️⃣ Create User
        if (!isset($post['user'])) {
            throw new CException('User data is required');
        }

        $user = new User();
        $user->attributes = $post['user'];

        if (empty($user->password)) {
            $user->password = 'DefaultPassword123'; // plain text
        }


        if (!$user->save()) {
            throw new CException('User validation failed: ' . json_encode($user->errors));
        }

        // 2️⃣ Create Address
        $address = new Address();
        if (isset($post['address'])) {
            $address->attributes = $post['address'];
        }
        $address->userId = $user->userId;

        if (!$address->save()) {
            throw new CException('Address validation failed: ' . json_encode($address->errors));
        }

        // 3️⃣ Create Employee
        $employee = new Employee();
        if (isset($post['employee'])) {
            $employee->attributes = $post['employee'];
        }
        $employee->userId = $user->userId;

        if (!$employee->save()) {
            throw new CException('Employee validation failed: ' . json_encode($employee->errors));
        }

        $transaction->commit();

        $this->sendJson([
            'success' => true,
            'user' => $user->getSafeApiData(),
            'employee' => $employee->getSafeApiData(),
            'address' => $address->getSafeApiData()
        ]);

    } catch (Exception $e) {
        $transaction->rollback();
        $this->sendJson([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}



    /**
     * Update Employee + Address
     */

public function actionUpdate()
{
    // Enable Docker logging
    ini_set('log_errors', 1);
    ini_set('error_log', 'php://stderr');

    $docker_log = function($message, $level = 'INFO') {
        $time = date('Y-m-d H:i:s');
        error_log("[$time] [$level] $message");
    };

    $docker_log("=== Employee Update Action Called ===");

    // Get data from $_POST (already populated by beforeAction())
    $employeeId = Yii::app()->request->getPost('employeeId');
    $employeeData = Yii::app()->request->getPost('employee');
    $addressData = Yii::app()->request->getPost('address');

    $docker_log("Employee ID: " . ($employeeId ?: 'NOT FOUND'));
    $docker_log("Employee Data: " . print_r($employeeData, true));

    // Validation
    if (!$employeeId) {
        $docker_log("ERROR: employeeId is required", 'ERROR');
        $this->sendJson(['success' => false, 'message' => 'employeeId is required'], 400);
    }

    // Fetch employee
    $employee = Employee::model()->findByAttributes(['employeeId' => $employeeId]);
    if (!$employee) {
        $docker_log("ERROR: Employee not found", 'ERROR');
        $this->sendJson(['success' => false, 'message' => 'Employee not found'], 404);
    }

    // Begin transaction
    $transaction = Yii::app()->db->beginTransaction();
    try {
        // Update Employee
        if ($employeeData && is_array($employeeData)) {
            $employee->attributes = $employeeData;
            if (!$employee->save()) {
                throw new CException('Employee validation failed: ' . json_encode($employee->errors));
            }
            $docker_log("Employee saved successfully");
        }

        // Update Address
        $address = $employee->address;
        if ($address && $addressData && is_array($addressData)) {
            $address->attributes = $addressData;
            if (!$address->save()) {
                throw new CException('Address validation failed: ' . json_encode($address->errors));
            }
            $docker_log("Address saved successfully");
        }

        $transaction->commit();
        $docker_log("Transaction committed successfully", 'INFO');

        $this->sendJson([
            'success' => true,
            'employee' => $employee->getSafeApiData(),
            'address' => $address ? $address->getSafeApiData() : null
        ]);
    } catch (Exception $e) {
        $transaction->rollback();
        $docker_log("Transaction failed: " . $e->getMessage(), 'ERROR');
        $this->sendJson(['success' => false, 'message' => $e->getMessage()], 400);
    }
}


// public function actionUpdate()
// {
//     /* ===============================
//      * Docker logging setup
//      * =============================== */
//     ini_set('log_errors', 1);
//     ini_set('error_log', '/proc/self/fd/2'); // Docker Desktop logs

//     $log = function ($msg, $level = 'INFO') {
//         error_log("[" . date('Y-m-d H:i:s') . "] [$level] $msg");
//     };

//     $log("=== Employee Update API Called ===");

//     /* ===============================
//      * Read raw JSON
//      * =============================== */
//     $raw = file_get_contents('php://input');
//     $log("Raw JSON: " . substr($raw, 0, 500), 'DEBUG');

//     $data = json_decode($raw, true);
//     if (!is_array($data)) {
//         $log("Invalid JSON body", 'ERROR');
//         $this->sendJson(['success' => false, 'message' => 'Invalid JSON'], 400);
//     }

//     /* ===============================
//      * FIX: Inject CSRF into Yii
//      * =============================== */
//     $csrfName = Yii::app()->request->csrfTokenName;

//     if (isset($data[$csrfName])) {
//         $_POST[$csrfName] = $data[$csrfName];
//         $log("CSRF injected into \$_POST", 'DEBUG');
//     } else {
//         $log("CSRF token missing in JSON", 'ERROR');
//     }

//     /* ===============================
//      * Force Yii CSRF validation
//      * =============================== */
//     if (!Yii::app()->request->validateCsrfToken()) {
//         $log("CSRF VALIDATION FAILED", 'ERROR');
//         $this->sendJson(['success' => false, 'message' => 'CSRF failed'], 400);
//     }

//     $log("CSRF validation PASSED");

//     /* ===============================
//      * Validate employeeId
//      * =============================== */
//     $employeeId = $data['employeeId'] ?? null;
//     if (!$employeeId) {
//         $log("employeeId missing", 'ERROR');
//         $this->sendJson(['success' => false, 'message' => 'employeeId required'], 400);
//     }

//     $employee = Employee::model()->findByAttributes(['employeeId' => $employeeId]);
//     if (!$employee) {
//         $log("Employee not found: $employeeId", 'ERROR');
//         $this->sendJson(['success' => false, 'message' => 'Employee not found'], 404);
//     }

//     /* ===============================
//      * Transaction
//      * =============================== */
//     $tx = Yii::app()->db->beginTransaction();
//     try {
//         $employee->attributes = $data['employee'] ?? [];

//         if (!$employee->save()) {
//             throw new Exception(json_encode($employee->errors));
//         }

//         if ($employee->address && isset($data['address'])) {
//             $employee->address->attributes = $data['address'];
//             if (!$employee->address->save()) {
//                 throw new Exception(json_encode($employee->address->errors));
//             }
//         }

//         $tx->commit();
//         $log("Employee updated successfully: $employeeId");

//         $this->sendJson([
//             'success' => true,
//             'employee' => $employee->getSafeApiData(),
//         ]);
//     } catch (Exception $e) {
//         $tx->rollback();
//         $log("Update failed: " . $e->getMessage(), 'ERROR');
//         $this->sendJson(['success' => false, 'message' => $e->getMessage()], 400);
//     }
// }



    /**
     * Search Employees by Department
     */
    public function actionSearchByDepartment($departmentId)
    {
        $employees = Employee::model()->with('department')->findAllByAttributes([
            'departmentId' => $departmentId,
        ]);

        $data = [];
        foreach ($employees as $emp) {
            $data[] = $emp->getSafeApiData(['employeeId', 'designation', 'status']);
        }

        $this->sendJson(['success' => true, 'employees' => $data]);
    }

    /**
     * Send JSON response
     */
    protected function sendJson($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        Yii::app()->end();
    }

    /**
     * Get count of employees by department
     */
    public function actionCountByDepartment()
    {
        $sql = "
            SELECT d.name AS departmentName, COUNT(e.id) AS employeeCount
            FROM department d
            LEFT JOIN employee e
                ON e.departmentId = d.id AND e.status = 1
            WHERE d.status = 1
            GROUP BY d.id
            ORDER BY d.name ASC
        ";

        $command = Yii::app()->db->createCommand($sql);
        $results = $command->queryAll();

        $this->sendJson([
            'success' => true,
            'departments' => $results
        ]);
    }

}
