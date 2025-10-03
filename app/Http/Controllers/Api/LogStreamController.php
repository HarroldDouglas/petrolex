<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogStreamController extends Controller
{
    /**
     * Get recent Laravel logs for frontend display
     */
    public function getRecentLogs(Request $request): JsonResponse
    {
        try {
            $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
            
            if (!File::exists($logFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No log file found for today',
                    'logs' => []
                ]);
            }

            // Get the last N lines from the log file
            $lines = $this->getLastLines($logFile, 50);
            $parsedLogs = [];

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                $logEntry = $this->parseLogLine($line);
                if ($logEntry) {
                    $parsedLogs[] = $logEntry;
                }
            }

            // Filter for payment-related logs if requested
            if ($request->get('payment_only')) {
                $parsedLogs = array_filter($parsedLogs, function($log) {
                    return stripos($log['message'], 'payment') !== false || 
                           stripos($log['message'], 'mtn') !== false || 
                           stripos($log['message'], 'orange') !== false;
                });
            }

            return response()->json([
                'success' => true,
                'logs' => array_values($parsedLogs),
                'count' => count($parsedLogs),
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse a Laravel log line into structured data
     */
    private function parseLogLine(string $line): ?array
    {
        // Laravel log format: [2025-10-03 15:25:49] local.INFO: Message {"context"} []
        if (!preg_match('/^\[([^\]]+)\] (\w+)\.(\w+): (.+)/', $line, $matches)) {
            return null;
        }

        $timestamp = $matches[1];
        $environment = $matches[2];
        $level = strtolower($matches[3]);
        $messageAndContext = $matches[4];

        // Extract emoji and clean message
        $message = $messageAndContext;
        if (strpos($messageAndContext, '{"') !== false) {
            $parts = explode(' {"', $messageAndContext, 2);
            $message = $parts[0];
        }

        // Map Laravel log levels to frontend levels
        $levelMap = [
            'info' => 'info',
            'error' => 'error',
            'warning' => 'warning',
            'debug' => 'info',
            'critical' => 'error',
            'alert' => 'error',
            'emergency' => 'error'
        ];

        return [
            'timestamp' => $timestamp,
            'level' => $levelMap[$level] ?? 'info',
            'message' => trim($message),
            'environment' => $environment,
            'raw' => $line
        ];
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