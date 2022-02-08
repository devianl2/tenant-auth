# Tenant Auth

Tenant auth is a laravel package that validate JWT token and its claim properties and set into request header

For each microservice development, you need to follow instruction below to ensure the application is standardize.

## Step 1:
Install from composer
```sh
composer require devianl2/tenant-auth
```

Run the following command for public key config
```sh
php artisan vendor:publish --provider="Tenant\Auth\TenantAuthProvider"
```

## Step 2:
To use this package, make sure any **API request** does have Authorization header and **Web request** does have Authorization key in cookie

Go to **App\Http\Middleware\Kernel** and add the following syntax
```sh
 protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        ...
        **\Tenant\Auth\Middleware\TenantAuthMiddleware::class**
    ];
```


### Note:
This package will automatic extract the following information if JWT token is valid:

* x-user-uuid (Current user's uuid)
* x-tenant-uuid (Current user's tenant id)
* x-tenant-url (Current user's tenant url)
* x-scopes (Current user's permissions / json encoded)
* x-roles (Current user's roles. E.g: admin/users. / Json encoded)
* x-modules (Module that user could access / json encoded)

You may use $request to extract the information in controller
E.g $request->header('x-user-uuid');
