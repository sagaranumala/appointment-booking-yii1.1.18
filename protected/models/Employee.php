<?php

class Employee extends BaseModel
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'employee';
    }

    /**
     * Soft delete default scope
     */
    public function defaultScope()
    {
        return [
            'condition' => 't.status = 1'
        ];
    }

    public function rules()
    {
        return [
            ['userId, departmentId, designation', 'required'],
            ['userId', 'length', 'max' => 26],
            ['designation, profilePicture', 'length', 'max' => 255],
            ['status', 'numerical', 'integerOnly' => true],
        ];
    }

    public function relations()
    {
        return [
            'user' => [self::BELONGS_TO, 'User', 'userId'],
            'department' => [self::BELONGS_TO, 'Department', 'departmentId'],
            'address' => [
                self::HAS_ONE,
                'Address',
                ['userId' => 'userId'], // explicitly map Employee.userId -> Address.userId
            ],

        ];
    }

    /**
     * Tell BaseModel which fields need ULIDs
     */
    protected function ulidFields()
    {
        return ['employeeId'];
    }

    /**
     * Soft delete helper
     */
    public function deactivate()
    {
        $this->status = 0;
        return $this->save(false, ['status']);
    }
}
