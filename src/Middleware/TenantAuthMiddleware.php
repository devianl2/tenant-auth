<?php

namespace Tenant\Auth\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cookie;
use Lcobucci\JWT\Configuration;
use Illuminate\Support\Facades\Http;

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
            $bearerToken    =   str_replace('Bearer ', '', $token);
            $jwt = (Configuration::forUnsecuredSigner()->parser()->parse($bearerToken));
        } catch(\Exception $e) {
            throw new AuthorizationException();
        }

        // Check if jwt token is expired
        if ($jwt->isExpired(Carbon::now()))
        {
            throw new AuthorizationException();
        }

        /* check if we want to check both claim and value */
        if ($jwt->claims()->has('userUid') &&
            $jwt->claims()->has('tenantUid') &&
            $jwt->claims()->has('tenantUrl') &&
            $jwt->claims()->has('userScopes') &&
            $jwt->claims()->has('userRoles') &&
            $jwt->claims()->has('modules')
        ) {

            // Set header to the request
            $this->request->headers->set('x-user-uuid', $jwt->claims()->get('userUid'));
            $this->request->headers->set('x-tenant-uuid', $jwt->claims()->get('tenantUid'));
            $this->request->headers->set('x-tenant-url', $jwt->claims()->get('tenantUrl'));
            $this->request->headers->set('x-scopes', $jwt->claims()->has('userScopes'));
            $this->request->headers->set('x-roles', $jwt->claims()->has('userRoles'));
            $this->request->headers->set('x-modules', $jwt->claims()->has('modules'));

            $this->checkSelectedTenantId($jwt->claims()->get('tenantUid'), $bearerToken);

            return $this->request;
        }
        else
        {
            throw new AuthorizationException('Invalid claims');
        }
    }

    /**
     * @param string $tenantId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     * @throws AuthorizationException
     */
    private function checkSelectedTenantId($tenantId, $token)
    {
        if(!$this->request->has('tenantId'))
        {
            throw new AuthorizationException('Invalid tenant id');
        }
        else
        {

            $response = Http::withToken($token)
                ->post(config('tenant-auth.validate_tenant_gateway'), [
                    'tenantId' => $this->request->input('tenantId'),
            ]);

            if (!$response->successful())
            {
                throw new AuthorizationException('Invalid tenant id');
            }
        }
    }
}
