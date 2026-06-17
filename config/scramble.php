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
        'description' => '<h1> Advertising API Documentation!</h1>
            <h3>ERD</h3>
            <p>Below is the Entity Relationship Diagram (ERD) for the Advertising API, illustrating the relationships between different entities within the advertising system.</p>
            <img src="https://minio-console.uiii.ac.id/oss-dev/advertising/docAPI/ERD.png" alt="advertising ERD" style="max-width: 100%; height: auto;">
            <h3>Use Case & Activity Diagram</h3>
            <p>Below is the Diagrams for the Advertising API, illustrating how system work.</p>
            <img src="https://minio-console.uiii.ac.id/oss-dev/advertising/docAPI/diagram.png" alt="advertising Diagram System" style="max-width: 100%; height: auto;">
            <h3>Authentication</h3>
            <p>This API uses API Key authentication. You need to include your API key in the request headers as follows:</p>
            <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
            <h3>Versioning</h3>
            <p>The current version of the API is 1.0.0. Please refer to the versioning guidelines for any changes in future releases.</p>
            <h3>Contact Information</h3>
            <p>If you have any questions or need support, please contact the API support team at <a href="mailto:ict@uiii.ac.id">ict@uiii.ac.id</a>.</p>
            <h3>Development By</h3>
            <p><a href="https://github.com/gagahhwck" target="_blank">gagahhwck (Nur Alief Gagah Wicaksono)</a></p>
            <h3>This Project contain Websocket</h3>
            <table style="border-collapse: collapse; width: 100%;">
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align:center;">ID</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align:center;">KEY</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align:center;">Secret</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">advertising-uiii-id</td>
                    <td style="border: 1px solid #000; padding: 8px;">3c9255027fe9394491b1219831aad636e65769d6</td>
                    <td style="border: 1px solid #000; padding: 8px;">83f10c128f808261dd577b2966bed732664dad4c</td>
                </tr>
            </table>
            <table style="border-collapse: collapse; width: 100%;">
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align:center;">Event</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align:center;">Channels</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align:center;">Listen</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">Content</td>
                    <td style="border: 1px solid #000; padding: 8px;">contents</td>
                    <td style="border: 1px solid #000; padding: 8px;">ContentCreated, ContentUpdated</td>
                </tr>
            </table>
            <h3>Role on this Project</h3>
            <table style="border-collapse: collapse; width: 100%;">
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align:center; width: 150px;">Role Name</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align:center;">Permission</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">Ads Super Admin</td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        <ul>
                            <li>CRUD Event Category</li>
                            <li>CRUD Event</li>
                            <li>CRUD Template</li>
                            <li>CRUD Content</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">Ads Admin</td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        <ul>
                            <li>CRUD Event</li>
                            <li>CRUD Content</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">Ads Admin Media</td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        <ul>
                            <li>CRUD Template</li>
                            <li>CRUD Event</li>
                            <li>CRUD Content</li>
                        </ul>
                    </td>
                </tr>
            </table>
            <h3>Next.js / React.Js Integration (Frontend)</h3>
            <p>If your frontend is built with <strong>Next.js</strong>, follow these steps to receive realtime updates from this API via WebSockets (Laravel broadcasting):</p>
                <ol>
                        <li><strong>Install client packages</strong> in your Next.js app:
                                <pre><code>npm install laravel-echo pusher-js</code></pre>
                        </li>
                        <li>
                            <strong>Client initialization (Next.js)</strong> — only run on the client (use):
                            <code>useEffect
                            </code>
                        <pre>
                            <code>
                                import { useEffect } from "react";
                                import Echo from "laravel-echo";
                                import Pusher from "pusher-js";
                                if (typeof window !== "undefined") window.Pusher = Pusher;
                                export default function useContentsRealtime() {
                                    useEffect(() =&gt; {
                                        const echo = new Echo({
                                            broadcaster: "pusher",
                                            key: process.env.NEXT_PUBLIC_PUSHER_APP_KEY,
                                            cluster: process.env.NEXT_PUBLIC_PUSHER_APP_CLUSTER,
                                            wsHost: process.env.NEXT_PUBLIC_PUSHER_HOST || window.location.hostname,
                                            wsPort: process.env.NEXT_PUBLIC_PUSHER_PORT || 6001,
                                            forceTLS: process.env.NEXT_PUBLIC_PUSHER_SCHEME === "https",
                                            disableStats: true,
                                            auth: {
                                                headers: {
                                                    Authorization: `Bearer ${localStorage.getItem("api_token")}`,
                                                },
                                            },
                                        });

                                        echo.channel("contents").listen("ContentCreated", (e) =&gt; {
                                            console.log("content created", e);
                                        });

                                        or 

                                        echo.channel("contents").listen("ContentUpdated", e => {
                                        if (e.action === "deleted") {
                                            // hapus dari list
                                        } else if (e.action === "updated") {
                                            // update item di list
                                        }
                                        });

                                        return () =&gt; echo.disconnect();
                                    }, []);
                                }
                            </code>
                        </pre>
                        <p>Adjust channel name (`contents`) and event class name to match the server broadcast. If you use namespaced events (Laravel default), listen for the short name or the full class name emitted by the server.</p>
                    </li>
                    <li><strong>SSR considerations</strong>: initialize Echo only on the client. Do not run Pusher/Echo on the server-side. Use hooks or lazy-loaded components that run after hydration.</li>
                    <li><strong>Channel security</strong>: use <code>PrivateChannel</code> or <code>PresenceChannel</code> in Laravel and protect <code>/broadcasting/auth</code> to verify the logged-in user. For API token auth, configure Echo <code>auth.headers</code> to include the Authorization bearer token.</li>
                    <li><strong>Event payloads</strong>: the backend already broadcasts events with <code>action</code> and <code>data</code> keys; client code should inspect <code>e.action</code> to determine created/updated/deleted flows.</li>
                </ol>
        ',
    ],

    /*
     * Customize Stoplight Elements UI
     */
    'ui' => [
        /*
         * Define the title of the documentation's website. App name is used when this config is `null`.
         */
        'title' => 'Advertising API',

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
        'Development'   => 'https://api-dev.uiii.ac.id/ads',
        'live'    => 'https://api.uiii.ac.id/ads',
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
    $data['servers'] = ['Development' => 'https://api-dev.uiii.ac.id/ads'];
}

if (env("APP_ENV") == 'live') {
    $data['servers'] = ['live' => 'https://api.uiii.ac.id/ads'];
}

return $data;
