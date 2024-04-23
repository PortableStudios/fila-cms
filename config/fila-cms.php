<?php

return [
    'use_admin_panel' => true,
    'admin_prefix' => 'admin',
    'publish_content_routes' => true,
    'admin_plugins' => [
        \Portable\FilaCms\Plugins\UsersPlugin::class,
        \Portable\FilaCms\Plugins\PermissionsPlugin::class,
        \Portable\FilaCms\Plugins\AuthorsPlugin::class,
        \Portable\FilaCms\Plugins\TaxonomyPlugin::class,
        \Portable\FilaCms\Plugins\PagesPlugin::class,
        \Portable\FilaCms\Plugins\NavigationsPlugin::class,
    ],
    'users' => [
        // The fields that appear in the column listing, and on the edit page
        'default_columns' => ['name', 'email', 'roles'],
        'extra_fields' => [
            // Any additional fields you want to add to the user model editing screen
        ],
        'exclude_fields' => [
            // Any fields here you _don't_ want FilaCms to provide editing for
        ],
    ],
];
