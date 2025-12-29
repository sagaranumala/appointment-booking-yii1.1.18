<?php

class ServiceCategory extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'serviceCategories';
    }

    protected function ulidFields()
    {
        return ['categoryUlid'];
    }

    public function rules()
    {
        return [
            ['categoryUlid, name', 'required'],
            ['name', 'length', 'max'=>255],
            ['status', 'numerical', 'integerOnly'=>true],
        ];
    }

    public function relations()
    {
        return [
            'providers' => [self::HAS_MANY, 'ServiceProvider', 'categoryUlid'],
        ];
    }
}
