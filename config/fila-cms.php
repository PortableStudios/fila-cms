<?php

use Portable\FilaCms\Filament\FormBlocks\CheckboxBlock;
use Portable\FilaCms\Filament\FormBlocks\CheckboxListBlock;
use Portable\FilaCms\Filament\FormBlocks\ColumnBlock;
use Portable\FilaCms\Filament\FormBlocks\DateTimeInputBlock;
use Portable\FilaCms\Filament\FormBlocks\InformationBlock;
use Portable\FilaCms\Filament\FormBlocks\RadioBlock;
use Portable\FilaCms\Filament\FormBlocks\RelationshipBlock;
use Portable\FilaCms\Filament\FormBlocks\RichTextBlock;
use Portable\FilaCms\Filament\FormBlocks\SelectBlock;
use Portable\FilaCms\Filament\FormBlocks\TextAreaBlock;
use Portable\FilaCms\Filament\FormBlocks\TextInputBlock;

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
        \Portable\FilaCms\Plugins\FormsPlugin::class,
        \Portable\FilaCms\Plugins\MenusPlugin::class,
    ],
    'users' => [
        'profile_updater' => \Portable\FilaCms\Actions\Fortify\UpdateUserProfileInformation::class,
        // The fields that appear in the column listing, and on the edit page
        'default_columns' => ['name', 'email', 'roles'],
        'extra_fields' => [
            // Any additional fields you want to add to the user model editing screen
        ],
        'exclude_fields' => [
            // Any fields here you _don't_ want FilaCms to provide editing for
        ],
    ],
    'editor' => [
        'media_action' => \Portable\FilaCms\Filament\Actions\MediaAction::class,
    ],
    'forms' => [
        'blocks' => [
            InformationBlock::class,
            TextInputBlock::class,
            TextAreaBlock::class,
            DateTimeInputBlock::class,
            RadioBlock::class,
            CheckboxBlock::class,
            CheckboxListBlock::class,
            RichTextBlock::class,
            SelectBlock::class,
            RelationshipBlock::class,
            ColumnBlock::class,
        ]
    ],
    'media_library' => [
        'allow_root_uploads' => false,
        'thumbnails' => [
            'small' => [
                'width' => 100,
                'height' => 100,
                'fit' => 'crop',
            ],
            'medium' => [
                'width' => 250,
                'height' => 250,
                'fit' => 'crop',
            ],
            'large' => [
                'width' => 500,
                'height' => 500,
                'fit' => 'crop',
            ],
        ],
        'root_folders' => [
            [
                'name' => 'Images',
                'disk' => 'local',
            ],
            [
                'name' => 'Documents',
                'disk' => 'local',
            ],
            [
                'name' => 'Videos',
                'disk' => 'local',
            ]
        ]
    ],
    'short_url_prefix' => env('FILACMS_SHORT_URL_PREFIX', 's'),
];
