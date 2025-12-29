<?php

class PurchaseController extends Controller
{
    /**
     * Action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    /**
     * Access rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'users' => array('@'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * List all purchases
     */
    public function actionIndex()
    {
        $model = new Purchase('search');
        $model->unsetAttributes();
        
        if (isset($_GET['Purchase'])) {
            $model->attributes = $_GET['Purchase'];
        }

        if ($this->isApiRequest()) {
            $criteria = new CDbCriteria();
            $criteria->with = array('supplier', 'warehouse', 'items', 'items.product');
            $criteria->order = 't.createdAt DESC';
            
            // Apply filters
            if (isset($_GET['status']) && $_GET['status']) {
                $criteria->compare('t.status', $_GET['status']);
            }
            
            if (isset($_GET['supplierId']) && $_GET['supplierId']) {
                $criteria->compare('t.supplierId', $_GET['supplierId']);
            }
            
            if (isset($_GET['warehouseId']) && $_GET['warehouseId']) {
                $criteria->compare('t.warehouseId', $_GET['warehouseId']);
            }
            
            if (isset($_GET['search']) && $_GET['search']) {
                $search = $_GET['search'];
                $criteria->addSearchCondition('t.purchaseId', $search, true, 'OR');
                $criteria->addSearchCondition('t.notes', $search, true, 'OR');
            }
            
            $purchases = Purchase::model()->findAll($criteria);
            $data = $this->arToArray($purchases);
            
            $this->sendJson(array(
                'success' => true,
                'data' => $data,
            ));
            return;
        }
        
        $this->render('index', array(
            'model' => $model,
        ));
    }

    /**
     * View purchase details
     */
    public function actionView($id)
    {
        $purchase = Purchase::model()->with('supplier', 'warehouse', 'items.product')->findByAttributes(array(
            'purchaseId' => $id
        ));
        
        if (!$purchase) {
            if ($this->isApiRequest()) {
                $this->sendJson(array(
                    'success' => false,
                    'message' => 'Purchase not found',
                ));
                return;
            }
            throw new CHttpException(404, 'Purchase not found');
        }

        if ($this->isApiRequest()) {
            $data = $this->arToArray($purchase);
            
            $this->sendJson(array(
                'success' => true,
                'data' => $data,
            ));
            return;
        }
        
        $this->render('view', array(
            'purchase' => $purchase,
        ));
    }

    /**
     * Create new purchase
     */
    public function actionCreate()
    {
        if ($this->isApiRequest()) {
            $transaction = Yii::app()->db->beginTransaction();
            
            try {
                // Create purchase
                $purchase = new Purchase();
                $purchase->attributes = $_POST;
                
                if (!$purchase->save()) {
                    throw new Exception('Failed to save purchase: ' . $purchase->getErrorsString());
                }
                
                // Save purchase items
                if (isset($_POST['items']) && is_array($_POST['items'])) {
                    foreach ($_POST['items'] as $itemData) {
                        $item = new PurchaseItem();
                        $item->attributes = $itemData;
                        $item->purchaseId = $purchase->purchaseId;
                        
                        if (!$item->save()) {
                            throw new Exception('Failed to save purchase item: ' . $item->getErrorsString());
                        }
                    }
                    
                    // Recalculate total amount
                    $purchase->refresh();
                }
                
                $transaction->commit();
                
                $this->sendJson(array(
                    'success' => true,
                    'message' => 'Purchase created successfully',
                    'data' => $this->arToArray($purchase),
                ));
                
            } catch (Exception $e) {
                $transaction->rollback();
                
                $this->sendJson(array(
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => isset($purchase) ? $purchase->getErrors() : array(),
                ));
            }
            return;
        }
        
        $this->render('create');
    }

    /**
     * Update purchase
     */
    public function actionUpdate($id)
    {
        $purchase = Purchase::model()->findByAttributes(array(
            'purchaseId' => $id
        ));
        
        if (!$purchase) {
            if ($this->isApiRequest()) {
                $this->sendJson(array(
                    'success' => false,
                    'message' => 'Purchase not found',
                ));
                return;
            }
            throw new CHttpException(404, 'Purchase not found');
        }

        if ($this->isApiRequest()) {
            $purchase->attributes = $_POST;
            
            if ($purchase->save()) {
                $this->sendJson(array(
                    'success' => true,
                    'message' => 'Purchase updated successfully',
                    'data' => $this->arToArray($purchase),
                ));
            } else {
                $this->sendJson(array(
                    'success' => false,
                    'message' => 'Failed to update purchase',
                    'errors' => $purchase->getErrors(),
                ));
            }
            return;
        }
        
        $this->render('update', array(
            'purchase' => $purchase,
        ));
    }

    /**
     * Update purchase status
     */
    public function actionUpdateStatus($id)
    {
        $purchase = Purchase::model()->findByAttributes(array(
            'purchaseId' => $id
        ));
        
        if (!$purchase) {
            $this->sendJson(array(
                'success' => false,
                'message' => 'Purchase not found',
            ));
            return;
        }
        
        $status = Yii::app()->request->getPost('status');
        $validStatuses = array_keys(Purchase::getStatusOptions());
        
        if (!$status || !in_array($status, $validStatuses)) {
            $this->sendJson(array(
                'success' => false,
                'message' => 'Invalid status',
            ));
            return;
        }
        
        $purchase->status = $status;
        
        if ($purchase->save()) {
            $this->sendJson(array(
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $this->arToArray($purchase),
            ));
        } else {
            $this->sendJson(array(
                'success' => false,
                'message' => 'Failed to update status',
                'errors' => $purchase->getErrors(),
            ));
        }
    }

    /**
     * Delete purchase
     */
    public function actionDelete($id)
    {
        $purchase = Purchase::model()->findByAttributes(array(
            'purchaseId' => $id
        ));
        
        if (!$purchase) {
            $this->sendJson(array(
                'success' => false,
                'message' => 'Purchase not found',
            ));
            return;
        }

        // Only allow deletion of draft purchases
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            $this->sendJson(array(
                'success' => false,
                'message' => 'Only draft purchases can be deleted',
            ));
            return;
        }

        if ($purchase->delete()) {
            $this->sendJson(array(
                'success' => true,
                'message' => 'Purchase deleted successfully',
            ));
        } else {
            $this->sendJson(array(
                'success' => false,
                'message' => 'Failed to delete purchase',
            ));
        }
    }

    /**
     * Get purchase statistics
     */
    public function actionStats()
    {
        // Total purchases
        $total = Purchase::model()->count();
        
        // Total amount
        $totalAmount = Purchase::model()->findBySql('SELECT SUM(totalAmount) as total FROM purchases');
        $totalAmount = $totalAmount ? $totalAmount->total : 0;
        
        // By status
        $byStatus = Yii::app()->db->createCommand()
            ->select('status, COUNT(*) as count, SUM(totalAmount) as amount')
            ->from('purchases')
            ->group('status')
            ->queryAll();
        
        // Recent purchases
        $recent = Purchase::model()->with('supplier', 'warehouse')->findAll(array(
            'order' => 'createdAt DESC',
            'limit' => 5,
        ));
        
        $this->sendJson(array(
            'success' => true,
            'data' => array(
                'total' => $total,
                'totalAmount' => (float)$totalAmount,
                'byStatus' => $byStatus,
                'recent' => $this->arToArray($recent),
            ),
        ));
    }

    /**
     * Convert ActiveRecord to array with relations
     */
    protected function arToArray($ar)
    {
        if (is_array($ar)) {
            $data = [];
            foreach ($ar as $item) {
                $data[] = $this->arToArray($item);
            }
            return $data;
        }
        $attributes = $ar->attributes;
        foreach ($ar->relations() as $name => $relation) {
            if ($ar->$name !== null) {
                $attributes[$name] = $this->arToArray($ar->$name);
            }
        }
        return $attributes;
    }

    /**
     * Check if request is API request
     */
    protected function isApiRequest()
    {
        return Yii::app()->request->getParam('format') === 'json' || 
               Yii::app()->request->isAjaxRequest;
    }

    /**
     * Send JSON response
     */
    protected function sendJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        Yii::app()->end();
    }
}