<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 18:24
 */
namespace App\Service\Auth;

use Illuminate\Auth\TokenGuard;
class JwtGuard extends TokenGuard
{
    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {

        $token = $this->request->query($this->inputKey);

        if (empty($token)) {
            $token = $this->request->header($this->inputKey);
        }
        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        if (empty($token)) {
            $token = $this->request->getPassword();
        }
        return $token;
    }
}
