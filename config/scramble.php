<?php

use App\Http\Middleware\RestrictedDocsAccess;

$data = [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
     */
    'api_path' => env('API_PATH', 'api'),

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * matcher, so when implementing your own, make sure you use this config if needed.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info' => [
        /*
         * API version.
         */
        'version' => env('API_VERSION', '2.0.0'),

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => '<h1> Ticketing API Documentation!</h1>
          <h3>ERD</h3>
          <p>Below is the Entity Relationship Diagram (ERD) for the Ticketing API, illustrating the relationships between different entities within the ticketing system.</p>
          <img src="'. env('AWS_ENDPOINT') . '/oss-dev/Ticketing/erd/v1.png" alt="Ticketing ERD" style="max-width: 100%; height: auto;">
          <h3>Authentication</h3>
          <p>This API uses API Key authentication. You need to include your API key in the request headers as follows:</p>
          <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
          <h3>Versioning</h3>
          <p>The current version of the API is 2.0.0. Please refer to the versioning guidelines for any changes in future releases.</p>
          <h3>Contact Information</h3>
          <p>If you have any questions or need support, please contact the API support team at <a href="mailto:ict@uiii.ac.id">ict@uiii.ac.id</a>.</p>
        ',
    ],

    /*
     * Customize Stoplight Elements UI
     */
    'ui' => [
        /*
         * Define the title of the documentation's website. App name is used when this config is `null`.
         */
        'title' => 'ticketing API',

        /*
         * Define the theme of the documentation. Available options are `light`, `dark`, and `system`.
         */
        'theme' => 'light',

        /*
         * Hide the `Try It` feature. Enabled by default.
         */
        'hide_try_it' => false,

        /*
         * Hide the schemas in the Table of Contents. Enabled by default.
         */
        'hide_schemas' => false,

        /*
         * URL to an image that displays as a small square logo next to the title, above the table of contents.
         */
        'logo' => 'https://sso.uiii.ac.id/logo/128x128.png',

        /*
         * Use to fetch the credential policy for the Try It feature. Options are: omit, include (default), and same-origin
         */
        'try_it_credentials_policy' => 'include',

        /*
         * There are three layouts for Elements:
         * - sidebar - (Elements default) Three-column design with a sidebar that can be resized.
         * - responsive - Like sidebar, except at small screen sizes it collapses the sidebar into a drawer that can be toggled open.
         * - stacked - Everything in a single column, making integrations with existing websites that have their own sidebar or other columns already.
         */
        'layout' => 'responsive',
    ],

    /*
     * The list of servers of the API. By default, when `null`, server URL will be created from
     * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
     * will need to specify the local server URL manually (if needed).
     *
     * Example of non-default config (final URLs are generated using Laravel `url` helper):
     *
     * ```php
     * 'servers' => [
     *     'Live' => 'api',
     *     'Prod' => 'https://scramble.dedoc.co/api',
     * ],
     * ```
     */
    'servers' => [
        'Local'         => 'api',
        'Development'   => 'https://api-dev.uiii.ac.id/ticketing',
        'live'    => 'https://api.uiii.ac.id/ticketing',
    ],

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     * Available options:
     * - 'description' – Case descriptions are stored as the enum schema's description using table formatting.
     * - 'extension' – Case descriptions are stored in the `x-enumDescriptions` enum schema extension.
     *
     *    @see https://redocly.com/docs-legacy/api-reference-docs/specification-extensions/x-enum-descriptions
     * - false - Case descriptions are ignored.
     */
    'enum_cases_description_strategy' => 'description',

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],
];

if (env("APP_ENV") == 'local') {
    $data['servers'] = ['Local' => 'api'] + $data['servers'];
}

if (env("APP_ENV") == 'development') {
    $data['servers'] = ['Development' => 'https://api-dev.uiii.ac.id/ticketing'];
}

if (env("APP_ENV") == 'live') {
    $data['servers'] = ['live' => 'https://api.uiii.ac.id/ticketing'];
}

return $data;
