<?php

class ServiceProvider extends BaseModel
{
    // Specify fields that should auto-generate ULID
    protected function ulidFields()
    {
        return ['providerUlid'];
    }

    public function tableName()
    {
        return 'serviceProviders';
    }

    public function rules()
{
    return [
        ['providerUlid, userUlid, categoryUlid', 'required'],
        ['experienceYears', 'numerical', 'integerOnly' => true],
        ['hourlyRate', 'numerical'],
    ];
}

    public function relations()
    {
        return [
            'category' => [self::BELONGS_TO, 'ServiceCategory', 'categoryUlid'],
            'user'     => [self::BELONGS_TO, 'User', 'userUlid'],
            'appointments' => [self::HAS_MANY, 'Appointment', 'providerUlid'],
        ];
    }

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

}
