<?php
class ProviderAvailability extends BaseModel
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'provideravailability';
    }

     protected function ulidFields()
    {
        return ['appointmentUlid'];
    }

    public function rules()
    {
        return array(
            array('providerUlid, dayOfWeek, startTime, endTime', 'required'),
            array('dayOfWeek', 'numerical', 'integerOnly'=>true, 'min'=>0, 'max'=>6),
            array('isAllDayAvailable', 'boolean'),
            array('startTime, endTime', 'type', 'type'=>'time', 'message'=>'Invalid time format'),
            array('endTime', 'compare', 'compareAttribute'=>'startTime', 'operator'=>'>', 'message'=>'End time must be after start time'),
            array('status', 'numerical', 'integerOnly'=>true),
        );
    }

    public function relations()
    {
        return array(
            'provider' => array(self::BELONGS_TO, 'Provider', 'providerUlid'),
        );
    }

    public function getAvailableSlots($date, $duration = 60)
    {
        $dayOfWeek = date('w', strtotime($date));
        $availability = $this->findByAttributes(array(
            'providerUlid' => $this->providerUlid,
            'dayOfWeek' => $dayOfWeek,
            'status' => 1
        ));

        if (!$availability) return array();

        $slots = array();
        $start = strtotime($availability->startTime);
        $end = strtotime($availability->endTime);
        
        while ($start < $end) {
            $slotEnd = $start + ($duration * 60);
            if ($slotEnd <= $end) {
                $slots[] = date('H:i:s', $start);
            }
            $start += ($duration * 60);
        }

        return $slots;
    }
}