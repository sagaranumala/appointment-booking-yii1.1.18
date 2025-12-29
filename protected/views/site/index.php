<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>
    <h1>Welcome to the Application</h1>
    
    <?php if (Yii::app()->user->isGuest): ?>
        <p>Please <a href="<?php echo $this->createUrl('auth/login'); ?>">login</a> to continue.</p>
    <?php else: ?>
        <p>Welcome, <?php echo Yii::app()->user->name; ?>!</p>
        <p><a href="<?php echo $this->createUrl('site/logout'); ?>">Logout</a></p>
    <?php endif; ?>
    
    <hr>
    
    <h3>API Endpoints:</h3>
    <ul>
        <li><strong>POST</strong> /index.php?r=auth/login - Login with email & password</li>
        <li><strong>GET</strong> /index.php?r=auth/validate - Validate JWT token</li>
        <li><strong>POST</strong> /index.php?r=auth/register - Register new user</li>
    </ul>
</body>
</html>