<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'product-update-form',
    'enableAjaxValidation'=>true,
)); ?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <?php echo $form->errorSummary($model); ?>

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
        <?php
            echo $form->dropDownList(
                $model,
                'categoryId',
                CHtml::listData(Category::model()->findAll(), 'categoryId', 'name'),
                array('prompt'=>'Select Category')
            );
        ?>
        <?php echo $form->error($model,'categoryId'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'unitPrice'); ?>
        <?php echo $form->textField($model,'unitPrice'); ?>
        <?php echo $form->error($model,'unitPrice'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'costPrice'); ?>
        <?php echo $form->textField($model,'costPrice'); ?>
        <?php echo $form->error($model,'costPrice'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'description'); ?>
        <?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
        <?php echo $form->error($model,'description'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton('Update', array('class'=>'btn btn-primary')); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->
