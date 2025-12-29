<?php

class ProviderController extends BaseApiController
{
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * Send JSON
     */
    protected function sendJson($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        Yii::app()->end();
    }

/**
 * GET /provider/index
 * GET /provider/index?categoryUlid=xxx
 * List all providers (optional category filter)
 */
public function actionIndex()
{
    $categoryUlid = Yii::app()->request->getQuery('categoryUlid');

    $criteria = new CDbCriteria();
    $criteria->condition = 't.status = 1';
    $criteria->with = ['user'];   // JOIN users table
    $criteria->together = true;   // force single query

    if (!empty($categoryUlid)) {
        $criteria->addCondition('t.categoryUlid = :categoryUlid');
        $criteria->params[':categoryUlid'] = $categoryUlid;
    }

    $providers = ServiceProvider::model()->findAll($criteria);

    $data = [];

    foreach ($providers as $provider) {
        $data[] = [
            // provider fields
            'providerUlid'    => $provider->providerUlid,
            'userUlid'        => $provider->userUlid,
            'categoryUlid'    => $provider->categoryUlid,
            'experienceYears'=> (int) $provider->experienceYears,
            'bio'             => $provider->bio,
            'hourlyRate'      => (float) $provider->hourlyRate,
            'status'          => (int) $provider->status,
            'createdAt'       => $provider->createdAt,
            'updatedAt'       => $provider->updatedAt,

            // user fields (safe access)
            'name'            => $provider->user ? $provider->user->name : null,
            'email'           => $provider->user ? $provider->user->email : null,
            'phone'           => $provider->user ? $provider->user->phone : null,
        ];
    }

    $this->sendJson([
        'success' => true,
        'data'    => $data,
        'count'   => count($data),
    ]);
}


/**
 * POST /provider/get-by-ulid
 * Get provider profile by ULID using FormData (no authentication required)
 */
public function actionView()
{
    // Get providerUlid from FormData
    $providerUlid = Yii::app()->request->getPost('providerUlid');
    
    if (empty($providerUlid)) {
        $this->sendJsonError('Provider ULID is required', 400);
    }

    $provider = ServiceProvider::model()->findByAttributes([
        'providerUlid' => $providerUlid,
        'status' => 1
    ]);
    
    if (!$provider) {
        $this->sendJsonError('Provider not found', 404);
    }

    $user = User::model()->findByAttributes(['userId' => $provider->userUlid]);
    $category = ServiceCategory::model()->findByAttributes(['categoryUlid' => $provider->categoryUlid]);

    $data = [
        'providerUlid' => $provider->providerUlid,
        'userUlid' => $provider->userUlid,
        'categoryUlid' => $provider->categoryUlid,
        'name' => $user ? $user->name : null,
        'email' => $user ? $user->email : null,
        'phone' => $user ? $user->phone : null,
        'experienceYears' => $provider->experienceYears,
        'bio' => $provider->bio,
        'hourlyRate' => $provider->hourlyRate,
        'categoryName' => $category ? $category->name : null,
        'status' => $provider->status,
        'createdAt' => $provider->createdAt,
        'updatedAt' => $provider->updatedAt
    ];

    $this->sendJson([
        'success' => true,
        'data' => $data
    ]);
}


    // /**
    //  * GET /provider/view/{providerUlid}
    //  * View single provider
    //  */
    // public function actionView($providerUlid)
    // {
    //     $provider = ServiceProvider::model()->findByAttributes([
    //         'providerUlid' => $providerUlid,
    //         'status' => 1
    //     ]);
        
    //     if (!$provider) {
    //         $this->sendJsonError('Provider not found', 404);
    //     }

    //     $user = User::model()->findByAttributes(['userId' => $provider->userUlid]);
    //     $category = ServiceCategory::model()->findByAttributes(['categoryUlid' => $provider->categoryUlid]);

    //     $data = [
    //         'providerUlid' => $provider->providerUlid,
    //         'userUlid' => $provider->userUlid,
    //         'categoryUlid' => $provider->categoryUlid,
    //         'name' => $user ? $user->name : null,
    //         'email' => $user ? $user->email : null,
    //         'phone' => $user ? $user->phone : null,
    //         'experienceYears' => $provider->experienceYears,
    //         'bio' => $provider->bio,
    //         // 'website' => $provider->website,
    //         'hourlyRate' => $provider->hourlyRate,
    //         'categoryName' => $category ? $category->name : null,
    //         'status' => $provider->status,
    //         'createdAt' => $provider->createdAt,
    //         'updatedAt' => $provider->updatedAt
    //     ];

    //     $this->sendJson([
    //         'success' => true,
    //         'data' => $data
    //     ]);
    // }

    /**
     * POST /provider/create
     * Create new provider profile
     */
    public function actionCreate()
{
    $this->validateCsrf();

    $currentUser = Yii::app()->jwt->getCurrentUser();
    if (!$currentUser) {
        $this->sendJsonError('Unauthorized', 401);
    }

    // Check if provider already exists for this user
    $existingProvider = ServiceProvider::model()->findByAttributes([
        'userUlid' => $currentUser['userId'],
        'status' => 1
    ]);
    
    if ($existingProvider) {
        $this->sendJsonError('Provider profile already exists for this user', 409);
    }

    // Get the profile array from POST data
    $profileData = Yii::app()->request->getPost('profile', []);
    
    // Debug: See what data is being received
    /*
    echo "<pre>";
    echo "Raw POST data:\n";
    print_r($_POST);
    echo "\nProfile data:\n";
    print_r($profileData);
    echo "</pre>";
    Yii::app()->end();
    */

    $provider = new ServiceProvider();
    $provider->userUlid = $currentUser['userId'];
    $provider->categoryUlid = isset($profileData['categoryUlid']) ? $profileData['categoryUlid'] : '';
    $provider->experienceYears = isset($profileData['experienceYears']) ? (int)$profileData['experienceYears'] : 0;
    $provider->bio = isset($profileData['bio']) ? $profileData['bio'] : '';
    $provider->hourlyRate = isset($profileData['hourlyRate']) ? (float)$profileData['hourlyRate'] : 0;
    $provider->status = 1;

    if (!$provider->save()) {
        // Debug validation errors
        /*
        echo "<pre>";
        echo "Validation errors:\n";
        print_r($provider->getErrors());
        echo "\nModel attributes:\n";
        print_r($provider->attributes);
        echo "</pre>";
        Yii::app()->end();
        */
        
        $this->sendJsonError('Failed to create provider profile', 422, $provider->getErrors());
    }

    // Also update user role if not already provider
    $user = User::model()->findByPk($currentUser['userId']);
    if ($user && $user->role !== 'provider') {
        $user->role = 'provider';
        $user->save();
    }

    $this->sendJson([
        'success' => true,
        'message' => 'Provider profile created successfully',
        'data' => [
            'providerUlid' => $provider->providerUlid,
            'categoryUlid' => $provider->categoryUlid
        ]
    ], 201);
}

    /**
     * PUT /provider/update/{providerUlid}
     * Update provider profile
     */
    public function actionUpdate($providerUlid)
    {
        $this->validateCsrf();

        $currentUser = Yii::app()->jwt->getCurrentUser();
        if (!$currentUser) {
            $this->sendJsonError('Unauthorized', 401);
        }

        $provider = ServiceProvider::model()->findByAttributes([
            'providerUlid' => $providerUlid,
            'status' => 1
        ]);
        
        if (!$provider) {
            $this->sendJsonError('Provider not found', 404);
        }

        // Check ownership
        if ($provider->userUlid !== $currentUser['userId'] && $currentUser['role'] !== 'admin') {
            $this->sendJsonError('Not authorized to update this provider', 403);
        }

        // Get PUT data
        parse_str(file_get_contents("php://input"), $putData);
        
        $provider->experienceYears = isset($putData['experienceYears']) ? (int)$putData['experienceYears'] : $provider->experienceYears;
        $provider->bio = isset($putData['bio']) ? $putData['bio'] : $provider->bio;
        $provider->website = isset($putData['website']) ? $putData['website'] : $provider->website;
        $provider->hourlyRate = isset($putData['hourlyRate']) ? (float)$putData['hourlyRate'] : $provider->hourlyRate;
        $provider->categoryUlid = isset($putData['categoryUlid']) ? $putData['categoryUlid'] : $provider->categoryUlid;
        $provider->updatedAt = date('Y-m-d H:i:s');

        if (!$provider->save()) {
            $this->sendJsonError('Failed to update provider', 422, $provider->getErrors());
        }

        $this->sendJson([
            'success' => true,
            'message' => 'Provider profile updated successfully',
            'data' => [
                'providerUlid' => $provider->providerUlid
            ]
        ]);
    }

    /**
     * DELETE /provider/delete/{providerUlid}
     * Soft delete provider profile
     */
    public function actionDelete($providerUlid)
    {
        $this->validateCsrf();

        $currentUser = Yii::app()->jwt->getCurrentUser();
        if (!$currentUser) {
            $this->sendJsonError('Unauthorized', 401);
        }

        $provider = ServiceProvider::model()->findByAttributes([
            'providerUlid' => $providerUlid,
            'status' => 1
        ]);
        
        if (!$provider) {
            $this->sendJsonError('Provider not found', 404);
        }

        // Check ownership
        if ($provider->userUlid !== $currentUser['userId'] && $currentUser['role'] !== 'admin') {
            $this->sendJsonError('Not authorized to delete this provider', 403);
        }

        $provider->status = 0;
        $provider->updatedAt = date('Y-m-d H:i:s');

        if (!$provider->save()) {
            $this->sendJsonError('Failed to delete provider', 422, $provider->getErrors());
        }

        $this->sendJson([
            'success' => true,
            'message' => 'Provider profile deleted successfully'
        ]);
    }

    /**
     * GET /provider/search?q=keyword&categoryUlid=xxx
     * Search providers
     */
    public function actionSearch()
    {
        $query = Yii::app()->request->getQuery('q', '');
        $categoryUlid = Yii::app()->request->getQuery('categoryUlid', '');
        
        if (empty($query)) {
            $this->sendJsonError('Search query required', 400);
        }

        // Build criteria
        $criteria = new CDbCriteria();
        $criteria->condition = 't.status = 1';
        $criteria->with = ['user'];
        
        if ($categoryUlid) {
            $criteria->addCondition('t.categoryUlid = :categoryUlid');
            $criteria->params[':categoryUlid'] = $categoryUlid;
        }

        // Search in user name, provider bio, etc.
        $criteria->addCondition('(user.name LIKE :query OR t.bio LIKE :query)');
        $criteria->params[':query'] = '%' . $query . '%';
        
        $criteria->order = 't.createdAt DESC';
        $criteria->limit = 20;

        $providers = ServiceProvider::model()->with('user')->findAll($criteria);

        $data = [];
        foreach ($providers as $provider) {
            $user = $provider->user;
            $category = ServiceCategory::model()->findByAttributes(['categoryUlid' => $provider->categoryUlid]);

            $data[] = [
                'providerUlid' => $provider->providerUlid,
                'name' => $user ? $user->name : null,
                'email' => $user ? $user->email : null,
                'experienceYears' => $provider->experienceYears,
                'bio' => $provider->bio,
                'website' => $provider->website,
                'hourlyRate' => $provider->hourlyRate,
                'categoryName' => $category ? $category->name : null,
                'categoryUlid' => $provider->categoryUlid
            ];
        }

        $this->sendJson([
            'success' => true,
            'data' => $data,
            'count' => count($data)
        ]);
    }

    /**
     * GET /provider/slots
     * Get available slots for a provider on a specific date
     * Params: providerUlid, date (YYYY-MM-DD)
     */
    public function actionSlots()
    {
        $providerUlid = Yii::app()->request->getQuery('providerUlid');
        $date = Yii::app()->request->getQuery('date');

        if (!$providerUlid || !$date) {
            $this->sendJsonError('providerUlid and date required', 400);
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
            $this->sendJsonError('Invalid date format. Use YYYY-MM-DD', 400);
        }

        $dayOfWeek = (int)date('N', strtotime($date)); // 1=Mon

        // 1️⃣ Get provider availability for the day
        $availability = ProviderAvailability::model()->findByAttributes([
            'providerUlid' => $providerUlid,
            'dayOfWeek' => $dayOfWeek,
            'status' => 1
        ]);

        if (!$availability) {
            $this->sendJson([
                'success' => true,
                'data' => [],
                'message' => 'No availability for this day'
            ]);
        }

        // 2️⃣ Generate all slots
        $allSlots = SlotHelper::generateSlots(
            $availability->startTime,
            $availability->endTime,
            $availability->slotDuration
        );

        // 3️⃣ Fetch booked slots
        $bookedAppointments = Appointment::model()->findAllByAttributes([
            'providerUlid' => $providerUlid,
            'appointmentDate' => $date,
            'status' => 'booked'
        ]);

        $bookedMap = [];
        foreach ($bookedAppointments as $appointment) {
            $bookedMap[$appointment->startTime . '-' . $appointment->endTime] = true;
        }

        // 4️⃣ Remove booked slots
        $availableSlots = [];
        foreach ($allSlots as $slot) {
            $key = $slot['startTime'] . '-' . $slot['endTime'];
            if (!isset($bookedMap[$key])) {
                $availableSlots[] = $slot;
            }
        }

        $this->sendJson([
            'success' => true,
            'data' => $availableSlots,
            'availability' => [
                'dayOfWeek' => $dayOfWeek,
                'startTime' => $availability->startTime,
                'endTime' => $availability->endTime,
                'slotDuration' => $availability->slotDuration
            ]
        ]);
    }

    /**
     * GET /provider/my-profile
     * Get current user's provider profile
     */
   // ✅ LOWERCASE "p"
 /**
     * GET /provider/myprofile
     */
    public function actionMyprofile()
    {
        $jwt = Yii::app()->jwt;
        $currentUser = $jwt->getCurrentUser();

        /* ------------------------------
         * AUTH CHECK
         * ------------------------------ */
        if (!$currentUser || empty($currentUser['userId'])) {
            $jwt->sendResponse(false, 'Unauthorized', null, 401);
        }

        /* ------------------------------
         * OPTIONAL ROLE CHECK
         * ------------------------------ */
        if (isset($currentUser['role']) && $currentUser['role'] !== 'provider') {
            $jwt->sendResponse(false, 'Only providers can access this', null, 403);
        }

        /* ------------------------------
         * FIND PROVIDER PROFILE
         * ------------------------------ */
        $provider = ServiceProvider::model()->findByAttributes([
            'userUlid' => $currentUser['userId'],
            'status'   => 1
        ]);

        /* ------------------------------
         * IF NOT FOUND → EXPLAIN
         * ------------------------------ */
        if (!$provider) {
            $jwt->sendResponse(false, 'Provider profile not found', [
                'reason' => 'No active provider profile exists for this user'
            ], 404);
        }

        /* ------------------------------
         * LOAD RELATED DATA
         * ------------------------------ */
        $user = User::model()->findByPk($currentUser['userId']);

        $category = ServiceCategory::model()->findByAttributes([
            'categoryUlid' => $provider->categoryUlid
        ]);

        /* ------------------------------
         * RESPONSE DATA
         * ------------------------------ */
        $data = [
            'providerUlid'     => $provider->providerUlid,
            'userUlid'         => $provider->userUlid,
            'categoryUlid'     => $provider->categoryUlid,
            'name'             => $user ? $user->name : null,
            'email'            => $user ? $user->email : null,
            'phone'            => $user ? $user->phone : null,
            'experienceYears'  => $provider->experienceYears,
            'bio'              => $provider->bio,
            'hourlyRate'       => $provider->hourlyRate,
            'categoryName'     => $category ? $category->name : null,
            'status'           => $provider->status,
            'createdAt'        => $provider->createdAt,
            'updatedAt'        => $provider->updatedAt
        ];

        /* ------------------------------
         * SUCCESS
         * ------------------------------ */
        $jwt->sendResponse(true, 'Provider profile fetched', $data);
    }



    /**
     * GET /provider/by-category/{categoryUlid}
     * Get providers by category with pagination
     */
    public function actionByCategory($categoryUlid)
    {
        $page = (int)Yii::app()->request->getQuery('page', 1);
        $limit = (int)Yii::app()->request->getQuery('limit', 10);
        $offset = ($page - 1) * $limit;

        $criteria = new CDbCriteria();
        $criteria->condition = 't.status = 1 AND t.categoryUlid = :categoryUlid';
        $criteria->params = [':categoryUlid' => $categoryUlid];
        $criteria->with = ['user'];
        $criteria->order = 't.createdAt DESC';
        $criteria->limit = $limit;
        $criteria->offset = $offset;

        $providers = ServiceProvider::model()->with('user')->findAll($criteria);
        $totalCount = ServiceProvider::model()->count('status = 1 AND categoryUlid = :categoryUlid', [
            ':categoryUlid' => $categoryUlid
        ]);

        $data = [];
        foreach ($providers as $provider) {
            $user = $provider->user;
            $category = ServiceCategory::model()->findByAttributes(['categoryUlid' => $provider->categoryUlid]);

            $data[] = [
                'providerUlid' => $provider->providerUlid,
                'name' => $user ? $user->name : null,
                'email' => $user ? $user->email : null,
                'experienceYears' => $provider->experienceYears,
                'bio' => $provider->bio,
                'website' => $provider->website,
                'hourlyRate' => $provider->hourlyRate,
                'categoryName' => $category ? $category->name : null,
                'createdAt' => $provider->createdAt
            ];
        }

        $this->sendJson([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'pages' => ceil($totalCount / $limit)
            ]
        ]);
    }
}