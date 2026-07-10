<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        '_metadata' => [
            'success' => true,
            'message' => 'API is running',
        ],
        'data' => [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
        ],
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Client-side JS log collector (for remote debugging on mobile)
Route::post('/js-log', function (Request $request) {
    $entries = $request->input('entries', []);
    $ua = $request->userAgent();
    $ip = $request->ip();

    foreach (array_slice($entries, 0, 50) as $entry) {
        \Illuminate\Support\Facades\Log::channel('jslog')->info($entry['message'] ?? '', [
            'level' => $entry['level'] ?? 'log',
            'url' => $entry['url'] ?? '',
            'ua' => $ua,
            'ip' => $ip,
        ]);
    }

    return response()->json(['ok' => true]);
});

// Scanner diagnostic snapshot upload (for remote debugging on mobile).
// Frames that the decoder failed on are uploaded here so the dev can inspect
// what the camera actually saw without waiting for the user to screenshot.
Route::post('/scanner-snapshot', function (Request $request) {
    $sessionId = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $request->input('session_id', 'unknown'));
    $label = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $request->input('label', 'snap'));
    $imageDataUrl = (string) $request->input('image', '');

    if (! str_starts_with($imageDataUrl, 'data:image/')) {
        return response()->json(['ok' => false, 'error' => 'invalid image'], 400);
    }
    [, $b64] = explode(',', $imageDataUrl, 2) + [null, null];
    $bin = base64_decode($b64 ?? '', true);
    if ($bin === false || strlen($bin) > 2_000_000) {
        return response()->json(['ok' => false, 'error' => 'invalid base64 / too large'], 400);
    }

    $dir = storage_path("app/scanner-snapshots/{$sessionId}");
    if (! is_dir($dir)) @mkdir($dir, 0775, true);
    $filename = date('His') . '_' . $label . '.jpg';
    $path = "{$dir}/{$filename}";
    file_put_contents($path, $bin);

    \Illuminate\Support\Facades\Log::channel('jslog')->info(
        "[TestScanner {$sessionId}] SNAPSHOT_SAVED {$path}",
        ['level' => 'log', 'url' => $request->input('url', ''), 'ua' => $request->userAgent(), 'ip' => $request->ip()]
    );

    return response()->json(['ok' => true, 'path' => str_replace(storage_path(), '', $path)]);
});

// Server-side barcode decoder using zbarimg (industrial-grade C++ decoder).
// Accepts a JPEG/PNG image uploaded as multipart "image" OR base64 in JSON.
// Runs zbarimg, returns the decoded values + raw stdout for diagnostics.
Route::post('/decode-barcode', function (Request $request) {
    $sessionId = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $request->input('session_id', 'unknown'));

    // Accept either multipart upload OR base64 data URL
    $tmpPath = tempnam(sys_get_temp_dir(), 'scan_');
    $cleanupPath = $tmpPath;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        if (! $file->isValid() || $file->getSize() > 5_000_000) {
            @unlink($cleanupPath);
            return response()->json(['ok' => false, 'error' => 'invalid upload'], 400);
        }
        @unlink($tmpPath);
        $tmpPath .= '.' . ($file->getClientOriginalExtension() ?: 'jpg');
        $cleanupPath = $tmpPath;
        $file->move(dirname($tmpPath), basename($tmpPath));
    } else {
        $dataUrl = (string) $request->input('image', '');
        if (! str_starts_with($dataUrl, 'data:image/')) {
            @unlink($cleanupPath);
            return response()->json(['ok' => false, 'error' => 'no image'], 400);
        }
        [, $b64] = explode(',', $dataUrl, 2) + [null, null];
        $bin = base64_decode($b64 ?? '', true);
        if ($bin === false || strlen($bin) > 5_000_000) {
            @unlink($cleanupPath);
            return response()->json(['ok' => false, 'error' => 'invalid base64'], 400);
        }
        file_put_contents($tmpPath, $bin);
    }

    // Run zbarimg. --raw prints just the value, no format prefix.
    // -q quiet stderr, --xml gives us format info too if we want it later.
    $cmd = sprintf('zbarimg --raw -q %s 2>&1', escapeshellarg($tmpPath));
    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);

    // Also archive the image when decode fails so we can inspect it later.
    if ($exitCode !== 0 || empty($output)) {
        $archiveDir = storage_path("app/scanner-snapshots/{$sessionId}");
        if (! is_dir($archiveDir)) @mkdir($archiveDir, 0775, true);
        @copy($tmpPath, "{$archiveDir}/" . date('His') . '_zbar_fail.jpg');
    }

    @unlink($cleanupPath);

    $values = array_values(array_filter(array_map('trim', $output)));

    \Illuminate\Support\Facades\Log::channel('jslog')->info(
        "[TestScanner {$sessionId}] ZBAR_DECODE values=" . json_encode($values) . " exit={$exitCode}",
        ['level' => 'log', 'url' => $request->input('url', ''), 'ua' => $request->userAgent(), 'ip' => $request->ip()]
    );

    return response()->json([
        'ok' => $exitCode === 0 && ! empty($values),
        'values' => $values,
        'exit' => $exitCode,
    ]);
});

// Inclure les routes modulaires
require __DIR__.'/api/auth.php';
require __DIR__.'/api/distribution-centers.php';
require __DIR__.'/api/customers.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/bottles.php';
require __DIR__.'/api/payments.php';
require __DIR__.'/api/delivery.php';
require __DIR__.'/api/tracking.php';
require __DIR__.'/api/geography.php';
require __DIR__.'/api/app.php';
require __DIR__.'/api/manager.php';

if (! app()->isProduction()) {
    Route::post('test-websocket-event', function () {
        event(new \App\Events\TestWebSocketEvent);

        return response()->json([
            'success' => true,
            'message' => 'Test WebSocket event dispatched',
            'time' => now()->toISOString(),
        ]);
    });
}
