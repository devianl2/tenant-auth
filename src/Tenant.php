<?php

namespace Tenant\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Tenant\Auth\Traits\TenantTrait;

class Tenant
{
    use TenantTrait;

    /**
     * Get authorization header from request
     * @param false $stripBearer
     * @return string|null
     */
    public function getAuthorizationToken($stripBearer = false)
    {
        $request    =   new Request();
        $token  =   null;

        if ($request->hasHeader('Authorization') &&
            !empty($request->header('Authorization'))
        )
        {
            $token  =   $request->header('Authorization');

            if ($stripBearer)
            {
                $token    =   str_replace('Bearer ', '', $token);
            }
        }
        else
        {
            if (Cookie::has('Authorization'))
            {
                $token  =   Cookie::get('Authorization');

                if ($stripBearer)
                {
                    $token    =   str_replace('Bearer ', '', $token);
                }
            }
        }

        return $token;
    }

    /**
     * Get decoded token information
     * @param $token
     * @return array
     * @throws AuthorizationException
     */
    public function tokenDecode($token)
    {
        try {
            $bearerToken    =   str_replace('Bearer ', '', $token);

            $pubKey = file_get_contents(config('tenant-auth.public_key_path'));

            $decoded = JWT::decode($bearerToken, new Key($pubKey, 'RS256'));

            // Set data to setter
            $this->setData(json_decode(json_encode($decoded), true));

            return $this->getData();

        } catch(\Exception $e) {
            throw new AuthorizationException();
        }
    }

    /**
     * Check if token is expired
     * @param int $timestamp
     * @return bool
     */
    public function isExpired($timestamp)
    {
        if (!empty($this->exp) && $this->exp > $timestamp)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Validate token through gateway
     * @param $gatewayDomain
     * @param $token
     * @param $tenantId
     * @throws AuthorizationException
     */
    public function validateGatewayToken($gatewayDomain, $token, $tenantId)
    {

        $response = Http::withToken($token)
            ->post($gatewayDomain.config('tenant-auth.gateway_url.validate_tenant'), [
                'tenantId' => $tenantId,
            ]);

        if (!$response->successful())
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}
