<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Role Permission Matrix
    |--------------------------------------------------------------------------
    |
    | Define which permission groups can be assigned to which roles.
    | This creates a security boundary preventing unauthorized permission escalation.
    |
    | Format:
    |   'RoleName' => [
    |       'allowed_groups' => ['Group Pattern 1', 'Group Pattern 2'],
    |       'blocked_groups' => ['Blocked Pattern'],  // optional
    |       'allowed_specific' => ['specific.permission.name'],  // optional
    |       'blocked_specific' => ['specific.permission.name'],  // optional
    |       'description' => 'Human readable description'
    |   ]
    |
    | Patterns support wildcards:
    |   - 'Admin - *' matches all groups starting with 'Admin - '
    |   - '*' matches all groups
    |   - Exact match: 'User - Dashboard & Profile'
    |
    */

    'role_permission_matrix' => [
        'Admin' => [
            'allowed_groups' => ['*'],  // Admin can have all permissions
            'description' => 'Administrator dengan akses penuh ke semua fitur sistem'
        ],

        'Operator-Vidcon' => [
            'allowed_groups' => [
                'Operator',
                'User - *',  // All user groups
            ],
            'blocked_groups' => [
                'Admin - *',  // Cannot have admin permissions
            ],
            'description' => 'Operator Video Conference dengan akses ke fitur operator dan layanan user'
        ],

        'Operator-Sandi' => [
            'allowed_groups' => [
                'Operator',
                'User - *',
                'Admin - TTE (Tanda Tangan Elektronik)',
                'Admin - Subdomain IP Change',
            ],
            'allowed_specific' => [
                'admin.vpn.registration.index',
                'admin.vpn.registration.show',
                'admin.vpn.registration.update-status',
                'admin.vpn.reset.index',
                'admin.vpn.reset.show',
                'admin.vpn.reset.update-status',
            ],
            'blocked_groups' => [
                'Admin - Dashboard & User Management',
                'Admin - Role Management',
                'Admin - Layanan Digital',
                'Admin - Video Conference',
                'Admin - TIK & Inventaris',
            ],
            'description' => 'Operator Keamanan Sandi dengan akses ke TTE, VPN, dan perubahan IP'
        ],

        'User' => [
            'allowed_groups' => [
                'User - *',  // All user permission groups
            ],
            'blocked_groups' => [
                'Admin - *',
                'Operator',
            ],
            'description' => 'User biasa dengan akses ke semua layanan user'
        ],

        'User-OPD' => [
            'allowed_groups' => [
                'User - *',  // All user permission groups
            ],
            'blocked_groups' => [
                'Admin - *',
                'Operator',
            ],
            'description' => 'Pengelola TIK OPD dengan akses lengkap ke layanan user untuk mengelola aplikasi dan infrastruktur OPD'
        ],

        'User-Individual' => [
            'allowed_groups' => [
                'User - Dashboard & Profile',
            ],
            'allowed_specific' => [
                'user.email.index',
                'user.email.create',
                'user.email.show',
                'user.rekomendasi.index',
                'user.rekomendasi.create',
            ],
            'blocked_groups' => [
                'Admin - *',
                'Operator',
                'User - Subdomain IP Change',
            ],
            'blocked_specific' => [
                'user.subdomain.index',
                'user.subdomain.create',
                'user.subdomain.show',
                'user.subdomain.ip-change.*',
            ],
            'description' => 'User perorangan (ASN/pegawai) dengan akses terbatas untuk keperluan pribadi'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Group Hierarchy
    |--------------------------------------------------------------------------
    |
    | Define the hierarchy and relationships between permission groups.
    | This helps in organizing the permission display in the UI.
    |
    */
    'group_hierarchy' => [
        'admin' => [
            'Admin - Dashboard & User Management',
            'Admin - Layanan Digital',
            'Admin - Role Management',
            'Admin - Subdomain IP Change',
            'Admin - Video Conference',
            'Admin - TTE (Tanda Tangan Elektronik)',
            'Admin - TIK & Inventaris',
        ],
        'user' => [
            'User - Dashboard & Profile',
            'User - Layanan Digital',
            'User - Subdomain IP Change',
        ],
        'operator' => [
            'Operator',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Group Display Settings
    |--------------------------------------------------------------------------
    |
    | Customize how permission groups are displayed in the UI
    |
    */
    'group_display' => [
        'Admin - Dashboard & User Management' => [
            'icon' => 'dashboard',
            'color' => 'purple',
            'order' => 1,
        ],
        'Admin - Layanan Digital' => [
            'icon' => 'cloud',
            'color' => 'blue',
            'order' => 2,
        ],
        'Admin - Role Management' => [
            'icon' => 'users-cog',
            'color' => 'indigo',
            'order' => 3,
        ],
        'Admin - Subdomain IP Change' => [
            'icon' => 'network-wired',
            'color' => 'teal',
            'order' => 4,
        ],
        'Admin - Video Conference' => [
            'icon' => 'video',
            'color' => 'green',
            'order' => 5,
        ],
        'Admin - TTE (Tanda Tangan Elektronik)' => [
            'icon' => 'file-signature',
            'color' => 'yellow',
            'order' => 6,
        ],
        'Admin - TIK & Inventaris' => [
            'icon' => 'server',
            'color' => 'gray',
            'order' => 7,
        ],
        'User - Dashboard & Profile' => [
            'icon' => 'user',
            'color' => 'blue',
            'order' => 10,
        ],
        'User - Layanan Digital' => [
            'icon' => 'cloud-upload',
            'color' => 'cyan',
            'order' => 11,
        ],
        'User - Subdomain IP Change' => [
            'icon' => 'exchange-alt',
            'color' => 'teal',
            'order' => 12,
        ],
        'Operator' => [
            'icon' => 'headset',
            'color' => 'green',
            'order' => 20,
        ],
    ],
];
