<?php
// api/chatbot_service.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/api_keys.php';

header('Content-Type: application/json');

/**
 * Calls the Gemini API with a specific model.
 *
 * @param string $model The model to use (e.g., 'gemini-pro-latest').
 * @param string $question The user's question.
 * @param string $apiKey The Google AI API key.
 * @return array|null The decoded JSON response from the API.
 */
function callGeminiAPI($model, $question, $apiKey, $max_retries = 3)
{
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $question]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Use true in production with proper CA certs

    $response = null;
    $http_code = 0;

    for ($attempt = 0; $attempt < $max_retries; $attempt++) {
        $response_body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($response_body, true);

        $error_status = $response['error']['status'] ?? null;
        $is_overloaded = (
            $http_code == 429 || // Too Many Requests
            $http_code == 503 || // Service Unavailable
            $error_status === 'UNAVAILABLE' ||
            $error_status === 'RESOURCE_EXHAUSTED'
        );

        if ($is_overloaded && $attempt < $max_retries - 1) {
            $delay = 2 ** $attempt; // Exponential backoff: 1, 2, 4 seconds
            error_log("Model {$model} is overloaded (Attempt " . ($attempt + 1) . "). Retrying in {$delay} seconds...");
            sleep($delay);
        } else {
            break; // Success or final attempt
        }
    }

    curl_close($ch);

    return [
        'body' => $response,
        'http_code' => $http_code
    ];
}

// --- Main Logic ---

if (!defined('GEMINI_API_KEY')) {
    http_response_code(500);
    echo json_encode(['error' => 'API key is not configured.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_question = $input['question'] ?? '';

if (empty($user_question)) {
    http_response_code(400);
    echo json_encode(['error' => 'No question provided.']);
    exit;
}

$primaryModel = 'gemini-pro-latest';
$fallbackModel = 'gemini-flash-latest';

// First attempt with the primary model
$response = callGeminiAPI($primaryModel, $user_question, GEMINI_API_KEY);
$response_data = $response['body'];
$http_code = $response['http_code'];

// --- Fallback Logic ---
$error_status = $response_data['error']['status'] ?? null;
$should_fallback = (
    $http_code == 429 || // Too Many Requests
    $http_code == 503 || // Service Unavailable
    $error_status === 'UNAVAILABLE' ||
    $error_status === 'RESOURCE_EXHAUSTED'
);

if ($should_fallback) {
    error_log("Primary model {$primaryModel} failed (HTTP: {$http_code}, Status: {$error_status}). Trying fallback model {$fallbackModel}.");
    $response = callGeminiAPI($fallbackModel, $user_question, GEMINI_API_KEY);
    $response_data = $response['body'];
}

// Process the final response
if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
    $bot_answer = $response_data['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['answer' => $bot_answer]);
} else {
    // Log the detailed error response from the API
    error_log('Gemini API Error: ' . json_encode($response_data));

    http_response_code(500);
    echo json_encode([
        'error' => 'Could not extract answer from AI response. Check server logs for details.',
        'details' => $response_data // Still send details to the client for console debugging
    ]);
}