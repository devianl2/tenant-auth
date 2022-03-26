<?php

namespace Tenant\Auth\Traits;


use DateTimeImmutable;

trait TenantTrait
{
    protected DateTimeImmutable $exp;
    protected ?string $userUid  =   null;
    protected ?string$tenantUid  =   null;
    protected ?string $tenantUrl  =   null;
    protected array $userScopes  =   [];
    protected array $userRoles  =   [];
    protected array $modules  =   [];
    protected array $data =   [];

    public function setData($decodedToken)
    {
        $this->data =   $decodedToken;

        if (!empty($decodedToken['exp']))
        {
            $this->exp    =   $decodedToken['exp'];
        }

        if (!empty($decodedToken['tenantUrl']))
        {
            $this->tenantUrl    =   $decodedToken['tenantUrl'];
        }

        if (!empty($decodedToken['tenantUid']))
        {
            $this->tenantUid    =   $decodedToken['tenantUid'];
        }

        if (!empty($decodedToken['userUid']))
        {
            $this->userUid    =   $decodedToken['userUid'];
        }

        if (!empty($decodedToken['userScopes']))
        {
            $this->userScopes    =   $decodedToken['userScopes'];
        }

        if (!empty($decodedToken['userRoles']))
        {
            $this->userRoles    =   $decodedToken['userRoles'];
        }

        if (!empty($decodedToken['modules']))
        {
            $this->modules    =   $decodedToken['modules'];
        }
    }

    /**
     * @return string|null
     */
    public function getTenantUrl()
    {
        return $this->tenantUrl;
    }

    /**
     * @return string|null
     */
    public function getTenantUid()
    {
        return $this->tenantUid;
    }

    /**
     * @return string|null
     */
    public function getUserUid()
    {
        return $this->userUid;
    }

    /**
     * @return array
     */
    public function getUserScopes()
    {
        return $this->userScopes;
    }

    /**
     * @return array
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }

    /**
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check if the key is exist in decoded token
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        if (array_key_exists($key, $this->getData()))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Return from decoded token
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->getData()))
        {
            return $this->data[$key];
        }
        else
        {
            return null;
        }
    }
}
