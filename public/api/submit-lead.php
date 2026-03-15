<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed.',
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput ?: '', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request payload.',
    ]);
    exit;
}

$cleanString = static function ($value): string {
    if (!is_string($value)) {
        return '';
    }

    $value = trim($value);
    $value = str_replace(["\r", "\n"], ' ', $value);

    return $value;
};

$name = $cleanString($payload['name'] ?? '');
$email = filter_var(trim((string) ($payload['email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
$phone = $cleanString($payload['phone'] ?? '');
$services = $cleanString($payload['services'] ?? '');
$source = $cleanString($payload['source'] ?? '');
$sourceLabel = $cleanString($payload['sourceLabel'] ?? '');
$submittedAt = $cleanString($payload['submitted_at'] ?? '');
$pageUrl = filter_var(trim((string) ($payload['page_url'] ?? '')), FILTER_SANITIZE_URL);
$consentEmail = !empty($payload['consent_email']) ? 'Yes' : 'No';
$consentPhone = !empty($payload['consent_phone']) ? 'Yes' : 'No';
$isCotswolds = !empty($payload['is_cotswolds']) ? 'Yes' : 'No';

if ($name === '' || $email === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'Name and email are required.',
    ]);
    exit;
}

$recipient = 'beatus@bes-tcapital.co.tz';
$subject = 'New lead form submission - Bes-tCapital';

$bodyLines = [
    'A new lead has been submitted from the website.',
    '',
    'Name: ' . $name,
    'Email: ' . $email,
    'Phone: ' . ($phone !== '' ? $phone : 'Not provided'),
    'Services: ' . ($services !== '' ? $services : 'Not provided'),
    'Source: ' . ($source !== '' ? $source : 'Not provided'),
    'Source label: ' . ($sourceLabel !== '' ? $sourceLabel : 'Not provided'),
    'Consent to email: ' . $consentEmail,
    'Consent to phone: ' . $consentPhone,
    'Submitted at: ' . ($submittedAt !== '' ? $submittedAt : gmdate(DATE_ATOM)),
    'Page URL: ' . ($pageUrl !== '' ? $pageUrl : 'Not provided'),
    'Cotswolds journey: ' . $isCotswolds,
    '',
    'Raw payload:',
    json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
];

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: Bes-tCapital Website <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>',
    'Reply-To: ' . $name . ' <' . $email . '>',
    'X-Mailer: PHP/' . phpversion(),
];

$sent = mail(
    $recipient,
    $subject,
    implode("\n", $bodyLines),
    implode("\r\n", $headers)
);

if (!$sent) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Email could not be sent.',
    ]);
    exit;
}

echo json_encode([
    'success' => true,
]);
