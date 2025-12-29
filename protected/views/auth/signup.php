<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .signup-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .signup-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        input.error-field {
            border-color: #e74c3c;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            min-height: 18px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            position: relative;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
            margin-top: 3px;
        }
        
        .checkbox-group label {
            margin: 0;
            font-weight: normal;
            font-size: 14px;
            flex: 1;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button[type="submit"]:hover {
            background: #218838;
        }
        
        button[type="submit"]:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .error-summary {
            background: #ffeaea;
            border: 1px solid #ffcccc;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            color: #c0392b;
            font-size: 14px;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 33px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 16px;
            color: #666;
        }
        
        .field-hint {
            color: #6c757d;
            font-size: 12px;
            margin-top: 3px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="signup-wrapper">
        <div class="signup-container">
            <h1>Create Your Account</h1>
            <p class="subtitle">Join our community today</p>
            
            <?php if ($model->hasErrors()): ?>
                <div class="error-summary">
                    Please fix the following errors:
                </div>
            <?php endif; ?>
            
            <?php $form = $this->beginWidget('CActiveForm', array(
                'id' => 'signup-form',
                'enableClientValidation' => true,
                'clientOptions' => array(
                    'validateOnSubmit' => true,
                    'validateOnChange' => true,
                    'validationDelay' => 300,
                    'errorCssClass' => 'error-field',
                    'successCssClass' => 'success-field',
                    'afterValidateAttribute' => 'js:function(form, attribute, data, hasError) {
                        updateSubmitButton();
                    }',
                    'afterValidate' => 'js:function(form, data, hasError) {
                        updateSubmitButton();
                        return !hasError;
                    }'
                ),
            )); ?>
            
            <div class="form-group">
                <?php echo $form->labelEx($model, 'name'); ?>
                <?php echo $form->textField($model, 'name', array(
                    'class' => 'form-control',
                    'placeholder' => 'Enter your full name',
                    'id' => 'SignupForm_name'
                )); ?>
                <?php echo $form->error($model, 'name', array('class' => 'error-message')); ?>
                <span class="field-hint">Minimum 2 characters</span>
            </div>
            
            <div class="form-group">
                <?php echo $form->labelEx($model, 'email'); ?>
                <?php echo $form->textField($model, 'email', array(
                    'class' => 'form-control',
                    'placeholder' => 'Enter your email',
                    'type' => 'email',
                    'id' => 'SignupForm_email'
                )); ?>
                <?php echo $form->error($model, 'email', array('class' => 'error-message')); ?>
            </div>
            
            <div class="form-group">
                <?php echo $form->labelEx($model, 'phone'); ?>
                <?php echo $form->textField($model, 'phone', array(
                    'class' => 'form-control',
                    'placeholder' => 'Phone number (optional)',
                    'id' => 'SignupForm_phone'
                )); ?>
                <?php echo $form->error($model, 'phone', array('class' => 'error-message')); ?>
                <span class="field-hint">Optional</span>
            </div>
            
            <div class="form-group password-container">
                <?php echo $form->labelEx($model, 'password'); ?>
                <?php echo $form->passwordField($model, 'password', array(
                    'class' => 'form-control',
                    'placeholder' => 'Enter password (min 6 characters)',
                    'id' => 'SignupForm_password'
                )); ?>
                <button type="button" class="password-toggle" onclick="togglePassword('SignupForm_password')">👁️</button>
                <?php echo $form->error($model, 'password', array('class' => 'error-message')); ?>
                <span class="field-hint">Minimum 6 characters</span>
            </div>
            
            <div class="form-group password-container">
                <?php echo $form->labelEx($model, 'password_repeat'); ?>
                <?php echo $form->passwordField($model, 'password_repeat', array(
                    'class' => 'form-control',
                    'placeholder' => 'Confirm your password',
                    'id' => 'SignupForm_password_repeat'
                )); ?>
                <button type="button" class="password-toggle" onclick="togglePassword('SignupForm_password_repeat')">👁️</button>
                <?php echo $form->error($model, 'password_repeat', array('class' => 'error-message')); ?>
            </div>
            
            <div class="form-group">
                <?php echo $form->labelEx($model, 'role'); ?>
                <?php echo $form->dropDownList($model, 'role', array(
                    'user' => 'Regular User',
                    'admin' => 'Administrator'
                ), array(
                    'class' => 'form-control',
                    'empty' => '-- Select Role --',
                    'id' => 'SignupForm_role'
                )); ?>
                <?php echo $form->error($model, 'role', array('class' => 'error-message')); ?>
            </div>
            
            <div class="checkbox-group">
                <?php echo $form->checkBox($model, 'agree_terms', array(
                    'id' => 'SignupForm_agree_terms'
                )); ?>
                <?php echo $form->label($model, 'agree_terms', array(
                    'label' => 'I agree to the terms and conditions',
                    'class' => 'checkbox-label'
                )); ?>
                <?php echo $form->error($model, 'agree_terms', array('class' => 'error-message')); ?>
            </div>
            
            <div class="form-group">
                <button type="submit" id="submit-btn" disabled>Sign Up</button>
            </div>
            
            <?php $this->endWidget(); ?>
            
            <div class="login-link">
                Already have an account? 
                <a href="<?php echo $this->createUrl('auth/login'); ?>">Login here</a>
            </div>
        </div>
    </div>

    <script>
        // Password toggle function
        function togglePassword(fieldId) {
            var field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }
        
        // Real-time password match validation
        function validatePasswordMatch() {
            var password = document.getElementById('SignupForm_password');
            var passwordRepeat = document.getElementById('SignupForm_password_repeat');
            var errorElement = document.querySelector('#SignupForm_password_repeat + .error-message');
            
            if (password && passwordRepeat) {
                if (password.value !== passwordRepeat.value && passwordRepeat.value !== '') {
                    // Show error
                    if (!errorElement) {
                        errorElement = document.createElement('div');
                        errorElement.className = 'error-message';
                        errorElement.id = 'password-match-error';
                        passwordRepeat.parentNode.insertBefore(errorElement, passwordRepeat.nextSibling);
                    }
                    errorElement.textContent = 'Passwords do not match.';
                    passwordRepeat.classList.add('error-field');
                    return false;
                } else {
                    // Remove error
                    if (errorElement && errorElement.id === 'password-match-error') {
                        errorElement.remove();
                    }
                    passwordRepeat.classList.remove('error-field');
                    return true;
                }
            }
            return true;
        }
        
        // Check if all required fields are filled
        function checkFormValidity() {
            var requiredFields = [
                'SignupForm_name',
                'SignupForm_email', 
                'SignupForm_password',
                'SignupForm_password_repeat',
                'SignupForm_role'
            ];
            
            var isValid = true;
            
            // Check required fields
            for (var i = 0; i < requiredFields.length; i++) {
                var field = document.getElementById(requiredFields[i]);
                if (field && (!field.value || field.value.trim() === '')) {
                    isValid = false;
                    break;
                }
            }
            
            // Check password match
            if (!validatePasswordMatch()) {
                isValid = false;
            }
            
            // Check terms checkbox
            var agreeTerms = document.getElementById('SignupForm_agree_terms');
            if (agreeTerms && !agreeTerms.checked) {
                isValid = false;
            }
            
            return isValid;
        }
        
        // Update submit button state
        function updateSubmitButton() {
            var isValid = checkFormValidity();
            var submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.disabled = !isValid;
                submitBtn.style.opacity = isValid ? '1' : '0.6';
            }
        }
        
        // Attach event listeners to all form fields
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('signup-form');
            if (form) {
                // Listen to input events on all fields
                var inputs = form.querySelectorAll('input, select');
                inputs.forEach(function(input) {
                    input.addEventListener('input', updateSubmitButton);
                    input.addEventListener('change', updateSubmitButton);
                    input.addEventListener('blur', updateSubmitButton);
                });
                
                // Special handling for password fields
                var password = document.getElementById('SignupForm_password');
                var passwordRepeat = document.getElementById('SignupForm_password_repeat');
                
                if (password && passwordRepeat) {
                    password.addEventListener('input', function() {
                        validatePasswordMatch();
                        updateSubmitButton();
                    });
                    
                    passwordRepeat.addEventListener('input', function() {
                        validatePasswordMatch();
                        updateSubmitButton();
                    });
                }
                
                // Initial check
                updateSubmitButton();
            }
        });
        
        // Form submission validation
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            if (!checkFormValidity()) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
                return false;
            }
            return true;
        });
    </script>
</body>
</html>