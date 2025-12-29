<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::app()->language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print">
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection">
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css">
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        body, html { margin:0; padding:0; width:100%; height:100%; }
        #page { width:100%; max-width:100%; margin:0; padding:0; }
        .container { width:100% !important; max-width:100% !important; margin:0 !important; padding:0 !important; }
        #header { width:100%; background:#333; color:white; padding:8px 0; position:sticky; top:0; z-index:1000; }
        #logo { width:100%; max-width:1200px; margin:0 auto; padding:0 15px; font-size:20px; font-weight:bold; line-height:1.2; }
        #mainmenu { width:100%; background:#6346ceff; border-bottom:1px solid #ddd; padding:0; }
        #mainmenu ul { width:100%; max-width:1200px; margin:0 auto; padding:0 15px; list-style:none; display:flex; height:45px; }
        #mainmenu li { margin:0; }
        #mainmenu a { display:flex; align-items:center; padding:0 15px; color:#fff; text-decoration:none; height:100%; font-size:14px; }
        #mainmenu a:hover { color:#eee; }
        .content-container { width:100%; max-width:1200px; margin:0 auto; padding:15px; }
        .clear { clear:both; }
        #footer { width:100%; background:#333; color:white; text-align:center; padding:15px 0; margin-top:20px; font-size:14px; }
        .language-switcher { float:right; margin:10px; }
        .language-switcher select { padding:4px; }
        .breadcrumbs { margin:8px 0 !important; padding:0 !important; }
        .menu-list { margin:0; padding:0; height:45px; }
    </style>
</head>

<body class="<?php echo isset($this->bodyClass) ? $this->bodyClass : ''; ?>">

<div id="page">
    <div id="header">
        <div id="logo"><?php echo CHtml::encode(Yii::app()->name); ?></div>
    </div>

    <div id="mainmenu" style="display:flex; justify-content:space-between; align-items:center; background:#6346ceff; border-bottom:1px solid #ddd; padding:0;">
    <!-- Left: Menu Items -->
    <div style="flex:1;">
        <?php $this->widget('zii.widgets.CMenu', array(
            'items' => array(
                array('label'=>Yii::t('app','Home'), 'url'=>array('/site/index')),
                array('label'=>Yii::t('app','Dashboard'), 'url'=>array('/site/dashboard'), 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>Yii::t('app','Profile'), 'url'=>array('/site/profile'), 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>Yii::t('app','Login'), 'url'=>array('auth/login'), 'visible'=>Yii::app()->user->isGuest),
                array('label'=>Yii::t('app','Logout').' ('.Yii::app()->user->name.')', 'url'=>array('/site/logout'), 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>Yii::t('app','Jobs'), 'url'=>array('/job/index')),
                array('label'=>Yii::t('app','Job Applications'), 'url'=>array('/jobApplication/index')),
            ),
            'htmlOptions' => array('class'=>'menu-list', 'style'=>'display:flex; list-style:none; margin:0; padding:0; height:45px;'),
            'itemCssClass'=>'menu-item',
        )); ?>
    </div>

    <!-- Right: Language Switcher -->
    <!-- <div style="margin-right:15px;">
        
    </div> -->
</div>


    <?php if(isset($this->breadcrumbs) && !empty($this->breadcrumbs)): ?>
        <div class="content-container">
            <?php $this->widget('zii.widgets.CBreadcrumbs', array(
                'links'=>$this->breadcrumbs,
                'htmlOptions'=>array('class'=>'breadcrumbs'),
            )); ?>
        </div>
    <?php endif; ?>

    <div class="content-container">
        <?php echo $content; ?>
    </div>

    <div class="clear"></div>

    <div id="footer">
        &copy; <?php echo date('Y'); ?> <?php echo CHtml::encode(Yii::app()->name); ?>. All Rights Reserved.<br/>
        <?php echo Yii::powered(); ?>
    </div>
</div>

</body>
</html>
