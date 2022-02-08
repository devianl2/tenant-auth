<?php

namespace Tenant\Auth\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cookie;
use Lcobucci\JWT\Configuration;

class TenantAuthMiddleware
{

    public $request;

    public function handle($request, Closure $next)
    {

        $this->request  =   $request; // init

        if ($request->hasHeader('Authorization') &&
            !empty($request->header('Authorization'))
        )
        {
            try {

                $this->validateBearerToken($this->request->header('Authorization'));
                $this->checkClaims($this->request->header('Authorization'));

                return $next($this->request);

            }
            catch (\Exception $exception)
            {
                // Throw exception if cannot decode token, expired and other issue
                throw new \Exception('Unauthorized', 401);
            }
        }
        else
        {
            if (Cookie::has('Authorization'))
            {
                try {

                    $this->validateBearerToken(Cookie::get('Authorization'));
                    $this->checkClaims(Cookie::get('Authorization'));

                    return $next($this->request);

                }
                catch (\Exception $exception)
                {
                    // Throw exception if cannot decode token, expired and other issue
                    throw new \Exception('Unauthorized', 401);
                }

            }
        }

        throw new \Exception('Unauthorized', 401);

    }

    /**
     * @param string $token
     * @return mixed
     * @throws AuthorizationException
     */
    private function checkClaims($token)
    {
        try {
            $jwt = (Configuration::forUnsecuredSigner()->parser()->parse($token));
        } catch(\Exception $e) {
            throw new AuthorizationException();
        }

        /* check if we want to check both claim and value */
        if ($jwt->claims()->has('userId') &&
            $jwt->claims()->has('tenantId') &&
            $jwt->claims()->has('tenantUrl') &&
            $jwt->claims()->has('scopes') &&
            $jwt->claims()->has('roles')
        ) {


            $this->request->headers->set('x-user-uuid', $jwt->claims()->get('userId'));
            $this->request->headers->set('x-tenant-uuid', $jwt->claims()->get('tenantId'));
            $this->request->headers->set('x-tenant-url', $jwt->claims()->get('tenantUrl'));
            $this->request->headers->set('x-scopes', $jwt->claims()->has('scopes'));
            $this->request->headers->set('x-roles', $jwt->claims()->has('roles'));
            $this->request->headers->set('x-modules', $jwt->claims()->has('modules'));

            return $this->request;

        }
        else
        {
            throw new AuthorizationException('Invalid claims');
        }
    }

    /**
     * @param string $token
     * @return object
     */
    private function validateBearerToken($token)
    {
        $publicKey  =   file_get_contents(config('tenant-auth.public_key_path'));

        // Remove Bearer prefix
        $bearerToken    =   str_replace('Bearer ', '', $token);

        $decodedToken   =   JWT::decode($bearerToken, new Key($publicKey, 'RS256'));

        return $decodedToken;
    }
}