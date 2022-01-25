<?php

namespace Tenant\Auth\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Closure;

class TenantAuthMiddleware
{

    public function handle($request, Closure $next)
    {

        $publicKey  =   file_get_contents(config('tenant-auth.public_key_path'));

        if ($request->hasHeader('Authorization') &&
            !empty($request->header('Authorization')) &&
            !empty($request->header('x-user-uuid')) &&
            !empty($request->header('x-tenant-uuid')) &&
            !empty($request->header('x-tenant-url'))
        )
        {
            try {
                // Remove Bearer prefix
                $bearerToken    =   str_replace('Bearer ', '', $request->header('Authorization'));

                $decodedToken   =   JWT::decode($bearerToken, new Key($publicKey, 'RS256'));

                $response = $next($request);

                $response->headers->set('Accept', 'application/json');

                return $response;

            }
            catch (\Exception $exception)
            {
                // Throw exception if cannot decode token, expired and other issue
                throw new \Exception('Unauthorized', 401);
            }
        }
        else
        {
            throw new \Exception('Unauthorized', 401);
        }

    }
}