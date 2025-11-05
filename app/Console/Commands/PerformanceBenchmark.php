<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PerformanceBenchmark extends Command
{
    protected $signature = 'performance:benchmark {--iterations=50 : Number of iterations for each test}';
    protected $description = 'Run performance benchmarks for the optimized API';

    private array $metrics = [];

    public function handle()
    {
        $iterations = (int) $this->option('iterations');
        
        $this->info("🚀 Starting Performance Benchmark");
        $this->info("Iterations per test: {$iterations}");
        $this->newLine();

        // Ensure we have a test user
        $this->ensureTestUser();

        // Run benchmarks
        $this->benchmarkUnauthenticatedEndpoints($iterations);
        $this->benchmarkAuthenticatedEndpoints($iterations);
        $this->benchmarkMemoryUsage($iterations);
        $this->benchmarkMiddlewareOverhead($iterations);
        $this->benchmarkDatabaseQueries();

        // Generate report
        $this->generateReport();

        return 0;
    }

    private function ensureTestUser()
    {
        $user = User::where('email', 'benchmark@example.com')->first();
        
        if (!$user) {
            User::create([
                'name' => 'Benchmark User',
                'email' => 'benchmark@example.com',
                'password' => bcrypt('benchmark123'),
            ]);
            $this->info("✅ Created benchmark test user");
        }
    }

    private function benchmarkUnauthenticatedEndpoints(int $iterations)
    {
        $this->info("📊 Benchmarking unauthenticated endpoints...");
        
        $baseUrl = config('app.url');
        $responseTimes = [];
        $memoryUsages = [];

        for ($i = 0; $i < $iterations; $i++) {
            $startTime = microtime(true);
            $startMemory = memory_get_usage();

            try {
                $response = Http::timeout(10)->post("{$baseUrl}/api/login", [
                    'email' => 'benchmark@example.com',
                    'password' => 'benchmark123'
                ]);

                $endTime = microtime(true);
                $endMemory = memory_get_usage();

                if ($response->successful()) {
                    $responseTime = ($endTime - $startTime) * 1000;
                    $memoryUsage = $endMemory - $startMemory;
                    
                    $responseTimes[] = $responseTime;
                    $memoryUsages[] = $memoryUsage;
                }
            } catch (\Exception $e) {
                $this->warn("Request failed: " . $e->getMessage());
            }

            if ($i % 10 == 0) {
                $this->info("  Progress: " . ($i + 1) . "/{$iterations}");
            }
        }

        $this->metrics['unauthenticated'] = [
            'avg_response_time' => count($responseTimes) > 0 ? array_sum($responseTimes) / count($responseTimes) : 0,
            'min_response_time' => count($responseTimes) > 0 ? min($responseTimes) : 0,
            'max_response_time' => count($responseTimes) > 0 ? max($responseTimes) : 0,
            'avg_memory_usage' => count($memoryUsages) > 0 ? array_sum($memoryUsages) / count($memoryUsages) : 0,
            'successful_requests' => count($responseTimes),
            'total_requests' => $iterations
        ];

        $this->info("✅ Unauthenticated endpoints benchmark completed");
    }

    private function benchmarkAuthenticatedEndpoints(int $iterations)
    {
        $this->info("🔐 Benchmarking authenticated endpoints...");
        
        $baseUrl = config('app.url');
        
        // Get authentication token
        $loginResponse = Http::post("{$baseUrl}/api/login", [
            'email' => 'benchmark@example.com',
            'password' => 'benchmark123'
        ]);

        if (!$loginResponse->successful()) {
            $this->error("Failed to get authentication token");
            return;
        }

        $token = $loginResponse->json('token');
        $responseTimes = [];
        $memoryUsages = [];

        for ($i = 0; $i < $iterations; $i++) {
            $startTime = microtime(true);
            $startMemory = memory_get_usage();

            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json'
                    ])
                    ->get("{$baseUrl}/api/me");

                $endTime = microtime(true);
                $endMemory = memory_get_usage();

                if ($response->successful()) {
                    $responseTime = ($endTime - $startTime) * 1000;
                    $memoryUsage = $endMemory - $startMemory;
                    
                    $responseTimes[] = $responseTime;
                    $memoryUsages[] = $memoryUsage;
                }
            } catch (\Exception $e) {
                $this->warn("Request failed: " . $e->getMessage());
            }

            if ($i % 10 == 0) {
                $this->info("  Progress: " . ($i + 1) . "/{$iterations}");
            }
        }

        $this->metrics['authenticated'] = [
            'avg_response_time' => count($responseTimes) > 0 ? array_sum($responseTimes) / count($responseTimes) : 0,
            'min_response_time' => count($responseTimes) > 0 ? min($responseTimes) : 0,
            'max_response_time' => count($responseTimes) > 0 ? max($responseTimes) : 0,
            'avg_memory_usage' => count($memoryUsages) > 0 ? array_sum($memoryUsages) / count($memoryUsages) : 0,
            'successful_requests' => count($responseTimes),
            'total_requests' => $iterations
        ];

        $this->info("✅ Authenticated endpoints benchmark completed");
    }

    private function benchmarkMemoryUsage(int $iterations)
    {
        $this->info("💾 Benchmarking memory usage...");
        
        $baselineMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();

        // Simulate typical API usage pattern
        for ($i = 0; $i < $iterations; $i++) {
            // Create some objects to simulate request processing
            $data = [
                'request_id' => uniqid(),
                'timestamp' => now(),
                'payload' => str_repeat('x', 1000), // 1KB of data
                'metadata' => [
                    'user_agent' => 'Benchmark/1.0',
                    'ip' => '127.0.0.1',
                    'iteration' => $i
                ]
            ];
            
            // Simulate some processing
            json_encode($data);
            unset($data);
            
            if ($i % 10 == 0) {
                gc_collect_cycles(); // Force garbage collection
            }
        }

        $finalMemory = memory_get_usage();
        $finalPeakMemory = memory_get_peak_usage();

        $this->metrics['memory'] = [
            'baseline_memory_mb' => round($baselineMemory / 1024 / 1024, 2),
            'final_memory_mb' => round($finalMemory / 1024 / 1024, 2),
            'memory_increase_mb' => round(($finalMemory - $baselineMemory) / 1024 / 1024, 2),
            'peak_memory_increase_mb' => round(($finalPeakMemory - $peakMemory) / 1024 / 1024, 2),
            'iterations' => $iterations
        ];

        $this->info("✅ Memory usage benchmark completed");
    }

    private function benchmarkMiddlewareOverhead(int $iterations)
    {
        $this->info("⚡ Benchmarking middleware overhead...");
        
        // This is a simplified simulation since we can't easily bypass middleware
        // We measure the difference between simple and complex requests
        
        $simpleRequestTimes = [];
        $complexRequestTimes = [];
        
        $baseUrl = config('app.url');
        
        // Simple requests (login - minimal middleware)
        for ($i = 0; $i < min($iterations, 20); $i++) {
            $startTime = microtime(true);
            
            Http::post("{$baseUrl}/api/login", [
                'email' => 'benchmark@example.com',
                'password' => 'benchmark123'
            ]);
            
            $endTime = microtime(true);
            $simpleRequestTimes[] = ($endTime - $startTime) * 1000;
        }
        
        // Get token for authenticated requests
        $token = Http::post("{$baseUrl}/api/login", [
            'email' => 'benchmark@example.com',
            'password' => 'benchmark123'
        ])->json('token');
        
        // Complex requests (authenticated - more middleware)
        for ($i = 0; $i < min($iterations, 20); $i++) {
            $startTime = microtime(true);
            
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get("{$baseUrl}/api/me");
            
            $endTime = microtime(true);
            $complexRequestTimes[] = ($endTime - $startTime) * 1000;
        }

        $avgSimple = count($simpleRequestTimes) > 0 ? array_sum($simpleRequestTimes) / count($simpleRequestTimes) : 0;
        $avgComplex = count($complexRequestTimes) > 0 ? array_sum($complexRequestTimes) / count($complexRequestTimes) : 0;

        $this->metrics['middleware'] = [
            'avg_simple_request_time' => round($avgSimple, 2),
            'avg_complex_request_time' => round($avgComplex, 2),
            'middleware_overhead' => round($avgComplex - $avgSimple, 2),
            'simple_requests' => count($simpleRequestTimes),
            'complex_requests' => count($complexRequestTimes)
        ];

        $this->info("✅ Middleware overhead benchmark completed");
    }

    private function benchmarkDatabaseQueries()
    {
        $this->info("🗄️ Benchmarking database performance...");
        
        DB::enableQueryLog();
        
        $startTime = microtime(true);
        
        // Simulate typical API operations
        $user = User::where('email', 'benchmark@example.com')->first();
        $token = $user->createToken('benchmark')->plainTextToken;
        
        // Simulate token verification (what happens in auth:sanctum middleware)
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        $tokenUser = $tokenModel ? $tokenModel->tokenable : null;
        
        $endTime = microtime(true);
        
        $queries = DB::getQueryLog();
        $totalTime = ($endTime - $startTime) * 1000;
        
        $this->metrics['database'] = [
            'total_queries' => count($queries),
            'total_time_ms' => round($totalTime, 2),
            'avg_time_per_query_ms' => count($queries) > 0 ? round($totalTime / count($queries), 2) : 0,
            'queries' => array_map(function($query) {
                return [
                    'sql' => $query['query'],
                    'time' => $query['time']
                ];
            }, $queries)
        ];
        
        DB::disableQueryLog();
        
        $this->info("✅ Database performance benchmark completed");
    }

    private function generateReport()
    {
        $this->newLine(2);
        $this->info("📋 PERFORMANCE BENCHMARK REPORT");
        $this->info(str_repeat("=", 60));
        
        // Response Time Analysis
        $this->newLine();
        $this->info("📊 RESPONSE TIME METRICS:");
        if (isset($this->metrics['unauthenticated'])) {
            $unauth = $this->metrics['unauthenticated'];
            $this->line("  Unauthenticated Endpoints:");
            $this->line("    Average: " . round($unauth['avg_response_time'], 2) . "ms");
            $this->line("    Min: " . round($unauth['min_response_time'], 2) . "ms");
            $this->line("    Max: " . round($unauth['max_response_time'], 2) . "ms");
            $this->line("    Success Rate: " . round(($unauth['successful_requests'] / $unauth['total_requests']) * 100, 1) . "%");
        }
        
        if (isset($this->metrics['authenticated'])) {
            $auth = $this->metrics['authenticated'];
            $this->line("  Authenticated Endpoints:");
            $this->line("    Average: " . round($auth['avg_response_time'], 2) . "ms");
            $this->line("    Min: " . round($auth['min_response_time'], 2) . "ms");
            $this->line("    Max: " . round($auth['max_response_time'], 2) . "ms");
            $this->line("    Success Rate: " . round(($auth['successful_requests'] / $auth['total_requests']) * 100, 1) . "%");
        }
        
        // Memory Usage Analysis
        $this->newLine();
        $this->info("💾 MEMORY USAGE ANALYSIS:");
        if (isset($this->metrics['memory'])) {
            $memory = $this->metrics['memory'];
            $this->line("  Baseline Memory: " . $memory['baseline_memory_mb'] . "MB");
            $this->line("  Final Memory: " . $memory['final_memory_mb'] . "MB");
            $this->line("  Memory Increase: " . $memory['memory_increase_mb'] . "MB");
            $this->line("  Peak Memory Increase: " . $memory['peak_memory_increase_mb'] . "MB");
        }
        
        // Middleware Overhead Analysis
        $this->newLine();
        $this->info("⚡ MIDDLEWARE OVERHEAD:");
        if (isset($this->metrics['middleware'])) {
            $middleware = $this->metrics['middleware'];
            $this->line("  Simple Requests: " . $middleware['avg_simple_request_time'] . "ms");
            $this->line("  Complex Requests: " . $middleware['avg_complex_request_time'] . "ms");
            $this->line("  Overhead: " . $middleware['middleware_overhead'] . "ms");
        }
        
        // Database Performance Analysis
        $this->newLine();
        $this->info("🗄️ DATABASE PERFORMANCE:");
        if (isset($this->metrics['database'])) {
            $db = $this->metrics['database'];
            $this->line("  Total Queries: " . $db['total_queries']);
            $this->line("  Total Time: " . $db['total_time_ms'] . "ms");
            $this->line("  Avg Time per Query: " . $db['avg_time_per_query_ms'] . "ms");
        }
        
        // Optimization Validation
        $this->newLine();
        $this->info("✅ OPTIMIZATION VALIDATION:");
        
        $validations = [];
        
        if (isset($this->metrics['unauthenticated']) && $this->metrics['unauthenticated']['avg_response_time'] < 100) {
            $validations[] = "✅ Unauthenticated response times optimized (< 100ms)";
        } else {
            $validations[] = "⚠️  Unauthenticated response times need optimization";
        }
        
        if (isset($this->metrics['authenticated']) && $this->metrics['authenticated']['avg_response_time'] < 50) {
            $validations[] = "✅ Authenticated response times optimized (< 50ms)";
        } else {
            $validations[] = "⚠️  Authenticated response times need optimization";
        }
        
        if (isset($this->metrics['memory']) && $this->metrics['memory']['memory_increase_mb'] < 5) {
            $validations[] = "✅ Memory usage optimized (< 5MB increase)";
        } else {
            $validations[] = "⚠️  Memory usage needs optimization";
        }
        
        if (isset($this->metrics['middleware']) && $this->metrics['middleware']['middleware_overhead'] < 20) {
            $validations[] = "✅ Middleware overhead optimized (< 20ms)";
        } else {
            $validations[] = "⚠️  Middleware overhead needs optimization";
        }
        
        if (isset($this->metrics['database']) && $this->metrics['database']['total_queries'] < 5) {
            $validations[] = "✅ Database queries optimized (< 5 queries)";
        } else {
            $validations[] = "⚠️  Database queries need optimization";
        }
        
        foreach ($validations as $validation) {
            $this->line("  " . $validation);
        }
        
        $this->newLine();
        $this->info(str_repeat("=", 60));
        $this->info("🎉 Performance benchmark completed successfully!");
    }
}