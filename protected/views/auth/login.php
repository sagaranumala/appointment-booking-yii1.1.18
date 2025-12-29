<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        
        .login-wrapper {
            display: flex;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box; /* This is important */
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .errorMessage {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .checkbox-group label {
            margin: 0;
            font-weight: normal;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button[type="submit"]:hover {
            background: #0056b3;
        }
        
        .error-summary {
            background: #ffeaea;
            border: 1px solid #ffcccc;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            color: #c0392b;
        }
        
        .text-center {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .text-center a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        
        .text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <h1>Login</h1>
            
            <?php if ($model->hasErrors()): ?>
                <div class="error-summary">
                    Please fix the following errors:
                </div>
            <?php endif; ?>
            
            <?php $form = $this->beginWidget('CActiveForm', array(
                'id' => 'login-form',
                'enableClientValidation' => true,
                'clientOptions' => array(
                    'validateOnSubmit' => true,
                    'errorCssClass' => 'has-error',
                ),
            )); ?>
            
            <div class="form-group">
                <?php echo $form->label($model, 'email', array('label' => 'Email Address')); ?>
                <?php echo $form->textField($model, 'email', array(
                    'placeholder' => 'Enter your email',
                    'autocomplete' => 'email'
                )); ?>
                <?php echo $form->error($model, 'email'); ?>
            </div>
            
            <div class="form-group">
                <?php echo $form->label($model, 'password', array('label' => 'Password')); ?>
                <?php echo $form->passwordField($model, 'password', array(
                    'placeholder' => 'Enter your password',
                    'autocomplete' => 'current-password'
                )); ?>
                <?php echo $form->error($model, 'password'); ?>
            </div>
            
            <div class="checkbox-group">
                <?php echo $form->checkBox($model, 'rememberMe'); ?>
                <?php echo $form->label($model, 'rememberMe', array('label' => 'Remember me')); ?>
                <?php echo $form->error($model, 'rememberMe'); ?>
            </div>
            
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
            
            <?php $this->endWidget(); ?>
            
            <div class="text-center">
                <p>Don't have an account?
                    <?php echo CHtml::link('Sign Up', array('auth/signup'), array('class' => 'text-primary')); ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>