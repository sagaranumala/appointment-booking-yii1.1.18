<?php

class AppointmentController extends BaseApiController
{
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * GET /appointment/index
     */
    // public function actionIndex()
    // {
    //     $currentUser = Yii::app()->jwt->getCurrentUser();
    //     if (!$currentUser || empty($currentUser['userId'])) {
    //         $this->sendJsonError('Unauthorized', 401);
    //         return;
    //     }

    //     $criteria = new CDbCriteria();

    //     // 🔐 Role-based filtering
    //     if (!isset($currentUser['role']) || $currentUser['role'] !== 'admin') {
    //         // Non-admin → only own appointments
    //         $criteria->compare('customerUlid', $currentUser['userId']);
    //     }
    //     // Admin → no filter (fetch all)

    //     $criteria->order = 'appointmentDate ASC, startTime ASC';

    //     $appointments = Appointment::model()->findAll($criteria);

    //     $data = [];

    //     foreach ($appointments as $a) {

    //         // 🔹 Customer
    //         $customer = User::model()->findByAttributes([
    //             'userId' => $a->customerUlid
    //         ]);

    //         // 🔹 Provider profile
    //         $provider = ServiceProvider::model()->findByAttributes([
    //             'providerUlid' => $a->providerUlid
    //         ]);

    //         // 🔹 Provider user
    //         $providerUser = null;
    //         if ($provider && !empty($provider->userUlid)) {
    //             $providerUser = User::model()->findByAttributes([
    //                 'userId' => $provider->userUlid
    //             ]);
    //         }

    //         // 🔹 Category
    //         $category = ServiceCategory::model()->findByPk($a->categoryUlid);

    //         $data[] = [
    //             'appointmentUlid' => $a->appointmentUlid,

    //             // IDs
    //             'customerUlid'    => $a->customerUlid,
    //             'providerUlid'    => $a->providerUlid,
    //             'categoryUlid'    => $a->categoryUlid,

    //             // Names
    //             'customerName'    => $customer ? $customer->name : null,
    //             'providerName'    => $providerUser ? $providerUser->name : null,
    //             'categoryName'    => $category ? $category->name : null,

    //             // Appointment
    //             'appointmentDate' => $a->appointmentDate,
    //             'startTime'       => $a->startTime,
    //             'endTime'         => $a->endTime,
    //             'status'          => $a->status,
    //             'notes'           => $a->notes,

    //             // Pricing
    //             'hourlyRate'      => $provider ? $provider->hourlyRate : null,
    //         ];
    //     }

    //     $this->sendJson([
    //         'success' => true,
    //         'data' => $data
    //     ]);
    // }

   public function actionIndex()
{
    $currentUser = Yii::app()->jwt->getCurrentUser();
    if (!$currentUser || empty($currentUser['userId'])) {
        $this->sendJsonError('Unauthorized', 401);
        return;
    }

    $criteria = new CDbCriteria();

    $role = strtolower($currentUser['role']);

    if ($role === 'admin') {
        // Admin → no filter
    } elseif ($role === 'provider') {
        // Provider → fetch appointments for their providerUlid
        $provider = ServiceProvider::model()->findByAttributes([
            'userUlid' => $currentUser['userId'],
            'status' => 1,
        ]);

        if (!$provider) {
            $this->sendJsonError('Provider profile not found', 404);
            return;
        }

        $criteria->compare('providerUlid', $provider->providerUlid);
    } else {
        // Regular user → fetch only their appointments
        $criteria->compare('customerUlid', $currentUser['userId']);
    }

    $criteria->order = 'appointmentDate ASC, startTime ASC';

    $appointments = Appointment::model()->findAll($criteria);

    $data = [];

    foreach ($appointments as $a) {
        // 🔹 Customer
        $customer = User::model()->findByAttributes([
            'userId' => $a->customerUlid
        ]);

        // 🔹 Provider profile
        $provider = ServiceProvider::model()->findByAttributes([
            'providerUlid' => $a->providerUlid
        ]);

        // 🔹 Provider user
        $providerUser = null;
        if ($provider && !empty($provider->userUlid)) {
            $providerUser = User::model()->findByAttributes([
                'userId' => $provider->userUlid
            ]);
        }

        // 🔹 Category
        $category = ServiceCategory::model()->findByPk($a->categoryUlid);

        $data[] = [
            'appointmentUlid' => $a->appointmentUlid,

            // IDs
            'customerUlid'    => $a->customerUlid,
            'providerUlid'    => $a->providerUlid,
            'categoryUlid'    => $a->categoryUlid,

            // Names
            'customerName'    => $customer ? $customer->name : null,
            'providerName'    => $providerUser ? $providerUser->name : null,
            'categoryName'    => $category ? $category->name : null,

            // Appointment
            'appointmentDate' => $a->appointmentDate,
            'startTime'       => $a->startTime,
            'endTime'         => $a->endTime,
            'status'          => $a->status,
            'notes'           => $a->notes,

            // Pricing
            'hourlyRate'      => $provider ? $provider->hourlyRate : null,
        ];
    }

    $this->sendJson([
        'success' => true,
        'data'    => $data,
    ]);
}



    // public function actionIndex()
    // {
    //     $currentUser = Yii::app()->jwt->getCurrentUser();
    //     if (!$currentUser) {
    //         $this->sendJsonError('Unauthorized', 401);
    //     }

    //     $criteria = new CDbCriteria();
    //     $criteria->compare('customerUlid', $currentUser['userId']);
    //     $criteria->order = 'appointmentDate ASC, startTime ASC';

    //     $appointments = Appointment::model()->findAll($criteria);

    //     $data = [];
    //     foreach ($appointments as $a) {
    //         $data[] = [
    //             'appointmentUlid' => $a->appointmentUlid,
    //             'providerUlid' => $a->providerUlid,
    //             'appointmentDate' => $a->appointmentDate,
    //             'startTime' => $a->startTime,
    //             'endTime' => $a->endTime,
    //             'status' => $a->status,
    //             'notes' => $a->notes,
    //         ];
    //     }

    //     $this->sendJson(['success' => true, 'data' => $data]);
    // }


public function actionCreate()
{
    // Handle CORS preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        Yii::app()->end();
    }

    $jwt = Yii::app()->jwt;
    $currentUser = $jwt->getCurrentUser();

    if (!$currentUser || empty($currentUser['userId'])) {
        $jwt->sendResponse(false, 'Unauthorized', null, 401);
    }

    $appointment = new Appointment();
    $appointment->customerUlid    = $currentUser['userId'];
    $appointment->providerUlid    = Yii::app()->request->getPost('providerUlid');
    $appointment->categoryUlid    = Yii::app()->request->getPost('categoryUlid');
    $appointment->appointmentDate = Yii::app()->request->getPost('appointmentDate');
    $appointment->startTime       = Yii::app()->request->getPost('startTime');
    $appointment->endTime         = Yii::app()->request->getPost('endTime');
    $appointment->notes           = Yii::app()->request->getPost('notes');
    $appointment->status          = 'booked';

    if (!$appointment->save()) {
        $jwt->sendResponse(false, 'Booking failed', $appointment->getErrors(), 422);
    }

    $jwt->sendResponse(true, 'Appointment booked', [
        'appointmentId' => $appointment->id
    ], 201);
}




/**
 * Helper to send JSON response
 */
protected function sendResponse($success, $message, $data = null, $httpCode = 200)
{
    header('Content-Type: application/json');
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    Yii::app()->end();
}



    /**
     * POST /appointment/cancel
     */
    public function actionCancel()
    {
        $this->validateCsrf();

        $appointmentUlid = Yii::app()->request->getPost('appointmentUlid');
        $appointment = Appointment::model()->findByAttributes([
            'appointmentUlid' => $appointmentUlid
        ]);

        if (!$appointment) {
            $this->sendJsonError('Appointment not found', 404);
        }

        $appointment->status = 'cancelled';
        $appointment->save();

        $this->sendJson([
            'success' => true,
            'message' => 'Appointment cancelled'
        ]);
    }

    /**
     * POST /appointment/confirm
     */
    public function actionConfirm()
{
    $this->validateCsrf();

    $appointmentUlid = Yii::app()->request->getPost('appointmentUlid');
    $appointment = Appointment::model()->findByAttributes([
        'appointmentUlid' => $appointmentUlid
    ]);

    if (!$appointment) {
        $this->sendJsonError('Appointment not found', 404);
    }
    // enum('booked','confirmed','cancelled')

    $appointment->status = 'confirmed';
    $appointment->save();

    $this->sendJson([
        'success' => true,
        'message' => 'Appointment confirmed'
    ]);
}

/**
 * GET /appointment/bookings
 * USER side
 */
public function actionBookings()
{
    $jwt = Yii::app()->jwt;
    $currentUser = $jwt->getCurrentUser();

    if (!$currentUser || empty($currentUser['userId'])) {
        $jwt->sendResponse(false, 'Unauthorized', null, 401);
    }

    // Fetch all appointments for current user
    $criteria = new CDbCriteria();
    $criteria->compare('customerUlid', $currentUser['userId']);
    $criteria->order = 'appointmentDate ASC, startTime ASC';
    $appointments = Appointment::model()->findAll($criteria);

    $data = [];

    foreach ($appointments as $a) {

        // 🔹 Get provider info
        $provider = ServiceProvider::model()->findByAttributes([
            'providerUlid' => $a->providerUlid
        ]);

        $providerUser = null;
        if ($provider && !empty($provider->userUlid)) {
            $providerUser = User::model()->findByAttributes([
                'userId' => $provider->userUlid
            ]);
        }

        // 🔹 Get category info
        $category = ServiceCategory::model()->findByPk($a->categoryUlid);

        $data[] = [
            'appointmentUlid' => $a->appointmentUlid,

            // Provider details
            'providerUlid'    => $a->providerUlid,
            'providerName'    => $providerUser ? $providerUser->name : null,
            'providerEmail'   => $providerUser ? $providerUser->email : null,
            'providerPhone'   => $providerUser ? $providerUser->phone : null,

            // Category details
            'categoryUlid'    => $a->categoryUlid,
            'categoryName'    => $category ? $category->name : null,

            // Appointment details
            'appointmentDate' => $a->appointmentDate,
            'startTime'       => $a->startTime,
            'endTime'         => $a->endTime,
            'status'          => $a->status,
            'notes'           => $a->notes,
            'createdAt'       => $a->createdAt,
        ];
    }

    $jwt->sendResponse(true, 'My bookings', $data);
}



    /**
 * GET /appointment/myappointments
 * PROVIDER side
 */
public function actionMyappointments()
{
    $jwt = Yii::app()->jwt;
    $currentUser = $jwt->getCurrentUser();

    // ✅ Check authentication
    if (!$currentUser || empty($currentUser['userId'])) {
        $jwt->sendResponse(false, 'Unauthorized', null, 401);
    }

    // ✅ Ensure only providers can access
    if (!isset($currentUser['role']) || $currentUser['role'] !== 'provider') {
        $jwt->sendResponse(false, 'Only providers allowed', null, 403);
    }

    // 🔍 Fetch provider profile
    $provider = ServiceProvider::model()->findByAttributes([
        'userUlid' => $currentUser['userId'],
        'status'   => 1
    ]);

    if (!$provider) {
        $jwt->sendResponse(false, 'Provider profile not found', null, 404);
    }

    // 🔍 Fetch appointments for this provider
    $criteria = new CDbCriteria();
    $criteria->compare('providerUlid', $provider->providerUlid);
    $criteria->order = 'appointmentDate ASC, startTime ASC';
    $appointments = Appointment::model()->findAll($criteria);

    $data = [];
    foreach ($appointments as $a) {

        // 🔹 Customer info
        $customer = User::model()->findByAttributes([
            'userId' => $a->customerUlid
        ]);

        // 🔹 Category info
        $category = ServiceCategory::model()->findByPk($a->categoryUlid);

        $data[] = [
            'appointmentUlid' => $a->appointmentUlid,

            // Customer
            'customerUlid'    => $a->customerUlid,
            'customerName'    => $customer ? $customer->name : null,
            'customerEmail'   => $customer ? $customer->email : null,
            'customerPhone'   => $customer ? $customer->phone : null,

            // Category
            'categoryUlid'    => $a->categoryUlid,
            'categoryName'    => $category ? $category->name : null,

            // Appointment
            'appointmentDate' => $a->appointmentDate,
            'startTime'       => $a->startTime,
            'endTime'         => $a->endTime,
            'status'          => $a->status,
            'notes'           => $a->notes,
            'createdAt'       => $a->createdAt,
        ];
    }

    $jwt->sendResponse(true, 'My appointments', $data);
}


}
