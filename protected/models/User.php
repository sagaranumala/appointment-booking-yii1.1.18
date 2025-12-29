<?php
// protected/models/User.php

class User extends BaseModel
{
    public $status;
    public $two2FA;
    public $twosecret2FA;
    public $profilePicture;
    public $createdAt;
    public $updatedAt;

    public function relations()
    {
        return [
            'employee' => [self::HAS_ONE, 'Employee', 'userId'],
            'serviceProvider'  => [self::HAS_ONE, 'serviceProvider', 'userUlid'],
        ];
    }

    public function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        return [
            ['name, email, password', 'required'],
            ['name, email, password, phone, role, userId, profilePicture, twosecret2FA', 'length', 'max'=>255],
            ['email', 'email'],
            ['email', 'unique'],
            ['role', 'default', 'value'=>'user'],
            ['status', 'default', 'value'=>1],
            ['two2FA', 'default', 'value'=>0],
            ['createdAt, updatedAt', 'safe'],
        ];
    }

    // Specify fields that should auto-generate ULID
    protected function ulidFields()
    {
        return ['userId'];
    }

    protected function beforeSave()
    {
        if (!parent::beforeSave()) return false;

        // Hash password if not hashed
        if ($this->isNewRecord || strpos($this->password, '$2y$') !== 0) {
            $this->password = CPasswordHelper::hashPassword($this->password);
        }

        return true;
    }

    public function validatePassword($password)
    {
        return CPasswordHelper::verifyPassword($password, $this->password);
    }

    public function getApiData()
    {
        return [
            'userId'        => $this->userId,
            'name'          => $this->name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'role'          => $this->role,
            'status'        => $this->status,
            'two2FA'        => $this->two2FA,
            'twosecret2FA'  => $this->twosecret2FA,
            'profilePicture'=> $this->profilePicture,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
        ];
    }

    // Static model method — required by Yii
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
