<?php

/**
 * Section B Integration Client Script
 * 
 * Standalone PHP script to demonstrate API integration with the Stock Movements endpoint.
 * No external dependencies (uses native curl).
 * 
 * Usage: php scripts/integration_client.php <api_token> [base_url]
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

if (!isset($argv[1])) {
    echo "Usage: php integration_client.php <api_token> [base_url]\n";
    exit(1);
}

$apiToken = $argv[1];
$baseUrl = isset($argv[2]) ? rtrim($argv[2], '/') : 'http://localhost:8000';
$logFile = __DIR__ . '/integration.log';

/**
 * Log a message to stdout and a file.
 */
function logMessage(string $message, string $level = 'INFO'): void
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[$timestamp] [$level] $message\n";
    
    echo $logLine;
    file_put_contents($logFile, $logLine, FILE_APPEND);
}

/**
 * Calculate exponential backoff delay in seconds.
 * Supports up to N retries if needed.
 */
function getBackoffDelay(int $attempt): int
{
    return (int) pow(2, $attempt);
}

/**
 * Make an HTTP request using native cURL.
 */
function makeApiRequest(string $url, string $method, string $token, array $data = null): array
{
    $ch = curl_init($url);
    
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ];

    if ($data !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body' => $responseBody ? json_decode($responseBody, true) : null,
        'error' => $error,
    ];
}

logMessage("Starting integration script. Base URL: $baseUrl");

// 1. Fetch all finished goods
$nextPageUrl = $baseUrl . '/api/v1/products?category=finished_goods';
$products = [];
$pageCount = 1;

while ($nextPageUrl !== null) {
    logMessage("Fetching products page $pageCount...");
    
    $response = makeApiRequest($nextPageUrl, 'GET', $apiToken);
    
    if ($response['status'] !== 200) {
        logMessage("Failed to fetch products: HTTP {$response['status']}. {$response['error']}", 'ERROR');
        exit(1);
    }
    
    $data = $response['body'];
    
    if (isset($data['data']) && is_array($data['data'])) {
        $products = array_merge($products, $data['data']);
    }
    
    // Check pagination via links.next
    $nextPageUrl = $data['links']['next'] ?? null;
    $pageCount++;
}

logMessage("Successfully fetched " . count($products) . " products.");

// 2. Post stock movements for each product
$successCount = 0;
$failCount = 0;

foreach ($products as $product) {
    $sku = $product['sku'] ?? null;
    if (!$sku) continue;
    
    $movementUrl = $baseUrl . '/api/v1/stock-movements';
    
    // NOTE: moved_by is hardcoded here, but could easily be made configurable via an additional $argv argument
    $payload = [
        'product_sku' => $sku,
        'warehouse_id' => 1,
        'movement_type' => 'out',
        'quantity' => 10,
        'moved_by' => 'integration_script'
    ];
    
    $maxRetries = 1;
    $attempt = 0;
    $success = false;
    
    while ($attempt <= $maxRetries && !$success) {
        if ($attempt > 0) {
            $delay = getBackoffDelay($attempt);
            logMessage("Rate limited or server error for SKU: $sku. Retrying in $delay seconds (Attempt $attempt)...", 'WARNING');
            sleep($delay);
        }
        
        $response = makeApiRequest($movementUrl, 'POST', $apiToken, $payload);
        $status = $response['status'];
        
        if ($status === 201) {
            logMessage("Processed movement for SKU: $sku (Success)");
            $success = true;
            $successCount++;
        } elseif ($status === 429 || $status >= 500) {
            $attempt++;
            if ($attempt > $maxRetries) {
                logMessage("Failed to process SKU: $sku after retry (HTTP $status). Skipping.", 'ERROR');
                $failCount++;
            }
        } else {
            // Client errors like 400, 422 - don't retry
            $errorMsg = isset($response['body']['message']) ? $response['body']['message'] : 'Unknown error';
            logMessage("Failed to process SKU: $sku (HTTP $status): $errorMsg", 'ERROR');
            $failCount++;
            break;
        }
    }
}

logMessage("Integration complete. Processed: $successCount, Failed: $failCount.");
