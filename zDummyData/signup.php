<?php
/* @var $this SiteController */
/* @var $model SignupForm */

$this->pageTitle = Yii::app()->name . ' - Sign Up';
$this->breadcrumbs = array('Sign Up');
?>

<div class="signup-container">
    <h1 class="text-center">Create Your Account</h1>
    <p class="text-center">Join our community today</p>

    <?php if (Yii::app()->user->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo Yii::app()->user->getFlash('success'); ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::app()->user->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo Yii::app()->user->getFlash('error'); ?>
        </div>
    <?php endif; ?>


    <div class="card shadow-sm">
        <div class="card-body">

            <?php $form = $this->beginWidget('CActiveForm', array(
                'id' => 'signup-form',
                'enableClientValidation' => true,
                'clientOptions' => array(
                    'validateOnSubmit' => true,
                ),
                'htmlOptions' => array('class' => 'needs-validation', 'novalidate' => 'novalidate'),
            )); ?>

            <div class="row">

                <!-- NAME -->
                <div class="col-md-12 mb-3">
                    <?php echo $form->labelEx($model, 'name', array('class' => 'form-label')); ?>
                    <?php echo $form->textField($model, 'name', array(
                        'class' => 'form-control',
                        'placeholder' => 'Enter your full name',
                        'required' => true
                    )); ?>
                    <div class="invalid-feedback"><?php echo $form->error($model, 'name'); ?></div>
                </div>

                <!-- EMAIL -->
                <div class="col-md-6 mb-3">
                    <?php echo $form->labelEx($model, 'email', array('class' => 'form-label')); ?>
                    <?php echo $form->textField($model, 'email', array(
                        'class' => 'form-control',
                        'placeholder' => 'you@example.com',
                        'type' => 'email',
                        'required' => true
                    )); ?>
                    <div class="invalid-feedback"><?php echo $form->error($model, 'email'); ?></div>
                </div>

                <!-- PHONE -->
                <div class="col-md-6 mb-3">
                    <?php echo $form->labelEx($model, 'phone', array('class' => 'form-label')); ?>
                    <?php echo $form->textField($model, 'phone', array(
                        'class' => 'form-control',
                        'placeholder' => '9876543210'
                    )); ?>
                    <div class="invalid-feedback"><?php echo $form->error($model, 'phone'); ?></div>
                </div>

                <!-- PASSWORD -->
                <div class="col-md-6 mb-3">
                    <div class="password-wrapper">
                        <?php echo $form->passwordField($model, 'password', array(
                            'class' => 'form-control',
                            'placeholder' => '••••••••',
                            'required' => true,
                            'id' => 'password-field'
                        )); ?>

                        <span class="toggle-password" id="togglePassword">
                            👁️
                        </span>
                    </div>


                    <div class="password-requirements mt-2">
                        <small class="text-muted">Password must:</small>
                        <ul class="list-unstyled mb-0">
                            <li class="req-length">• At least 8 characters</li>
                            <li class="req-upper">• One uppercase letter</li>
                            <li class="req-lower">• One lowercase letter</li>
                            <li class="req-number">• One number</li>
                        </ul>
                    </div>

                    <div class="invalid-feedback"><?php echo $form->error($model, 'password'); ?></div>
                </div>

                <!-- PASSWORD REPEAT -->
                <div class="col-md-6 mb-3">
                    <?php echo $form->labelEx($model, 'password_repeat', array('class' => 'form-label')); ?>
                    <?php echo $form->passwordField($model, 'password_repeat', array(
                        'class' => 'form-control',
                        'placeholder' => '••••••••',
                        'required' => true
                    )); ?>
                    <div class="invalid-feedback"><?php echo $form->error($model, 'password_repeat'); ?></div>
                </div>

                <!-- ROLE -->
                <div class="col-md-12 mb-3">
                    <?php echo $form->labelEx($model, 'role', array('class' => 'form-label')); ?>
                    <?php echo $form->dropDownList($model, 'role', $model->getRoleOptions(), array(
                        'class' => 'form-control',
                        'empty' => '-- Select Account Type --'
                    )); ?>
                    <div class="invalid-feedback"><?php echo $form->error($model, 'role'); ?></div>
                </div>

                <!-- TERMS -->
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <?php echo $form->checkBox($model, 'agree_terms', array(
                            'class' => 'form-check-input',
                            'id' => 'agree-terms'
                        )); ?>

                        <?php echo $form->label($model, 'agree_terms', array(
                            'class' => 'form-check-label',
                            'for' => 'agree-terms'
                        )); ?>

                        <div class="invalid-feedback" style="display:block;">
                            <?php echo $form->error($model, 'agree_terms'); ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SUBMIT BUTTON -->
            <div class="d-grid gap-2">
                <?php echo CHtml::submitButton('Create Account', array(
                    'class' => 'btn btn-primary btn-lg disabled-btn',
                    'id' => 'submit-btn',
                    'disabled' => true
                )); ?>

                <div class="text-center mt-3">
                    <p>Already have an account?
                        <?php echo CHtml::link('Sign In', array('auth/login'), array('class' => 'text-primary')); ?>
                    </p>
                </div>
            </div>

            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>


<!-- ------------- CSS ------------- -->
<style>
.signup-container { max-width: 650px; padding: 20px; }

.card {
    border: none; border-radius: 18px;
    background: #fff; padding: 25px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
}

.form-control {
    width: 100%; height: 48px;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    padding-left: 14px; margin-top: 5px;
}

.disabled-btn {
    background: #9ca3af !important; /* grey */
    border: none !important;
    cursor: not-allowed !important;
}

.active-btn {
    background: #2563eb !important; /* blue */
    cursor: pointer !important;
}

.password-requirements li {
    font-size: 0.85rem;
}
.password-requirements .valid { color: green; }
.password-requirements .invalid { color: red; }

.password-wrapper {
    position: relative;
}

.password-wrapper .toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    user-select: none;
    opacity: 0.6;
}

.password-wrapper .toggle-password:hover {
    opacity: 1;
}

</style>


<!-- ------------- JS ------------- -->
<script type="text/javascript">
$(document).ready(function() {

    function validateForm() {
        let name = $('#SignupForm_name').val().trim().length > 1;
        let email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($('#SignupForm_email').val());
        let phone = $('#SignupForm_phone').val().trim().length > 0;

        let pass = $('#password-field').val();
        let passRepeat = $('#SignupForm_password_repeat').val();
        let role = $('#SignupForm_role').val() !== "";
        let agree = $('#agree-terms').is(":checked");

        let passValid =
            pass.length >= 8 &&
            /[A-Z]/.test(pass) &&
            /[a-z]/.test(pass) &&
            /[0-9]/.test(pass);

        let passMatch = pass === passRepeat;

        let allValid = name && email && phone && passValid && passMatch && role && agree;

        if (allValid) {
            $('#submit-btn')
                .removeClass('disabled-btn')
                .addClass('active-btn')
                .prop('disabled', false);
        } else {
            $('#submit-btn')
                .removeClass('active-btn')
                .addClass('disabled-btn')
                .prop('disabled', true);
        }
    }

    // real-time validation
    $('input, select').on('keyup change blur', validateForm);

    // password rule checks
    $('#password-field').on('keyup', function() {
        var p = $(this).val();

        $('.req-length').toggleClass('valid', p.length >= 8).toggleClass('invalid', p.length < 8);
        $('.req-upper').toggleClass('valid', /[A-Z]/.test(p)).toggleClass('invalid', !(/[A-Z]/.test(p)));
        $('.req-lower').toggleClass('valid', /[a-z]/.test(p)).toggleClass('invalid', !(/[a-z]/.test(p)));
        $('.req-number').toggleClass('valid', /[0-9]/.test(p)).toggleClass('invalid', !(/[0-9]/.test(p)));
    });

});
</script>