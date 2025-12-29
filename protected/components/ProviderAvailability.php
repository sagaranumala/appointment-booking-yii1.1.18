<?php

class ProviderAvailability extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'providerAvailability';
    }

    public function rules()
    {
        return [
            ['availabilityUlid, providerUlid, dayOfWeek, startTime, endTime, slotDuration', 'required'],
            ['dayOfWeek, slotDuration', 'numerical', 'integerOnly'=>true],
        ];
    }
}
