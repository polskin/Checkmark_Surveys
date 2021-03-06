<?php

namespace App\Models;

use App\Classes\Database;

abstract class Model
{
    protected static $db;
    protected static $query;

    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';

    public array $errors = [];

    public static function init()
    {
        self::$db = Database::getInstance();
    }


    public function loadData($data)
    {
        foreach($data as $key => $value)
        {
            if(property_exists($this, $key))
                $this->{$key} = $value;
        }
    }

    abstract function rules();

    public function validate()
    {
        foreach($this->rules() as $attribute => $rules)
        {
            $value = $this->{$attribute};
            foreach($rules as $rule)
            {
                $ruleName = $rule;
                if(is_array($value)) {
                    foreach($value as $v) {
                        if($ruleName === self::RULE_REQUIRED && !$v)
                            $this->addError($attribute, self::RULE_REQUIRED);

                        if($ruleName === self::RULE_EMAIL && !filter_var($v, FILTER_VALIDATE_EMAIL))
                            $this->addError($attribute, self::RULE_EMAIL);
                    }
                } else {
                    if(!is_string($ruleName))
                    $ruleName = $rule[0];
                
                if($ruleName === self::RULE_REQUIRED && !$value)
                    $this->addError($attribute, self::RULE_REQUIRED);

                if($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL))
                    $this->addError($attribute, self::RULE_EMAIL);
                
                if($ruleName === self::RULE_MIN && strlen($value) < $rule['min'])
                    $this->addError($attribute, self::RULE_MIN, $rule);
                
                if($ruleName === self::RULE_MAX && strlen($value) > $rule['max'])
                    $this->addError($attribute, self::RULE_MAX, $rule);
                
                }
                
            }
        }

        return empty($this->errors);
    }

    public function addError($attribute, $rule, $params = [])
    {
        $message = $this->errorMessages()[$rule] ?? $rule;
        foreach($params as $key => $value)
        {
            $message = str_replace("{{$key}}", $value, $message);
        }
        $this->errors[$attribute][] = $message;
    }

    public function errorMessages()
    {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL => 'This field must be valid email address',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MAX => 'Max length of this field must be {max}',
        ];
    }

}
Model::init();