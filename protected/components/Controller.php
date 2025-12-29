<?php
class Controller extends CController
{
    public $layout = '//layouts/column1';
    public $menu = array();
    public $breadcrumbs = array();

    protected function sendJson($data, $status = 200)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($status);
        }
        echo json_encode($data, JSON_PRETTY_PRINT);
        Yii::app()->end();
    }
}
