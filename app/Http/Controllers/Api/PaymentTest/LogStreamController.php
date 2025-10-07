<?php

namespace App\Http\Controllers\Api\PaymentTest;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogStreamController extends Controller
{
    /**
     * Get recent Laravel logs for payment test frontend display
     */
    public function getRecentLogs(Request $request): JsonResponse
    {
        try {
            $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
            
            if (!File::exists($logFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No log file found for today',
                    'logs' => [],
                    'timestamp' => now()->toISOString()
                ]);
            }

            $lineCount = $request->get('lines', 100); // Default 100 lines for test environment
            $lines = $this->getLastLines($logFile, min($lineCount, 500)); // Max 500 lines
            $parsedLogs = [];

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                $logEntry = $this->parseLogLine($line);
                if ($logEntry) {
                    $parsedLogs[] = $logEntry;
                }
            }

            $filterType = $request->get('filter', 'payment');
            
            if ($filterType === 'payment') {
                $parsedLogs = $this->filterPaymentLogs($parsedLogs);
            } elseif ($filterType === 'test') {
                $parsedLogs = $this->filterTestLogs($parsedLogs);
            } elseif ($filterType === 'callback') {
                $parsedLogs = $this->filterCallbackLogs($parsedLogs);
            }

            usort($parsedLogs, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return response()->json([
                'success' => true,
                'logs' => array_values($parsedLogs),
                'count' => count($parsedLogs),
                'filter' => $filterType,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment test logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live payment test logs (streaming endpoint)
     */
    public function getLivePaymentLogs(Request $request): JsonResponse
    {
        try {
            $since = now()->subMinutes(5)->format('Y-m-d H:i:s');
            $logs = $this->getRecentLogsSince($since);
            
            $paymentLogs = $this->filterPaymentLogs($logs);
            
            return response()->json([
                'success' => true,
                'logs' => $paymentLogs,
                'count' => count($paymentLogs),
                'since' => $since,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch live payment logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment test statistics from logs
     */
    public function getPaymentTestStats(Request $request): JsonResponse
    {
        try {
            $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
            
            if (!File::exists($logFile)) {
                return response()->json([
                    'success' => true,
                    'stats' => $this->getEmptyStats(),
                    'timestamp' => now()->toISOString()
                ]);
            }

            $lines = $this->getLastLines($logFile, 1000); // Analyze more lines for stats
            $parsedLogs = [];

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                $logEntry = $this->parseLogLine($line);
                if ($logEntry && $this->isPaymentLog($logEntry)) {
                    $parsedLogs[] = $logEntry;
                }
            }

            $stats = $this->calculatePaymentStats($parsedLogs);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate payment test stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse a Laravel log line into structured data for payment tests
     */
    private function parseLogLine(string $line): ?array
    {
       if (!preg_match('/^\[([^\]]+)\] (\w+)\.(\w+): (.+)/', $line, $matches)) {
            return null;
        }

        $timestamp = $matches[1];
        $environment = $matches[2];
        $level = strtolower($matches[3]);
        $messageAndContext = $matches[4];

        $message = $messageAndContext;
        $context = null;
        
        if (strpos($messageAndContext, '{"') !== false) {
            $parts = explode(' {"', $messageAndContext, 2);
            $message = $parts[0];
            if (isset($parts[1])) {
                $contextStr = '{"' . $parts[1];
                $context = json_decode(trim($contextStr, ' []'), true);
            }
        }

        $levelMap = [
            'info' => 'info',
            'error' => 'error',
            'warning' => 'warning',
            'debug' => 'info',
            'critical' => 'error',
            'alert' => 'error',
            'emergency' => 'error'
        ];

        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $levelMap[$level] ?? 'info',
            'message' => trim($message),
            'environment' => $environment,
            'context' => $context,
            'raw' => $line
        ];

        $logEntry = $this->enhanceLogEntry($logEntry);

        return $logEntry;
    }

    /**
     * Enhance log entry with payment-specific metadata
     */
    private function enhanceLogEntry(array $logEntry): array
    {
        $message = $logEntry['message'];
        
        if (stripos($message, 'mtn') !== false || stripos($message, 'momo') !== false) {
            $logEntry['provider'] = 'MTN';
        } elseif (stripos($message, 'orange') !== false) {
            $logEntry['provider'] = 'ORANGE';
        }

        if (stripos($message, 'payment test initiated') !== false) {
            $logEntry['action'] = 'INITIATE';
        } elseif (stripos($message, 'callback') !== false) {
            $logEntry['action'] = 'CALLBACK';
        } elseif (stripos($message, 'status') !== false) {
            $logEntry['action'] = 'STATUS_CHECK';
        } elseif (stripos($message, 'processing completed') !== false) {
            $logEntry['action'] = 'COMPLETE';
        }

        if (stripos($message, 'PENDING') !== false) {
            $logEntry['payment_status'] = 'PENDING';
            $logEntry['level'] = 'pending';
        } elseif (stripos($message, 'SUCCESSFUL') !== false || stripos($message, 'SUCCESS') !== false) {
            $logEntry['payment_status'] = 'SUCCESS';
        } elseif (stripos($message, 'FAILED') !== false || stripos($message, 'FAILURE') !== false) {
            $logEntry['payment_status'] = 'FAILED';
        }

        return $logEntry;
    }

    /**
     * Filter logs for payment-related activities
     */
    private function filterPaymentLogs(array $logs): array
    {
        return array_filter($logs, function($log) {
            return $this->isPaymentLog($log);
        });
    }

    /**
     * Filter logs for test-specific activities
     */
    private function filterTestLogs(array $logs): array
    {
        return array_filter($logs, function($log) {
            $message = strtolower($log['message']);
            return stripos($message, 'test') !== false ||
                   stripos($message, 'simulation') !== false ||
                   stripos($message, 'sandbox') !== false;
        });
    }

    /**
     * Filter logs for callback activities
     */
    private function filterCallbackLogs(array $logs): array
    {
        return array_filter($logs, function($log) {
            $message = strtolower($log['message']);
            return stripos($message, 'callback') !== false ||
                   stripos($message, 'external') !== false;
        });
    }

    /**
     * Check if log entry is payment-related
     */
    private function isPaymentLog(array $log): bool
    {
        $message = strtolower($log['message']);
        
        $paymentKeywords = [
            'payment', 'mtn', 'orange', 'momo', 'callback', 'transaction',
            'gateway', 'processing', 'external_id', 'reference_id'
        ];

        foreach ($paymentKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate payment statistics from logs
     */
    private function calculatePaymentStats(array $logs): array
    {
        $stats = [
            'total_payments' => 0,
            'successful_payments' => 0,
            'failed_payments' => 0,
            'pending_payments' => 0,
            'mtn_payments' => 0,
            'orange_payments' => 0,
            'sandbox_payments' => 0,
            'live_payments' => 0,
            'callbacks_received' => 0,
            'avg_processing_time' => 0
        ];

        $processingTimes = [];

        foreach ($logs as $log) {
            $message = strtolower($log['message']);

            if (stripos($message, 'payment test initiated') !== false) {
                $stats['total_payments']++;

                if (isset($log['provider'])) {
                    if ($log['provider'] === 'MTN') {
                        $stats['mtn_payments']++;
                    } elseif ($log['provider'] === 'ORANGE') {
                        $stats['orange_payments']++;
                    }
                }

                if (stripos($message, 'sandbox') !== false) {
                    $stats['sandbox_payments']++;
                } elseif (stripos($message, 'live') !== false) {
                    $stats['live_payments']++;
                }
            }

            if (isset($log['payment_status'])) {
                switch ($log['payment_status']) {
                    case 'SUCCESS':
                        $stats['successful_payments']++;
                        break;
                    case 'FAILED':
                        $stats['failed_payments']++;
                        break;
                    case 'PENDING':
                        $stats['pending_payments']++;
                        break;
                }
            }*
            if (stripos($message, 'callback') !== false) {
                $stats['callbacks_received']++;
            }

            if (preg_match('/processing time: ([\d.]+)ms/', $message, $matches)) {
                $processingTimes[] = floatval($matches[1]);
            }
        }

        if (!empty($processingTimes)) {
            $stats['avg_processing_time'] = round(array_sum($processingTimes) / count($processingTimes), 2);
        }

        return $stats;
    }

    /**
     * Get empty stats structure
     */
    private function getEmptyStats(): array
    {
        return [
            'total_payments' => 0,
            'successful_payments' => 0,
            'failed_payments' => 0,
            'pending_payments' => 0,
            'mtn_payments' => 0,
            'orange_payments' => 0,
            'sandbox_payments' => 0,
            'live_payments' => 0,
            'callbacks_received' => 0,
            'avg_processing_time' => 0
        ];
    }

    /**
     * Get recent logs since a specific time
     */
    private function getRecentLogsSince(string $since): array
    {
        $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
        
        if (!File::exists($logFile)) {
            return [];
        }

        $lines = $this->getLastLines($logFile, 200);
        $parsedLogs = [];
        $sinceTimestamp = strtotime($since);

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $logEntry = $this->parseLogLine($line);
            if ($logEntry) {
                $logTimestamp = strtotime($logEntry['timestamp']);
                if ($logTimestamp >= $sinceTimestamp) {
                    $parsedLogs[] = $logEntry;
                }
            }
        }

        return $parsedLogs;
    }

    /**
     * Get the last N lines from a file efficiently
     */
    private function getLastLines(string $filename, int $lines = 50): array
    {
        $handle = fopen($filename, 'r');
        if (!$handle) {
            return [];
        }

        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];

        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) break;
        }

        fclose($handle);
        return array_reverse($text);
    }
}