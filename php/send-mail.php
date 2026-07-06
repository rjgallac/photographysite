<?php
// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
        exit;
    }

    // reCAPTCHA verification - read from environment variable
    $secret = getenv('RECAPTCHA_SECRET_KEY');

    if (empty($secret)) {
        error_log("RECAPTCHA_SECRET_KEY not configured");
        echo json_encode(['success' => false, 'error' => 'Server configuration error. Please try again later.']);
        exit;
    }
    $recaptchaResponse = isset($_POST['recaptcha-response']) ? $_POST['recaptcha-response'] : '';

    if (empty($recaptchaResponse)) {
        echo json_encode(['success' => false, 'error' => 'Security verification failed. Please try again.']);
        exit;
    }

    $verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=" . urlencode($recaptchaResponse);
    $response = file_get_contents($verifyUrl);
    $responseJson = json_decode($response, true);

    if (!$responseJson['success']) {
        error_log("reCAPTCHA verification failed: " . print_r($responseJson, true));
        echo json_encode(['success' => false, 'error' => 'Security verification failed. Please try again.']);
        exit;
    }

    // Check score (0-1 scale, recommend 0.5 or higher for form submissions)
    $score = isset($responseJson['score']) ? floatval($responseJson['score']) : 0;
    if ($score < 0.5) {
        error_log("reCAPTCHA score too low: " . $score);
        echo json_encode(['success' => false, 'error' => 'Security verification failed. Please try again.']);
        exit;
    }

    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $service = isset($_POST['service']) ? trim($_POST['service']) : '';
    $event_date = isset($_POST['event-date']) ? trim($_POST['event-date']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
        exit;
    }

    // Prepare the data array
    $log_data = [
        'timestamp'   => date('Y-m-d H:i:s'),
        'subject'     => 'Contact Form Submission',
        'name'        => $name,
        'email'       => $email,
        'phone'       => $phone,
        'service'     => $service,
        'event_date'  => $event_date,
        'message'     => $message
    ];

    $log_file = '/var/www/html/logs/form-submissions.log';

    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $email_body = "Name: $name\nEmail: $email";
    if (!empty($phone)) $email_body .= "\nPhone: $phone";
    if (!empty($service)) $email_body .= "\nService: $service";
    if (!empty($event_date)) $email_body .= "\nEvent Date: $event_date";
    $email_body .= "\n\nMessage:\n$message\n";

    // Convert the array to a single-line JSON string
    // JSON_UNESCAPED_SLASHES and JSON_UNESCAPED_UNICODE make it more readable
    $log_entry = json_encode($log_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";


    if (file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX)) {
        echo json_encode(['success' => true, 'message' => 'Thank you!']);
    } else {
        error_log("Failed to write log file: $log_file");
        echo json_encode(['success' => false, 'error' => 'Failed to process your request. Please try again later.']);
    }

} catch (Exception $e) {
    error_log("Form submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An unexpected error occurred.']);
}
?>
