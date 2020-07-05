<?php
/**
 * Created by 2020/7/5 0005 15:29
 * User: yuansai chen
 */

namespace App\Service\LoginApi;


interface LoginApi
{
    public function refreshAccessToken();

    public function code2Session();

    public function type();

    public function getUser();

    public function config();

    public function getQrCode($data);
}
