<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestWhatsAppConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WhatsApp server connectivity and DNS resolution';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing WhatsApp server connectivity...');

        $waServer = config('setting.wa_server', 'http://192.168.74.68:5001');
        $this->info("Server URL: {$waServer}");

        // Test DNS resolution
        $parsedUrl = parse_url($waServer);
        $host = $parsedUrl['host'] ?? null;
        $port = $parsedUrl['port'] ?? 80;

        if (!$host) {
            $this->error('Invalid server URL configuration');
            return 1;
        }

        $this->info("Testing DNS resolution for: {$host}");

        // Test with gethostbyname
        $ip = gethostbyname($host);
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            $this->warn("DNS resolution failed for {$host}");
        } else {
            $this->info("DNS resolution successful: {$host} -> {$ip}");
        }

        // Test socket connection
        $this->info("Testing socket connection to {$host}:{$port}");
        $socket = @fsockopen($host, $port, $errno, $errstr, 10);
        if ($socket) {
            $this->info("Socket connection successful");
            fclose($socket);
        } else {
            $this->warn("Socket connection failed: {$errno} - {$errstr}");
        }

        // Test HTTP connectivity
        $this->info("Testing HTTP connectivity...");
        try {
            $response = Http::timeout(10)
                ->withOptions([
                    'verify' => false,
                    'connect_timeout' => 5,
                ])
                ->get($waServer);

            $this->info("HTTP test successful - Status: {$response->status()}");
        } catch (\Exception $e) {
            $this->error("HTTP test failed: " . $e->getMessage());
        }

        // Test cURL directly
        $this->info("Testing cURL connectivity...");
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $waServer,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_VERBOSE => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($error) {
            $this->error("cURL test failed: {$error}");
        } else {
            $this->info("cURL test successful - HTTP Code: {$httpCode}");
        }

        $this->info("Connection test completed");
        return 0;
    }
}
