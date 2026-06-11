<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Scramble::configure()
      ->withDocumentTransformers(function (OpenApi $openApi) {
        $openApi->components->securitySchemes['key'] = SecurityScheme::apiKey('header', 'key');
        $openApi->components->securitySchemes['bearer'] = SecurityScheme::http('bearer');

        $openApi->security[] = new SecurityRequirement([
          'key' => [],
          'bearer' => [],
        ]);
      });
  }
}
