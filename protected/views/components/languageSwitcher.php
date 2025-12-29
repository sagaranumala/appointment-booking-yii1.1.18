<?php
class LanguageSwitcher extends CWidget
{
    public $paramName = 'lang';

    public function run()
    {
        $currentLang = Yii::app()->language;
        $languages = Yii::app()->params['languages'] ?? [];

        if (empty($languages)) {
            echo "No languages defined";
            return;
        }
        
        // Get current URL without language parameter
        $currentUrl = Yii::app()->request->url;
        $request = Yii::app()->request;
        
        // Build form with all current GET parameters except language
        $getParams = $request->getQuery($this->paramName);
        $actionUrl = $request->url;
        
        ?>
        <form method="get" action="<?php echo $actionUrl; ?>" class="language-switcher-form" id="language-form">
            <?php 
            // Preserve all GET parameters except 'lang'
            foreach($_GET as $key => $value) {
                if ($key != $this->paramName && $key != 'language') {
                    if (is_array($value)) {
                        foreach($value as $val) {
                            echo '<input type="hidden" name="'.CHtml::encode($key).'[]" value="'.CHtml::encode($val).'">';
                        }
                    } else {
                        echo '<input type="hidden" name="'.CHtml::encode($key).'" value="'.CHtml::encode($value).'">';
                    }
                }
            }
            ?>
            <label for="lang-select" class="title"><?php echo Yii::t('app','Select Language'); ?>:</label>
            <select id="lang-select" name="<?php echo $this->paramName; ?>">
                <?php foreach ($languages as $code => $label): ?>
                    <option value="<?php echo CHtml::encode($code); ?>" <?php echo $currentLang === $code ? 'selected' : ''; ?>>
                        <?php echo CHtml::encode($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript>
                <button type="submit"><?php echo Yii::t('app', 'Change'); ?></button>
            </noscript>
        </form>
        <script>
            document.getElementById('lang-select').addEventListener('change', function() {
                document.getElementById('language-form').submit();
            });
        </script>
        <style>
            .title{color: white;}
            .language-switcher-form { display:inline-block; margin:0; }
            .language-switcher-form select { padding:4px; margin-left:5px; }
            .language-switcher-form button { padding:4px 8px; margin-left:5px; }
        </style>
        <?php
    }
}