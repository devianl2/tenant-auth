<?php

namespace Tenant\Auth;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\UnauthorizedException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
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

//            $pubKey = file_get_contents(config('tenant-auth.public_key_path'));

            // Auto prompt error
            $token = (new Parser(new JoseEncoder()))->parse($bearerToken);
//            $token = (new Parser(new JoseEncoder()))->parse($token)->claims()->all();

            $validator  =   new Validator();

            if (!$validator->validate($token, new SignedWith(new Sha256(),
                    InMemory::file(config('tenant-auth.public_key_path')))
                ) || $token->isExpired(Carbon::now()))
            {
                throw new AuthorizationException('Invalid token');
            }

//            $decoded = JWT::decode($bearerToken, new Key($pubKey, 'RS256'));

            // Set data to setter
            $this->setData($token->claims()->all());

            return $this->getData();

        } catch(\Exception $e) {
            throw new AuthorizationException();
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
