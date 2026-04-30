<?php

declare(strict_types=1);

use TypiCMS\Modules\Core\Models\Page;

return [
    /*
     |--------------------------------------------------------------------------
     | Default Social Share image
     |--------------------------------------------------------------------------
     */
    'og_image' => 'resources/images/og-image.png',

    /*
     |--------------------------------------------------------------------------
     | Locales
     |--------------------------------------------------------------------------
     |
     | List of available locales for your website, the first key of this array
     | is the value returned by the mainLocale() helper.
     |
     */
    'locales' => [
        'en' => 'en_US',
        'fr' => 'fr_FR',
        'nl' => 'nl_NL',
    ],

    /*
     |--------------------------------------------------------------------------
     | You can choose not to have the main locale in URLs.
     |--------------------------------------------------------------------------
     |
     | If set to false, the main locale, the first of the locales array
     | will not appear in URLs.
     |
     */
    'main_locale_in_url' => true,

    /*
     |--------------------------------------------------------------------------
     | You can have a lang chooser at the root url.
     |--------------------------------------------------------------------------
     |
     | If set to false, the root URL will redirect to the browser locale
     | or the main locale (the first in the above array). If set to true,
     | main_locale_in_url must be true.
     |
     */
    'lang_chooser' => false,

    /*
     |--------------------------------------------------------------------------
     | Max file upload size allowed
     |--------------------------------------------------------------------------
     */
    'max_file_upload_size' => env('MAX_FILE_UPLOAD_SIZE', 61440),

    /*
     |--------------------------------------------------------------------------
     | Welcome message URL for the admin’s dashboard
     |--------------------------------------------------------------------------
     */
    'welcome_message_url' => env('WELCOME_MESSAGE_URL'),

    /*
     |--------------------------------------------------------------------------
     | Folder to find template files for pages.
     | This folder is a subfolder of /resources/views/vendor/pages
     |--------------------------------------------------------------------------
     */
    'template_dir' => 'public',

    /*
     |--------------------------------------------------------------------------
     | The following IP’s can visit the website without
     | login when the website is protected.
     |--------------------------------------------------------------------------
     */
    'authorized_ips' => [
        // '127.0.0.1',
    ],

    /*
     |--------------------------------------------------------------------------
     | Search engine configuration.
     |--------------------------------------------------------------------------
     */
    'search' => [
        'linkable_to_page' => true,
        'pages' => [
            'model' => Page::class,
            'columns' => [
                'title',
                'body',
            ],
        ],
        // 'news' => [
        //     'model' => News::class,
        //     'columns' => [
        //         'title',
        //         'body',
        //     ],
        // ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Modules configuration.
     |--------------------------------------------------------------------------
     */
    'modules' => [
        'news' => [
            // 'per_page' => 30
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | User registration
     |   - allowed: true to allow registration, false to disable it.
     |   - role: the role assigned to new users.
     |   - activated: true to activate the user account immediately,
     |     false to require activation by an administrator.
     |--------------------------------------------------------------------------
     */
    'registration' => [
        'allowed' => env('TYPICMS_REGISTRATION_ALLOWED', false),
        'role' => env('TYPICMS_REGISTRATION_ROLE'),
        'activated' => env('TYPICMS_REGISTRATION_ACTIVATED', false),
    ],

    /*
     |--------------------------------------------------------------------------
     | Send the x-powered-by: TypiCMS Header.
     |--------------------------------------------------------------------------
     */
    'send_powered_by_header' => true,
];
