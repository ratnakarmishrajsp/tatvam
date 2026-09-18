<?php
/**
 * TATVAM - Meta Conversion API (CAPI) Helper
 * Standardizes server-side pixel tracking logs (PageView, InitiateCheckout, Purchase) for advertising attribution
 */

require_once __DIR__ . '/../config.php';

/**
 * Dispatches server-side Conversion API event to Meta
 *
 * @param string $event_name
 * @param array $user_data (email, phone, name, value, currency, etc.)
 * @return bool
 */
function sendMetaCapiEvent($event_name, $user_data = []) {
    // If not configured, bypass silently
    if (META_CAPI_ACCESS_TOKEN === 'EAAB...YOUR_ACCESS_TOKEN' || !META_PIXEL_ID) {
        if (DEBUG_MODE) {
            error_log("Meta CAPI Bypass: Event '$event_name' logged in dry-run mode.");
        }
        return false;
    }

    // Format User details (Meta requires SHA256 hashed lowercase inputs for personal identifiers)
    $hashed_email = !empty($user_data['email']) ? hash('sha256', strtolower(trim($user_data['email']))) : null;
    $hashed_phone = !empty($user_data['phone']) ? hash('sha256', preg_replace('/[^0-9]/', '', $user_data['phone'])) : null;
    
    // Split name
    $first_name = '';
    $last_name = '';
    if (!empty($user_data['name'])) {
        $parts = explode(' ', trim($user_data['name']), 2);
        $first_name = hash('sha256', strtolower($parts[0]));
        if (isset($parts[1])) {
            $last_name = hash('sha256', strtolower($parts[1]));
        }
    }

    $event_id = 'evt_' . uniqid() . '_' . time();

    // Prepare Event Data Payload
    $event = [
        'event_name' => $event_name,
        'event_time' => time(),
        'event_id' => $event_id,
        'event_source_url' => SITE_URL . $_SERVER['REQUEST_URI'],
        'action_source' => 'website',
        'user_data' => array_filter([
            'em' => $hashed_email,
            'ph' => $hashed_phone,
            'fn' => $first_name ?: null,
            'ln' => $last_name ?: null,
            'client_ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'client_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ])
    ];

    // Optional Purchase Details
    if ($event_name === 'Purchase') {
        $event['custom_data'] = [
            'value' => $user_data['value'] ?? 0.00,
            'currency' => $user_data['currency'] ?? 'INR'
        ];
    }

    $post_fields = json_encode([
        'data' => [$event],
        'test_event_code' => (META_CAPI_TEST_CODE !== 'TEST12345') ? META_CAPI_TEST_CODE : null
    ]);

    // Send HTTP POST request via cURL
    $url = "https://graph.facebook.com/v15.0/" . META_PIXEL_ID . "/events?access_token=" . META_CAPI_ACCESS_TOKEN;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log("Meta CAPI dispatch failed: HTTP $http_code. Response: $response");
        return false;
    }

    return true;
}
