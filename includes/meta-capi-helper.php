<?php
/**
 * TATVAM - Meta Conversion API (CAPI) Helper
 * Dispatches server-side events (PageView, InitiateCheckout, Purchase) with full attribution parameters
 */

require_once __DIR__ . '/../config.php';

/**
 * Dispatches server-side Conversion API event to Meta
 *
 * @param string $event_name
 * @param array $user_data (email, phone, name, value, currency, event_id, client_ip, user_agent, fbp, fbc, content_name)
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

    // Format personal identifiers (Meta requires SHA256 hashed lowercase inputs)
    $hashed_email = !empty($user_data['email']) ? hash('sha256', strtolower(trim($user_data['email']))) : null;

    $clean_phone = !empty($user_data['phone']) ? preg_replace('/[^0-9]/', '', (string)$user_data['phone']) : '';
    if (strlen($clean_phone) === 10) {
        $clean_phone = '91' . $clean_phone;
    }
    $hashed_phone = !empty($clean_phone) ? hash('sha256', $clean_phone) : null;

    $first_name = '';
    $last_name = '';
    if (!empty($user_data['name'])) {
        $parts = explode(' ', trim($user_data['name']), 2);
        $first_name = hash('sha256', strtolower($parts[0]));
        if (isset($parts[1])) {
            $last_name = hash('sha256', strtolower($parts[1]));
        }
    }

    // Shared Deduplication Event ID (Matches client-side fbq eventID)
    $event_id = !empty($user_data['event_id']) ? $user_data['event_id'] : ('evt_' . uniqid() . '_' . time());

    // Resolve accurate client IP and user agent
    $client_ip = !empty($user_data['client_ip']) ? $user_data['client_ip'] : ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null);
    if ($client_ip && strpos($client_ip, ',') !== false) {
        $client_ip = trim(explode(',', $client_ip)[0]);
    }
    $client_user_agent = !empty($user_data['user_agent']) ? $user_data['user_agent'] : ($_SERVER['HTTP_USER_AGENT'] ?? null);

    // Resolve fbp (Facebook browser cookie) and fbc (Facebook click ID cookie)
    $fbp = !empty($user_data['fbp']) ? $user_data['fbp'] : ($_COOKIE['_fbp'] ?? null);
    $fbc = !empty($user_data['fbc']) ? $user_data['fbc'] : ($_COOKIE['_fbc'] ?? null);

    $userDataPayload = array_filter([
        'em' => $hashed_email,
        'ph' => $hashed_phone,
        'fn' => $first_name ?: null,
        'ln' => $last_name ?: null,
        'fbp' => $fbp ?: null,
        'fbc' => $fbc ?: null,
        'client_ip_address' => $client_ip ?: null,
        'client_user_agent' => $client_user_agent ?: null
    ]);

    $event = [
        'event_name' => $event_name,
        'event_time' => time(),
        'event_id' => $event_id,
        'event_source_url' => SITE_URL . ($_SERVER['REQUEST_URI'] ?? '/sanskar30.php'),
        'action_source' => 'website',
        'user_data' => $userDataPayload
    ];

    if ($event_name === 'Purchase') {
        $event['custom_data'] = [
            'value' => (float)($user_data['value'] ?? 0.00),
            'currency' => $user_data['currency'] ?? 'INR'
        ];
    } elseif ($event_name === 'InitiateCheckout') {
        $event['custom_data'] = [
            'value' => (float)($user_data['value'] ?? 199.00),
            'currency' => $user_data['currency'] ?? 'INR',
            'content_name' => $user_data['content_name'] ?? 'SANSKAR 30'
        ];
    }

    $post_fields = json_encode([
        'data' => [$event],
        'test_event_code' => (defined('META_CAPI_TEST_CODE') && META_CAPI_TEST_CODE !== 'TEST12345' && !empty(META_CAPI_TEST_CODE)) ? META_CAPI_TEST_CODE : null
    ]);

    $url = "https://graph.facebook.com/v19.0/" . META_PIXEL_ID . "/events?access_token=" . META_CAPI_ACCESS_TOKEN;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
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