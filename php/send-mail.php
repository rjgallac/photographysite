<?php
// Handle form submission
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $service = isset($_POST['service']) ? trim($_POST['service']) : '';
    $event_date = isset($_POST['event-date']) ? trim($_POST['event-date']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
        exit;
    }

    // TODO: Configure your email settings below
    $to = '[your-email@example.com]'; // Your email address
    $subject = 'New Contact Form Submission - ' . $name;
    
    $email_body = "You have received a new contact form submission:\n\n";
    $email_body .= "Name: $name\n";
    $email_body .= "Email: $email\n";
    if (!empty($phone)) {
        $email_body .= "Phone: $phone\n";
    }
    if (!empty($service)) {
        $email_body .= "Service Interested In: $service\n";
    }
    if (!empty($event_date)) {
        $email_body .= "Event Date: $event_date\n";
    }
    $email_body .= "\nMessage:\n$message\n";

    // TODO: Set up proper headers for your server
    $headers = "From: $name <$email>\r\nReply-To: $email\r\nX-Mailer: PHP/" . phpversion();

    if (mail($to, $subject, $email_body, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent.']);
    } else {
        // Log error for debugging
        error_log("Email sending failed: $subject");
        echo json_encode(['success' => false, 'error' => 'Failed to send message. Please try again later.']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
