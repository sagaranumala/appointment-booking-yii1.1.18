<?php
// protected/controllers/JobsApiController.php
require_once __DIR__ . '/../../vendor/autoload.php'; // adjust path if needed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class JobsApiController extends Controller
{
    // Define protected actions requiring login
    protected $protectedActions = ['create', 'update', 'delete', 'toggleStatus'];

    public function beforeAction($action)
    {
        $this->handleCors();

        // Prevent Yii from redirecting API calls to login page
        Yii::app()->user->loginUrl = null;

        // Check authentication for protected actions
        if (in_array($action->id, $this->protectedActions) && Yii::app()->user->isGuest) {
            $this->sendJsonResponse(401, [
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        return parent::beforeAction($action);
    }

    protected function handleCors()
    {
        $allowedOrigins = [
            'http://localhost:3000',
            'http://127.0.0.1:3000',
        ];

        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        // if (in_array($origin, $allowedOrigins)) {
        //     header("Access-Control-Allow-Origin: {$origin}");
        //     header("Access-Control-Allow-Credentials: true");
        // } else {
        //     header("Access-Control-Allow-Origin: *");
        // }

        header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Max-Age: 86400");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            Yii::app()->end();
        }
    }

    // ================= PUBLIC ENDPOINTS =================

    public function actionIndex()
    {
        $this->sendJobsList(false);
    }

    public function actionActive()
    {
        $this->sendJobsList(true);
    }

    public function actionView($id)
    {
        $job = Job::model()->findByPk($id);

        if (!$job) {
            $this->sendJsonResponse(404, [
                'success' => false,
                'message' => 'Job not found'
            ]);
        }

        $this->sendJsonResponse(200, [
            'success' => true,
            'message' => 'Job details fetched successfully',
            'data' => $this->prepareJobDetailData($job)
        ]);
    }

    // ================= PROTECTED ADMIN ACTIONS =================

    public function actionCreate()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $job = new Job();
        $job->attributes = $data;
        $job->createdAt = new CDbExpression('NOW()');
        $job->updatedAt = new CDbExpression('NOW()');
        $job->createdBy = Yii::app()->user->id;

        if ($job->status === 'active') {
            $job->publishedAt = new CDbExpression('NOW()');
        }

        if ($job->save()) {
            $this->sendJsonResponse(201, [
                'success' => true,
                'message' => 'Job created successfully',
                'data' => $this->prepareJobDetailData($job)
            ]);
        } else {
            $this->sendJsonResponse(400, [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $job->getErrors()
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $job = Job::model()->findByPk($id);
        if (!$job) {
            $this->sendJsonResponse(404, [
                'success' => false,
                'message' => 'Job not found'
            ]);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $oldStatus = $job->status;
        $job->attributes = $data;
        $job->updatedAt = new CDbExpression('NOW()');
        $job->updatedBy = Yii::app()->user->id;

        if ($oldStatus === 'draft' && $job->status === 'active' && !$job->publishedAt) {
            $job->publishedAt = new CDbExpression('NOW()');
        }

        if ($job->save()) {
            $this->sendJsonResponse(200, [
                'success' => true,
                'message' => 'Job updated successfully',
                'data' => $this->prepareJobDetailData($job)
            ]);
        } else {
            $this->sendJsonResponse(400, [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $job->getErrors()
            ]);
        }
    }

    public function actionDelete($id)
    {
        $job = Job::model()->findByPk($id);
        if (!$job) {
            $this->sendJsonResponse(404, [
                'success' => false,
                'message' => 'Job not found'
            ]);
        }

        $job->status = 'closed';
        $job->updatedAt = new CDbExpression('NOW()');
        $job->updatedBy = Yii::app()->user->id;

        if ($job->save()) {
            $this->sendJsonResponse(200, [
                'success' => true,
                'message' => 'Job closed successfully',
                'data' => $this->prepareJobDetailData($job)
            ]);
        } else {
            $this->sendJsonResponse(400, [
                'success' => false,
                'message' => 'Failed to close job',
                'errors' => $job->getErrors()
            ]);
        }
    }

    public function actionToggleStatus($id)
    {
        $job = Job::model()->findByPk($id);
        if (!$job) {
            $this->sendJsonResponse(404, [
                'success' => false,
                'message' => 'Job not found'
            ]);
        }

        $status = Yii::app()->request->getQuery('status');
        $validStatuses = ['active','draft','closed'];

        if (!in_array($status,$validStatuses)) {
            $this->sendJsonResponse(400, [
                'success' => false,
                'message' => 'Invalid status'
            ]);
        }

        $oldStatus = $job->status;
        $job->status = $status;
        $job->updatedAt = new CDbExpression('NOW()');
        $job->updatedBy = Yii::app()->user->id;

        if ($oldStatus === 'draft' && $status === 'active' && !$job->publishedAt) {
            $job->publishedAt = new CDbExpression('NOW()');
        }

        if ($job->save()) {
            $this->sendJsonResponse(200, [
                'success' => true,
                'message' => 'Job status updated',
                'data' => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'status' => $job->status,
                    'publishedAt' => $job->publishedAt
                ]
            ]);
        } else {
            $this->sendJsonResponse(400, [
                'success' => false,
                'message' => 'Failed to update status',
                'errors' => $job->getErrors()
            ]);
        }
    }

    // ==================== PRIVATE HELPERS ====================

    private function sendJobsList($onlyActive = false)
    {
        $criteria = new CDbCriteria();
        if ($onlyActive) $criteria->compare('status','active');

        // Filters & search
        $filters = ['department','locationType','employmentType','experienceLevel','search'];
        foreach ($filters as $f) {
            $val = Yii::app()->request->getQuery($f);
            if ($val) {
                if ($f === 'search') {
                    $criteria->addSearchCondition('title',$val,true,'OR');
                    $criteria->addSearchCondition('description',$val,true,'OR');
                } else {
                    $criteria->compare($f,$val);
                }
            }
        }

        // Pagination
        $page = Yii::app()->request->getQuery('page',1);
        $limit = Yii::app()->request->getQuery('limit',10);
        $criteria->limit = $limit;
        $criteria->offset = ($page-1)*$limit;

        // Sorting
        $criteria->order = 'publishedAt DESC, createdAt DESC';

        $total = Job::model()->count($criteria);
        $jobs = Job::model()->findAll($criteria);

        $this->sendJsonResponse(200, [
            'success' => true,
            'message' => 'Jobs fetched successfully',
            'data' => [
                'jobs' => $this->prepareJobsData($jobs),
                'pagination' => [
                    'total' => $total,
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'pages' => ceil($total/$limit)
                ]
            ]
        ]);
    }

    private function prepareJobsData($jobs)
    {
        $data = [];
        foreach ($jobs as $job) {
            $data[] = [
                'id'=>$job->id,
                'title'=>$job->title,
                'department'=>$job->department,
                'locationType'=>$job->locationType,
                'employmentType'=>$job->employmentType,
                'status'=>$job->status,
                'publishedAt'=>$job->publishedAt,
                'createdAt'=>$job->createdAt,
                'updatedAt'=>$job->updatedAt,
            ];
        }
        return $data;
    }

    private function prepareJobDetailData($job)
    {
        return [
            'id'=>$job->id,
            'title'=>$job->title,
            'department'=>$job->department,
            'category'=>$job->category,
            'description'=>$job->description,
            'responsibilities'=>$job->responsibilities,
            'requirements'=>$job->requirements,
            'skillsRequired'=>$job->skillsRequired,
            'locationType'=>$job->locationType,
            'locationCity'=>$job->locationCity,
            'locationCountry'=>$job->locationCountry,
            'employmentType'=>$job->employmentType,
            'experienceLevel'=>$job->experienceLevel,
            'status'=>$job->status,
            'minSalary'=>$job->minSalary,
            'maxSalary'=>$job->maxSalary,
            'salaryCurrency'=>$job->salaryCurrency,
            'createdAt'=>$job->createdAt,
            'updatedAt'=>$job->updatedAt,
            'publishedAt'=>$job->publishedAt,
            'createdBy'=>$job->createdBy,
            'updatedBy'=>$job->updatedBy
        ];
    }

    private function sendJsonResponse($statusCode, $data)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT);
        Yii::app()->end();
    }
}
