<?php
class TestController extends CController
{
    public function actionTest()
    {
        echo "TEST CONTROLLER IS WORKING!";
    }
    
    public function actionCheck()
    {
        echo "<pre>";
        echo "PHP Version: " . phpversion() . "\n";
        echo "Yii Version: " . Yii::getVersion() . "\n";
        echo "Controller Path: " . Yii::app()->getControllerPath() . "\n";
        echo "Real Path: " . realpath(Yii::app()->getControllerPath()) . "\n";
        
        // Check if CategoryController exists
        $categoryFile = Yii::app()->getControllerPath() . '/CategoryController.php';
        echo "\nCategoryController file: " . $categoryFile . "\n";
        echo "Exists: " . (file_exists($categoryFile) ? 'YES' : 'NO') . "\n";
        
        if (file_exists($categoryFile)) {
            echo "Contents (first 200 chars):\n";
            echo htmlspecialchars(substr(file_get_contents($categoryFile), 0, 200)) . "\n";
        }
        
        echo "</pre>";
    }
}