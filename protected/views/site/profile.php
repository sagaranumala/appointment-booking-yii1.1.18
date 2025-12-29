<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .back-btn {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #2980b9;
        }
        .profile-info {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
            color: #2c3e50;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>User Profile</h1>
            <a href="<?php echo $this->createUrl('site/dashboard'); ?>" class="back-btn">Back to Dashboard</a>
        </div>
        
        <div class="profile-info">
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($user->name); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo htmlspecialchars($user->email); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">User ID:</div>
                <div class="info-value"><?php echo htmlspecialchars($user->userId); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Role:</div>
                <div class="info-value"><?php echo htmlspecialchars($user->role); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value"><?php echo htmlspecialchars($user->phone ?: 'Not set'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Created:</div>
                <div class="info-value"><?php echo date('F j, Y, g:i a', strtotime($user->createdAt)); ?></div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo $this->createUrl('site/logout'); ?>" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Logout</a>
        </div>
    </div>
</body>
</html>