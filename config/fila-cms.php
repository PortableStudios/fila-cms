<?php

use App\Actions\Fortify\ResetUserPassword;
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
    'admin_dark_mode' => true,
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
        \Portable\FilaCms\Plugins\LinkChecksPlugin::class,
    ],
    'sso' => [
        'providers' => ['google','facebook','linkedin']
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
        'tools' => [
            'heading', 'bullet-list', 'ordered-list', 'checked-list', 'blockquote', 'hr', '|',
            'bold', 'italic', 'strike', 'underline', 'superscript', 'subscript', 'align-left', 'align-center', 'align-right', '|',
            'link', 'media', 'oembed', 'table', 'grid-builder', '|', 'code', 'code-block', 'source', 'blocks',
        ],
    ],
    'purify' => [
        'Core.Encoding' => 'utf-8',
        'HTML.Doctype' => 'XHTML 1.0 Transitional',
        // phpcs:ignore
        'HTML.Allowed' => 'h1,h2,h3,h4,h5,h6,b,strong,i,em,s,del,a[href|title],ul,ol,li,p,br,span,img[width|height|alt|src],blockquote,iframe[src|width|height|frameborder]',
        'HTML.SafeIframe' => 'true',
        'URI.SafeIframeRegexp' => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%',
        'HTML.ForbiddenElements' => '',
        'CSS.AllowedProperties' => '',
        'AutoFormat.AutoParagraph' => false,
        'AutoFormat.RemoveEmpty' => false,
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
    'auth' => [
        'force_2fa' => false,
        'forgot_password_view' => 'fila-cms::auth.forgot-password',
        'password_reset' => ResetUserPassword::class,
        'password_reset_view' => 'fila-cms::auth.reset-password',
    ],
    'tip_tap_blocks' => [],
];
