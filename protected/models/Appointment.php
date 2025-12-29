<?php

class Appointment extends BaseModel
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'appointments';
    }

     protected function ulidFields()
    {
        return ['appointmentUlid'];
    }

    public function rules()
    {
        // appointmentUlid
        return [
            [
                'appointmentUlid, customerUlid, providerUlid, categoryUlid, appointmentDate, startTime, endTime',
                'required'
            ],
            ['notes', 'safe'],
            ['status', 'in', 'range'=>['booked','cancelled','confirmed']],
            ['startTime', 'validateSlotAvailability'],
        ];
    }

    public function relations()
    {
        return [
            'provider' => [self::BELONGS_TO, 'ServiceProvider', 'providerUlid'],
            'customer' => [self::BELONGS_TO, 'User', 'customerUlid'],
        ];
    }

    /**
     * Prevent double booking
     */
    public function validateSlotAvailability($attribute, $params)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('providerUlid', $this->providerUlid);
        $criteria->compare('appointmentDate', $this->appointmentDate);
        $criteria->compare('startTime', $this->startTime);
        $criteria->compare('endTime', $this->endTime);
        $criteria->compare('status', 'booked');

        if (!$this->isNewRecord) {
            $criteria->addCondition('appointmentUlid != :ulid');
            $criteria->params[':ulid'] = $this->appointmentUlid;
        }

        if (self::model()->exists($criteria)) {
            $this->addError($attribute, 'This time slot is already booked.');
        }
    }
}
