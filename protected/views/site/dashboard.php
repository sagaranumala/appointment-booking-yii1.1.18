<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome {
            color: #333;
        }
        .welcome h1 {
            margin: 0;
            color: #2c3e50;
        }
        .user-info {
            text-align: right;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card h3 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .user-details p {
            margin: 10px 0;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .quick-links {
            list-style: none;
            padding: 0;
        }
        .quick-links li {
            margin: 10px 0;
        }
        .quick-links a {
            display: block;
            padding: 10px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .quick-links a:hover {
            background: #2980b9;
        }
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="welcome">
                <h1>Welcome, <?php echo htmlspecialchars($user->name); ?>!</h1>
                <p>Last Login: <?php echo $loginTime; ?></p>
            </div>
            <div class="user-info">
                <p>Email: <?php echo htmlspecialchars($user->email); ?></p>
                <p>Role: <?php echo htmlspecialchars($user->role); ?></p>
                <a href="<?php echo $this->createUrl('site/logout'); ?>" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>User Information</h3>
                <div class="user-details">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user->name); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user->email); ?></p>
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($user->userId); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user->phone ?: 'Not set'); ?></p>
                    <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($user->createdAt)); ?></p>
                </div>
            </div>
            
            <div class="card">
                <h3>Quick Links</h3>
                <ul class="quick-links">
                    <li><a href="<?php echo $this->createUrl('site/profile'); ?>">View Profile</a></li>
                    <li><a href="<?php echo $this->createUrl('site/logout'); ?>">Logout</a></li>
                    <li><a href="<?php echo $this->createUrl('auth/validate'); ?>" target="_blank">Validate JWT Token</a></li>
                    <li><a href="<?php echo $this->createUrl('site/index'); ?>">Home</a></li>
                </ul>
            </div>
            
            <div class="card">
                <h3>System Stats</h3>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $totalUsers; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">1</div>
                        <div class="stat-label">Active Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo date('H:i'); ?></div>
                        <div class="stat-label">Current Time</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo strtoupper($user->role); ?></div>
                        <div class="stat-label">Your Role</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h3>API Information</h3>
                <p><strong>Login Endpoint:</strong> POST /index.php?r=auth/login</p>
                <p><strong>Validate Token:</strong> GET /index.php?r=auth/validate</p>
                <p><strong>Register User:</strong> POST /index.php?r=auth/register</p>
                <p><strong>Your Token:</strong> <small>Use Authorization header with Bearer token</small></p>
            </div>
        </div>
    </div>
</body>
</html>