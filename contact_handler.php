<?php
// ECHO4SECURE Contact Form Handler
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Sanitize and validate input data
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Get form data
$firstName = sanitizeInput($_POST['first_name'] ?? '');
$lastName = sanitizeInput($_POST['last_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$company = sanitizeInput($_POST['company'] ?? '');
$service = sanitizeInput($_POST['service'] ?? '');
$message = sanitizeInput($_POST['message'] ?? '');
$newsletter = isset($_POST['newsletter']) ? 'Yes' : 'No';

// Validation
$errors = [];

if (empty($firstName)) {
    $errors[] = 'First name is required';
}

if (empty($lastName)) {
    $errors[] = 'Last name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!validateEmail($email)) {
    $errors[] = 'Invalid email format';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Prepare email content
$to = 'echo4secure@gmail.com';
$subject = 'New Contact Form Submission - ECHO4SECURE';

$emailBody = "
New contact form submission from ECHO4SECURE website:

Name: {$firstName} {$lastName}
Email: {$email}
Phone: {$phone}
Company: {$company}
Service Interest: {$service}
Newsletter Subscription: {$newsletter}

Message:
{$message}

---
Submitted on: " . date('Y-m-d H:i:s') . "
IP Address: " . $_SERVER['REMOTE_ADDR'] . "
";

$headers = [
    'From: noreply@echo4secure.com',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

// Try to send email
$mailSent = mail($to, $subject, $emailBody, implode("\r\n", $headers));

// Log the submission (create logs directory if it doesn't exist)
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'name' => $firstName . ' ' . $lastName,
    'email' => $email,
    'phone' => $phone,
    'company' => $company,
    'service' => $service,
    'message' => substr($message, 0, 100) . '...',
    'newsletter' => $newsletter,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'mail_sent' => $mailSent ? 'Yes' : 'No'
];

$logFile = $logDir . '/contact_submissions.log';
file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);

// Auto-responder email to user
$autoResponderSubject = 'Thank you for contacting ECHO4SECURE';
$autoResponderBody = "
Dear {$firstName},

Thank you for contacting ECHO4SECURE. We have received your message and will respond within 24 hours.

Your submission details:
- Service Interest: {$service}
- Message: {$message}

If you have any urgent security concerns, please call us directly at +92 XXX XXXXXXX.

Best regards,
ECHO4SECURE Team

---
This is an automated response. Please do not reply to this email.
";

$autoResponderHeaders = [
    'From: ECHO4SECURE <noreply@echo4secure.com>',
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

mail($email, $autoResponderSubject, $autoResponderBody, implode("\r\n", $autoResponderHeaders));

// Return response
if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We will get back to you within 24 hours.'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been received. We will contact you soon.'
    ]);
}

// Newsletter subscription handling
if ($newsletter === 'Yes') {
    // In a real implementation, you would integrate with an email service like MailChimp
    $newsletterLogEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'email' => $email,
        'name' => $firstName . ' ' . $lastName,
        'source' => 'contact_form'
    ];
    
    $newsletterLogFile = $logDir . '/newsletter_subscriptions.log';
    file_put_contents($newsletterLogFile, json_encode($newsletterLogEntry) . "\n", FILE_APPEND | LOCK_EX);
}
?>

