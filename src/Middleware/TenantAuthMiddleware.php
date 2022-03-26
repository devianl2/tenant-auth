<?php

namespace Tenant\Auth\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Tenant\Auth\Tenant;

class TenantAuthMiddleware
{

    public $request;

    public function handle($request, Closure $next)
    {

        $tenant =   new Tenant();
        $token  =   $tenant->getAuthorizationToken(true);
        $this->request  =   $request; // init

        if ($token)
        {
            try {

                // Decoded to array
                $tenant->tokenDecode($token);

                // Check if token is expired
                if ($tenant->isExpired())
                {
                    throw new AuthorizationException();
                }

                // Set essentials data to request header
                $this->setClaimsToRequest($tenant);

                // Validate token from gateway
                if (!$tenant->validateGatewayToken($tenant->getTenantUrl(), $token, $tenant->getTenantUid()))
                {
                    throw new AuthorizationException('Invalid tenant id');
                }

                return $next($this->request);

            }
            catch (\Exception $exception)
            {
                // Throw exception if cannot decode token, expired and other issue
                throw new \Exception('Unauthorized', 401);
            }
        }

        throw new \Exception('Unauthorized', 401);

    }

    /**
     * @param string $token
     * @return mixed
     * @throws AuthorizationException
     */
    private function setClaimsToRequest($tenant)
    {

        /* check if we want to check both claim and value */
        if ($tenant->has('userUid') &&
            $tenant->has('tenantUid') &&
            $tenant->has('tenantUrl') &&
            $tenant->has('userScopes') &&
            $tenant->has('userRoles') &&
            $tenant->has('modules')
        ) {
            // Set header to the request
            $this->request->headers->set('x-user-uuid', $tenant->get('userUid'));
            $this->request->headers->set('x-tenant-uuid', $tenant->get('tenantUid'));
            $this->request->headers->set('x-tenant-url', $tenant->get('tenantUrl'));
            $this->request->headers->set('x-scopes', $tenant->get('userScopes'));
            $this->request->headers->set('x-roles', $tenant->get('userRoles'));
            $this->request->headers->set('x-modules', $tenant->get('modules'));

            return $this->request;
        }
        else
        {
            throw new AuthorizationException('Invalid claims');
        }
    }
}
