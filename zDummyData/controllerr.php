<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
    public $layout='//layouts/column1';
    public $menu=array();
    public $breadcrumbs=array();

    /**
     * Add CORS headers safely (without breaking UI rendering)
     */
    public function beforeAction($action)
    {
		if ($this->isApiRequest()) {
        // Prevent Yii from redirecting to login page
        Yii::app()->user->loginUrl = null;
    }
        // Apply CORS only for AJAX / API requests
        if ($this->isApiRequest()) {
            $this->setCorsHeaders();
        }

        return parent::beforeAction($action);
    }

    /**
     * Detect API / AJAX requests
     */
    protected function isApiRequest()
    {
        return Yii::app()->request->isAjaxRequest
            || isset($_SERVER['HTTP_AUTHORIZATION'])
            || strpos(Yii::app()->request->contentType, 'application/json') !== false;
    }

    /**
     * Set CORS headers
     */
    protected function setCorsHeaders()
    {
        // if (isset($_SERVER['HTTP_ORIGIN'])) {
        //     header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        //     header("Access-Control-Allow-Credentials: true");
        // }

        header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Max-Age: 86400");

        // Handle preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            Yii::app()->end();
        }
    }
}
