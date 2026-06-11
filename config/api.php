<?php
return [
  'gw'          => env('API_GW_URL',        (env('APP_ENV') == 'local' ? 'https://api-service-uiii.test/api' : env('API_URL') . '/api')),
  'ais'         => env('API_AIS_URL',       (env('APP_ENV') == 'local' ? 'https://asset-be.test/api'         : env('API_URL') . '/ais')),
  'wa'          => env('API_WA_URL',        (env('APP_ENV') == 'local' ? 'https://wa-be.test/api'            : env('API_URL') . '/wa')),
  'lms'         => env('API_LMS_URL',       (env('APP_ENV') == 'local' ? 'https://logistic-be.test/api'      : env('API_URL') . '/lms')),
  'finsys'      => env('API_FINSYS_URL',    (env('APP_ENV') == 'local' ? 'https://finsys-be.test/api'        : env('API_URL') . '/finsys')),
];
