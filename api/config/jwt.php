<?php
const JWT_SECRET = 'replace-this-with-a-long-random-secret';
const JWT_ALGO = 'HS256';
const JWT_EXP_SECONDS = 3600;

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function getAuthorizationHeader() {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            return trim($headers['Authorization']);
        }
        if (isset($headers['authorization'])) {
            return trim($headers['authorization']);
        }
    }
    return null;
}

function getBearerToken() {
    $header = getAuthorizationHeader();
    if (!$header) {
        return null;
    }
    if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
        return $matches[1];
    }
    return null;
}

function getJsonInput() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (!is_array($data)) {
        sendError('Invalid JSON input', 400);
    }
    return $data;
}

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function sendError($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

function jwt_encode(array $payload) {
    $header = ['alg' => JWT_ALGO, 'typ' => 'JWT'];
    $payload['iat'] = time();
    if (!isset($payload['exp'])) {
        $payload['exp'] = time() + JWT_EXP_SECONDS;
    }
    $base64Header = base64url_encode(json_encode($header));
    $base64Payload = base64url_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
    $base64Signature = base64url_encode($signature);
    return "$base64Header.$base64Payload.$base64Signature";
}

function jwt_decode($jwt) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return null;
    }
    [$base64Header, $base64Payload, $base64Signature] = $parts;
    $header = json_decode(base64url_decode($base64Header), true);
    $payload = json_decode(base64url_decode($base64Payload), true);
    $signature = base64url_decode($base64Signature);

    if (!$header || !$payload || $signature === false) {
        return null;
    }
    $verify = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
    if (!hash_equals($verify, $signature)) {
        return null;
    }
    if (isset($payload['exp']) && time() > $payload['exp']) {
        return null;
    }
    return $payload;
}
