<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    $to = '[your-email@example.com]'; // Update this with your email
    $subject = 'New Contact Form Submission - ' . $name;
    
    $email_body = "Name: $name\nEmail: $email";
    if (!empty($phone)) $email_body .= "\nPhone: $phone";
    if (!empty($service)) $email_body .= "\nService: $service";
    if (!empty($event_date)) $email_body .= "\nEvent Date: $event_date";
    $email_body .= "\n\nMessage:\n$message\n";

    // Log to file for now (since mail() isn't available)
    $log_file = '/var/www/html/logs/form-submissions.log';
    
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }

    $log_entry = date('Y-m-d H:i:s') . " - Subject: $subject\n$email_body\n" . str_repeat("-", 50) . "\n";
    
    if (file_put_contents($log_file, $log_entry, FILE_APPEND)) {
        echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been received.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to process your request.']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
