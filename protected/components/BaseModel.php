<?php
// protected/components/BaseModel.php

class BaseModel extends CActiveRecord
{
    /**
     * Generate ULID (Universally Unique Lexicographically Sortable Identifier)
     */
    protected function generateUlid()
    {
        $chars = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $time = (int)(microtime(true) * 1000);
        $timeChars = '';
        for ($i = 0; $i < 10; $i++) {
            $timeChars = $chars[$time % 32] . $timeChars;
            $time = intdiv($time, 32);
        }
        $randomChars = '';
        for ($i = 0; $i < 16; $i++) {
            $randomChars .= $chars[random_int(0, 31)];
        }
        return $timeChars . $randomChars;
    }

    /**
     * Before validate hook to auto-generate ULIDs
     */
    protected function beforeValidate()
    {
        if ($this->isNewRecord) {
            foreach ($this->ulidFields() as $field) {
                if (empty($this->$field)) {
                    $this->$field = $this->generateUlid();
                }
            }
        }
        return parent::beforeValidate();
    }

    /**
     * Define which fields should use ULID
     * Override in child models
     */
    protected function ulidFields()
    {
        return [];
    }
    
    /**
     * Get data for API responses
     */
    public function getApiData()
    {
        return $this->attributes;
    }
    
    /**
     * Get data for API responses with specific fields
     */
    public function getSafeApiData($fields = [])
    {
        $data = $this->attributes;
        
        if (!empty($fields)) {
            $filtered = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $filtered[$field] = $data[$field];
                }
            }
            return $filtered;
        }
        
        return $data;
    }
}