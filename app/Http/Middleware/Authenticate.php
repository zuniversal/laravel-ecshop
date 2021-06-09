<?php

namespace App\Http\Middleware;

use App\CodeResponse;
use App\Exceptions\BussniessException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    // 5-13
    public function unauthenticated($request, array $guards) {// 
        if ($request->expectsJson() || in_array('wx', $guards)) {
            throw new BussniessException(CodeResponse::UN_LOGIN);
        }
        parent::unauthenticated($request, $guards);
        
    }
}
