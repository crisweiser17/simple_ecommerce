<?php

require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/functions.php';

function getContactSubjects(): array
{
    return [
        'purchase_questions',
        'delivery_questions',
        'returns_exchanges',
        'payment_billing',
        'technical_support',
        'commercial_partnership',
        'other',
    ];
}

function sendContactFormMessage(array $payload): array
{
    $name = trim((string)($payload['name'] ?? ''));
    $email = trim((string)($payload['email'] ?? ''));
    $phone = trim((string)($payload['phone'] ?? ''));
    $subjectKey = trim((string)($payload['subject'] ?? ''));
    $message = trim((string)($payload['message'] ?? ''));

    if ($name === '' || $email === '' || $phone === '' || $subjectKey === '' || $message === '') {
        return ['success' => false, 'message' => __('Please fill in all required fields.')];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => __('Please enter a valid email address.')];
    }

    $allowedSubjects = getContactSubjects();
    if (!in_array($subjectKey, $allowedSubjects, true)) {
        return ['success' => false, 'message' => __('Invalid contact subject.')];
    }

    $to = getSetting('contact_receive_email', '');
    if ($to === '') {
        $to = getSetting('smtp_reply_to', '');
    }
    if ($to === '') {
        $to = getSetting('smtp_from_email', '');
    }

    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => __('Contact channel is temporarily unavailable.')];
    }

    $subjectLabel = __('contact_subject_' . $subjectKey);
    $mailSubject = __('New contact message') . ' - ' . $subjectLabel;

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safePhone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subjectLabel, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

    $html = '<h2>' . __('New contact message') . '</h2>'
        . '<p><strong>' . __('Full Name') . ':</strong> ' . $safeName . '</p>'
        . '<p><strong>' . __('Email') . ':</strong> ' . $safeEmail . '</p>'
        . '<p><strong>' . __('Phone') . ':</strong> ' . $safePhone . '</p>'
        . '<p><strong>' . __('Subject') . ':</strong> ' . $safeSubject . '</p>'
        . '<p><strong>' . __('Message') . ':</strong><br>' . $safeMessage . '</p>';

    $alt = __('New contact message') . PHP_EOL
        . __('Full Name') . ': ' . $name . PHP_EOL
        . __('Email') . ': ' . $email . PHP_EOL
        . __('Phone') . ': ' . $phone . PHP_EOL
        . __('Subject') . ': ' . $subjectLabel . PHP_EOL
        . __('Message') . ': ' . $message;

    $sendResult = sendMailSMTP($to, $mailSubject, $html, $alt);

    if (!$sendResult['success']) {
        return ['success' => false, 'message' => __('We could not send your message right now. Please try again later.')];
    }

    return ['success' => true, 'message' => __('Your message has been sent successfully!')];
}
