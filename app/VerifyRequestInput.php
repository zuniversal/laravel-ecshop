<?php

namespace App;

use App\CodeResponse;
use App\Exceptions\BussniessException;
use App\Services\User\UserServices;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

// 6-12
trait VerifyRequestInput 
{
    public function verifyData($key, $default, $rule) {// 
        $value = request()->input($key, $default);
        $validator = Validator::make([
            $key => $value,
        ], [
            $key => $rule,
        ]);
        if (is_null($default) && is_null($value)) {
            return $value; 
        }
        if ($validator->fails()) {
            throw new BussniessException(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        return $value; 
    }
    public function verifyId($key, $default = null) {
        return $this->verifyData($key, $default, 'integer|digits_between:1,20'); 
    }
    public function verifyString($key, $default = null) {
        return $this->verifyData($key, $default, 'string'); 
    }
    public function verifyBoolean($key, $default = null) {
        return $this->verifyData($key, $default, 'boolean'); 
    }
    public function verifyInteger($key, $default = null) {
        return $this->verifyData($key, $default, 'integer'); 
    }
    public function verifyEnum($key, $default = null, $enum = []) {
        return $this->verifyData($key, $default, Rule::in($enum)); 
    }
    // 8-5
    public function verifyArrayNotEmpty($key, $default = null) {
        return $this->verifyData($key, $default, 'array|min:0'); 
        return $this->verifyData($key, $default, 'array|min:1'); 
    }
}
