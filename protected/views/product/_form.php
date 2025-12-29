<?php
/* @var $this ProductController */
/* @var $model Product */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'product-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'productId'); ?>
		<?php echo $form->textField($model,'productId',array('size'=>26,'maxlength'=>26)); ?>
		<?php echo $form->error($model,'productId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'sku'); ?>
		<?php echo $form->textField($model,'sku',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'sku'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'categoryId'); ?>
		<?php echo $form->textField($model,'categoryId',array('size'=>26,'maxlength'=>26)); ?>
		<?php echo $form->error($model,'categoryId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'unitPrice'); ?>
		<?php echo $form->textField($model,'unitPrice',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'unitPrice'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'costPrice'); ?>
		<?php echo $form->textField($model,'costPrice',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'costPrice'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'reorderLevel'); ?>
		<?php echo $form->textField($model,'reorderLevel'); ?>
		<?php echo $form->error($model,'reorderLevel'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'expiryDate'); ?>
		<?php echo $form->textField($model,'expiryDate'); ?>
		<?php echo $form->error($model,'expiryDate'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'isActive'); ?>
		<?php echo $form->textField($model,'isActive'); ?>
		<?php echo $form->error($model,'isActive'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'createdAt'); ?>
		<?php echo $form->textField($model,'createdAt'); ?>
		<?php echo $form->error($model,'createdAt'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'updatedAt'); ?>
		<?php echo $form->textField($model,'updatedAt'); ?>
		<?php echo $form->error($model,'updatedAt'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->