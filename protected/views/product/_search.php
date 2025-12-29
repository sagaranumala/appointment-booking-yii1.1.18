<?php
/* @var $this ProductController */
/* @var $model Product */
/* @var $form CActiveForm */
?>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'productId'); ?>
		<?php echo $form->textField($model,'productId',array('size'=>26,'maxlength'=>26)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'sku'); ?>
		<?php echo $form->textField($model,'sku',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'categoryId'); ?>
		<?php echo $form->textField($model,'categoryId',array('size'=>26,'maxlength'=>26)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'unitPrice'); ?>
		<?php echo $form->textField($model,'unitPrice',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'costPrice'); ?>
		<?php echo $form->textField($model,'costPrice',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'reorderLevel'); ?>
		<?php echo $form->textField($model,'reorderLevel'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'expiryDate'); ?>
		<?php echo $form->textField($model,'expiryDate'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'isActive'); ?>
		<?php echo $form->textField($model,'isActive'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'createdAt'); ?>
		<?php echo $form->textField($model,'createdAt'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'updatedAt'); ?>
		<?php echo $form->textField($model,'updatedAt'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->