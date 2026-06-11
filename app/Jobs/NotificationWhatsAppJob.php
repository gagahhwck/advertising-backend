<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $to, $text;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $text)
    {
        $this->to = $to;
        $this->text = $text;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $number = preg_replace('/\D/', '', $this->to);

            // Validate WhatsApp key
            if (empty(config('setting.whatsapp_key'))) {
                throw new \Exception('WhatsApp key is not configured. Please set WHATSAPP_KEY in .env file');
            }

            // Validate phone number
            if (empty($number)) {
                throw new \Exception('Invalid phone number: ' . $this->to);
            }

            // Get and validate WhatsApp server URL
            $waServer = config('setting.wa_server');
            if (empty($waServer)) {
                $waServer = 'http://192.168.74.68:5001'; // fallback default
                Log::warning('WhatsApp server URL not configured, using fallback', ['fallback' => $waServer]);
            }

            // Validate URL format
            if (!filter_var($waServer, FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid WhatsApp server URL format: ' . $waServer);
            }

            // Build the complete URL dengan validasi
            $url = rtrim($waServer, '/') . '/message/send-text';

            // Validate final URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid final URL generated: ' . $url);
            }

            // Additional URL validation - check if it contains valid scheme and host
            $parsedUrl = parse_url($url);
            if (!$parsedUrl || empty($parsedUrl['scheme']) || empty($parsedUrl['host'])) {
                throw new \Exception('Malformed URL detected: ' . $url . ' - Parsed: ' . json_encode($parsedUrl));
            }

            // Validate IP address format
            if (!filter_var($parsedUrl['host'], FILTER_VALIDATE_IP)) {
                throw new \Exception('Invalid IP address in URL: ' . $parsedUrl['host']);
            }

            Log::info('WhatsApp API attempt', [
                'url' => $url,
                'to' => $number,
                'server' => $waServer,
                'has_key' => !empty(config('setting.whatsapp_key')),
                'device' => config('setting.whatsapp_device'),
                'parsed_url' => $parsedUrl,
                'force_curl' => config('setting.whatsapp_force_curl') == 'TRUE'
            ]);

            // Check if we should force cURL usage to avoid Laravel HTTP client issues
            if (config('setting.whatsapp_force_curl') == 'TRUE') {
                Log::info('Using cURL directly due to WHATSAPP_FORCE_CURL setting');
                $success = $this->sendWithCurl($number, $waServer);
                if ($success) {
                    Log::info('WhatsApp sent successfully via forced cURL', ['to' => $number]);
                    return;
                } else {
                    throw new \Exception('Forced cURL method failed');
                }
            }

            // Create a fresh URL for HTTP request to avoid any potential memory corruption
            $requestUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . ':' . $parsedUrl['port'] . $parsedUrl['path'];

            // Double-check the URL before making the request
            if ($requestUrl !== $url) {
                Log::warning('URL mismatch detected, using reconstructed URL', [
                    'original' => $url,
                    'reconstructed' => $requestUrl
                ]);
            }

            $response = Http::timeout(30)
                ->withoutVerifying()
                ->retry(3, 100) // Retry 3 times with 100ms delay
                ->withOptions([
                    'verify' => false,
                    'http_errors' => false,
                    'connect_timeout' => 10,
                    'timeout' => 30,
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Force IPv4
                        CURLOPT_DNS_CACHE_TIMEOUT => 60,
                    ]
                ])
                ->withHeaders([
                    'key' => config('setting.whatsapp_key'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-ticketing/1.0'
                ])
                ->post($requestUrl, [
                'session'   => config('setting.whatsapp_device'),
                'to'        => $number,
                'text'      => $this->text
            ]);

            if ($response->status() == 200) {
                DB::connection('sso')->table('whatsapp_notifications')->insert([
                    'status'    => true,
                    'session'   => config('setting.whatsapp_device'),
                    'to'        => $number,
                    'text'      => $this->text,
                    'response'  => json_encode($response->json()),
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
                Log::info('WhatsApp sent successfully', ['to' => $number]);
            } else {
                DB::connection('sso')->table('whatsapp_notifications')->insert([
                    'status'    => false,
                    'session'   => config('setting.whatsapp_device'),
                    'to'        => $number,
                    'text'      => $this->text,
                    'response'  => json_encode($response->json()),
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
                Log::error('WhatsApp failed', ['to' => $number, 'status' => $response->status(), 'response' => $response->body()]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error('WhatsApp job failed with Laravel Http', [
                'to' => $this->to,
                'error' => $errorMessage,
                'url' => $requestUrl ?? $url ?? 'unknown',
                'server' => $waServer ?? 'unknown'
            ]);

            // Check for DNS resolution issues
            $isDnsError = (
                strpos($errorMessage, 'Could not resolve host') !== false ||
                strpos($errorMessage, 'Name or service not known') !== false ||
                strpos($errorMessage, 'getaddrinfo failed') !== false ||
                strpos($errorMessage, 'Temporary failure in name resolution') !== false
            );

            // Immediate fallback: Try with native cURL if Laravel Http fails with DNS errors
            if ($isDnsError) {
                Log::info('DNS resolution error detected, attempting WhatsApp with cURL fallback', [
                    'original_error' => $errorMessage,
                    'to' => $number ?? $this->to,
                    'server' => $waServer ?? 'unknown'
                ]);

                try {
                    $success = $this->sendWithCurl($number, $waServer);
                    if ($success) {
                        Log::info('WhatsApp sent successfully via cURL fallback', ['to' => $number]);
                        return;
                    }
                } catch (\Exception $curlException) {
                    Log::error('cURL fallback also failed', [
                        'error' => $curlException->getMessage(),
                        'original_error' => $errorMessage,
                        'to' => $number ?? $this->to
                    ]);
                }
            } else {
                // For non-DNS errors, try cURL as well
                Log::info('Non-DNS error detected, attempting cURL fallback anyway', [
                    'original_error' => $errorMessage,
                    'to' => $number ?? $this->to,
                    'server' => $waServer ?? 'unknown'
                ]);

                try {
                    $success = $this->sendWithCurl($number, $waServer);
                    if ($success) {
                        Log::info('WhatsApp sent successfully via cURL fallback (non-DNS error)', ['to' => $number]);
                        return;
                    }
                } catch (\Exception $curlException) {
                    Log::error('cURL fallback failed for non-DNS error', [
                        'error' => $curlException->getMessage(),
                        'original_error' => $errorMessage,
                        'to' => $number ?? $this->to
                    ]);
                }
            }

            // Log failed attempt to database
            DB::connection('sso')->table('whatsapp_notifications')->insert([
                'status'    => false,
                'session'   => config('setting.whatsapp_device'),
                'to'        => preg_replace('/\D/', '', $this->to),
                'text'      => $this->text,
                'response'  => json_encode(['error' => $e->getMessage()]),
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);

            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Fallback method using native cURL
     */
    private function sendWithCurl($number, $waServer)
    {
        $url = rtrim($waServer, '/') . '/message/send-text';

        // Additional validation for cURL fallback
        $parsedUrl = parse_url($url);
        if (!$parsedUrl || empty($parsedUrl['host'])) {
            throw new \Exception('Invalid URL for cURL fallback: ' . $url);
        }

        $data = json_encode([
            'session' => config('setting.whatsapp_device'),
            'to' => $number,
            'text' => $this->text
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Force IPv4
            CURLOPT_DNS_CACHE_TIMEOUT => 60,
            CURLOPT_FRESH_CONNECT => true, // Force new connection
            CURLOPT_FORBID_REUSE => true, // Don't reuse connection
            CURLOPT_VERBOSE => false,
            CURLOPT_HTTPHEADER => [
                'key: ' . config('setting.whatsapp_key'),
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: Laravel-ticketing-cURL/1.0'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);

        if ($error) {
            Log::error('cURL execution error', [
                'error' => $error,
                'url' => $url,
                'curl_info' => $curlInfo,
                'to' => $number
            ]);
            throw new \Exception('cURL error: ' . $error);
        }

        if ($httpCode == 200) {
            // Log success to database
            DB::connection('sso')->table('whatsapp_notifications')->insert([
                'status' => true,
                'session' => config('setting.whatsapp_device'),
                'to' => $number,
                'text' => $this->text,
                'response' => $response,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return true;
        } else {
            // Log failure to database
            DB::connection('sso')->table('whatsapp_notifications')->insert([
                'status' => false,
                'session' => config('setting.whatsapp_device'),
                'to' => $number,
                'text' => $this->text,
                'response' => json_encode(['http_code' => $httpCode, 'response' => $response]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            throw new \Exception('HTTP error: ' . $httpCode . ' - ' . $response);
        }
    }
}
