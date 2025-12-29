<?php
// protected/controllers/CategoryController.php

class CategoryController extends Controller
{
    /**
     * Disable layout for API responses
     */
    public $layout = false;

    /**
     * Disable CSRF for API actions
     */
    public function beforeAction($action)
    {
        $apiActions = ['index', 'view', 'create', 'update', 'delete', 'search'];
        
        if (in_array($action->id, $apiActions)) {
            Yii::app()->request->enableCsrfValidation = false;
        }
        
        return parent::beforeAction($action);
    }

    /**
     * GET /category/index
     * List all service categories with pagination
     */

    public function actionIndex()
{
    try {
        // Get pagination parameters
        $page = (int)Yii::app()->request->getParam('page', 1);
        $limit = (int)Yii::app()->request->getParam('limit', 20);
        $offset = ($page - 1) * $limit;
        
        // Build criteria
        $criteria = new CDbCriteria();
        // $criteria->condition = 'status = 1';
        $criteria->order = 'name ASC';
        $criteria->limit = $limit;
        $criteria->offset = $offset;
        
        // Get categories
        $categories = ServiceCategory::model()->findAll($criteria);
        $totalCount = ServiceCategory::model()->count('status = 1');
        
        // Format response
        $data = [];
        foreach ($categories as $category) {
            // Count providers linked to this category
            $providersCount = ServiceProvider::model()->countByAttributes([
                'categoryUlid' => $category->categoryUlid,
                'status' => 1
            ]);

            $data[] = [
                'categoryUlid'   => $category->categoryUlid,
                'name'           => $category->name,
                'description'    => $category->description,
                'createdAt'      => $category->createdAt,
                'updatedAt'      => $category->updatedAt,
                'status'         => $category->status,
                'providersCount' => (int)$providersCount, // added provider count
            ];
        }
        
        // Return success response
        $this->sendJson([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'pages' => ceil($totalCount / $limit)
            ]
        ], 200);
        
    } catch (Exception $e) {
        $this->sendJsonError('Server error: ' . $e->getMessage(), 500);
    }
}


    /**
     * GET /category/view/{categoryUlid}
     * View single category
     */
    public function actionView($categoryUlid)
    {
        try {
            $category = ServiceCategory::model()->findByAttributes([
                'categoryUlid' => $categoryUlid,
                'status' => 1
            ]);
            
            if (!$category) {
                $this->sendJsonError('Category not found', 404);
            }
            
            $this->sendJson([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => [
                    'categoryUlid' => $category->categoryUlid,  // Changed from categoryId
                    'name' => $category->name,
                    'description' => $category->description,
                    'createdAt' => $category->createdAt,
                    'updatedAt' => $category->updatedAt
                ]
            ], 200);
            
        } catch (Exception $e) {
            $this->sendJsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /category/create
     * Create new category
     */
    public function actionCreate()
    {
        try {
            // Check if it's POST request
            if (!Yii::app()->request->isPostRequest) {
                $this->sendJsonError('POST request required', 405);
            }
            
            // Get POST data
            $name = trim(Yii::app()->request->getPost('name', ''));
            $description = trim(Yii::app()->request->getPost('description', ''));
            
            // Validate input
            if (empty($name)) {
                $this->sendJsonError('Category name is required', 400);
            }
            
            // Check for duplicates - using ServiceCategory
            $existing = ServiceCategory::model()->findByAttributes([
                'name' => $name,
                'status' => 1
            ]);
            
            if ($existing) {
                $this->sendJsonError('Category with this name already exists', 409);
            }
            
            // Generate ULID
            // $ulid = $this->generateUlid();
            
            // Create new category - using ServiceCategory
            $category = new ServiceCategory();
            // $category->categoryUlid = $ulid;
            $category->name = $name;
            $category->description = $description;
            $category->status = 1;
            // createdAt and updatedAt will be handled by beforeSave if implemented
            
            if (!$category->save()) {
                $this->sendJsonError('Failed to create category', 422, $category->getErrors());
            }
            
            $this->sendJson([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => [
                    'categoryUlid' => $category->categoryUlid,  // Changed from categoryId
                    'name' => $category->name
                ]
            ], 201);
            
        } catch (Exception $e) {
            $this->sendJsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /category/update/{categoryUlid}
     * Update existing category
     */
    public function actionUpdate($categoryUlid)
    {
        try {
            // Check if it's PUT request
            if (!Yii::app()->request->isPutRequest) {
                $this->sendJsonError('PUT request required', 405);
            }
            
            // Find category - using ServiceCategory
            $category = ServiceCategory::model()->findByAttributes([
                'categoryUlid' => $categoryUlid,
                'status' => 1
            ]);
            
            if (!$category) {
                $this->sendJsonError('Category not found', 404);
            }
            
            // Get PUT data
            parse_str(file_get_contents("php://input"), $putData);
            $name = trim($putData['name'] ?? $category->name);
            $description = trim($putData['description'] ?? $category->description);
            
            // Validate input
            if (empty($name)) {
                $this->sendJsonError('Category name is required', 400);
            }
            
            // Check for duplicates (excluding current)
            $existing = ServiceCategory::model()->findByAttributes([
                'name' => $name,
                'status' => 1
            ]);
            
            if ($existing && $existing->categoryUlid != $categoryUlid) {
                $this->sendJsonError('Category with this name already exists', 409);
            }
            
            // Update category
            $category->name = $name;
            $category->description = $description;
            // updatedAt will be handled by beforeSave if implemented
            
            if (!$category->save()) {
                $this->sendJsonError('Failed to update category', 422, $category->getErrors());
            }
            
            $this->sendJson([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => [
                    'categoryUlid' => $category->categoryUlid,  // Changed from categoryId
                    'name' => $category->name
                ]
            ], 200);
            
        } catch (Exception $e) {
            $this->sendJsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /category/delete/{categoryUlid}
     * Soft delete category
     */
    public function actionDelete($categoryUlid)
    {
        try {
            // Check if it's DELETE request
            if (!Yii::app()->request->isDeleteRequest) {
                $this->sendJsonError('DELETE request required', 405);
            }
            
            $category = ServiceCategory::model()->findByAttributes([
                'categoryUlid' => $categoryUlid,
                'status' => 1
            ]);
            
            if (!$category) {
                $this->sendJsonError('Category not found', 404);
            }
            
            // Soft delete (set status = 0)
            $category->status = 0;
            // updatedAt will be handled by beforeSave if implemented
            
            if (!$category->save()) {
                $this->sendJsonError('Failed to delete category', 422, $category->getErrors());
            }
            
            $this->sendJson([
                'success' => true,
                'message' => 'Category deleted successfully'
            ], 200);
            
        } catch (Exception $e) {
            $this->sendJsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /category/search
     * Search categories
     */
    public function actionSearch()
    {
        try {
            $query = trim(Yii::app()->request->getParam('q', ''));
            
            if (empty($query) || strlen($query) < 2) {
                $this->sendJsonError('Search query must be at least 2 characters', 400);
            }
            
            $criteria = new CDbCriteria();
            $criteria->condition = 'status = 1 AND (name LIKE :query OR description LIKE :query)';
            $criteria->params = [':query' => '%' . $query . '%'];
            $criteria->order = 'name ASC';
            $criteria->limit = 20;
            
            $categories = ServiceCategory::model()->findAll($criteria);
            
            $data = [];
            foreach ($categories as $category) {
                $data[] = [
                    'categoryUlid' => $category->categoryUlid,  // Changed from categoryId
                    'name' => $category->name,
                    'description' => $category->description
                ];
            }
            
            $this->sendJson([
                'success' => true,
                'message' => 'Search completed',
                'data' => $data,
                'count' => count($data)
            ], 200);
            
        } catch (Exception $e) {
            $this->sendJsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper: Send JSON response
     */
    protected function sendJson($response, $httpCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo json_encode($response);
        Yii::app()->end();
    }

    /**
     * Helper: Send JSON error response
     */
    protected function sendJsonError($message, $httpCode = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        $this->sendJson($response, $httpCode);
    }
}