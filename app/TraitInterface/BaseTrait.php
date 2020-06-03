<?php

namespace App\TraitInterface;

use App;

trait BaseTrait
{
    public function successJson($data, $message = '')
    {
        return response()->json(['message' => $message, 'code' => 1, 'data' => $data], 200);
    }

    public function isTest()
    {
        return App::environment() == 'testing';
    }

    public function errorJson($message, $code = 2, $data = [], $status = 200)
    {
        if (!empty($data)) {
            $message = $this->validatorMessage($data);
        }
        return response()->json(['message' => $message, 'code' => $code, 'data' => $data], $status);
    }

    public function validatorMessage($array)
    {
        if (is_array($array)) {
            $msg = '';
            foreach ($array as $su) {
                if (is_array($su)) {
                    $msg .= $this->validatorMessage($su);
                } else {
                    $msg .= $su;
                }
            }
            return $msg;
        }
        return $array;
    }

}
