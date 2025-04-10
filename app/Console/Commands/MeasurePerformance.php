<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class MeasurePerformance extends Command
{
    protected $signature = 'measure:performance';
    protected $description = 'Measure performance of the restaurants index route';

    public function handle()
    {
        $iterations = 1000;
        $url = 'http://localhost:8080/food-delivery-app/public/restaurants';
        $concurrency = 25; // Giảm xuống 25 để giảm tải

        $client = new Client([
            'timeout' => 120,
            'connect_timeout' => 120,
            'http_errors' => false,
            'allow_redirects' => true,
        ]);
        $requests = function () use ($url, $iterations) {
            for ($i = 0; $i < $iterations; $i++) {
                yield new Request('GET', $url);
            }
        };

        $start = microtime(true);

        $pool = new Pool($client, $requests(), [
            'concurrency' => $concurrency,
            'fulfilled' => function ($response, $index) {
                if ($response->getStatusCode() !== 200) {
                    $this->error("Request failed at iteration $index with status: " . $response->getStatusCode());
                }
            },
            'rejected' => function ($reason, $index) use ($client, $url) {
                $this->error("Request failed at iteration $index: $reason");
                $maxRetries = 5; // Tăng số lần thử lại
                $retryCount = 0;
                $success = false;

                while ($retryCount < $maxRetries && !$success) {
                    try {
                        $response = $client->get($url);
                        if ($response->getStatusCode() === 200) {
                            $success = true;
                            $this->info("Retry succeeded at iteration $index after " . ($retryCount + 1) . " attempts");
                        }
                    } catch (\Exception $e) {
                        $retryCount++;
                        $this->error("Retry $retryCount failed at iteration $index: " . $e->getMessage());
                        if ($retryCount === $maxRetries) {
                            $this->error("Max retries reached at iteration $index");
                        }
                        sleep(2); // Tăng thời gian chờ giữa các lần thử
                    }
                }
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        $end = microtime(true);
        $executionTime = ($end - $start) * 1000;
        $averageTime = $executionTime / $iterations;

        $this->info("Execution time for $iterations requests: $executionTime ms");
        $this->info("Average time per request: $averageTime ms");
    }
}
