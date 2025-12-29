<?php
// test.php - place in protected folder
define('YII_DEBUG', true);
require_once('yii.php');
Yii::createWebApplication('config/main.php');

echo "Testing Car model...<br>";

// Try 1: Direct include
require_once('models/Car.php');

// Try 2: Using Yii autoload
try {
    $car = new Car();
    echo "✓ Car model loaded via autoload<br>";
    echo "Table: " . $car->tableName() . "<br>";
} catch (Exception $e) {
    echo "✗ Autoload failed: " . $e->getMessage() . "<br>";
}

// Try 3: Check if class exists
if (class_exists('Car')) {
    echo "✓ Car class exists<br>";
} else {
    echo "✗ Car class does NOT exist<br>";
}

// Try 4: Include directly
include_once('models/Car.php');
if (class_exists('Car')) {
    echo "✓ Car class exists after include<br>";
} else {
    echo "✗ Car class still missing<br>";
}
?>