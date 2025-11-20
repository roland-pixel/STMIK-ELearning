<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustProxies
{
    /**
     * The trusted proxies for this application.
     *
     * '*' artinya percaya semua proxy (Cloudflare Tunnel)
     */
    protected $proxies = '*';

    /**
     * The headers that should be trusted by the application.
     *
     * Gunakan Request::HEADER_X_FORWARDED_AWS_ELB atau kombinasi sesuai Laravel 12
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO;
}
