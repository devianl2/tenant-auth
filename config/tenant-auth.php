<?php

return [
    // Do not append domain. Path should come with slash in prefix. E.g /api
    'gateway_url'    =>  [
        'validate_tenant'   =>   '/api/system/tenant/validate', // compulsory
        'tenant_list'   =>   '/api/system/tenant/list',
        'user_list'   =>   '/api/users/list',
        'user_detail'   =>  '/api/users/detail/{uuid}',
        // Add more API below
    ],
    'public_key_path'   =>  config('filesystems.disk.local.root').'/public.key'
];
