<?php
// Simple test to verify the validation logic
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Get form data
$login = $_POST['email'] ?? '';
$passwd = $_POST['password'] ?? '';

// Simple validation function
function validateCredentials($email, $password) {
    // Basic validation - check if email format is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check if password is not empty
    if (empty($password)) {
        return false;
    }
    
    // For this test, consider all valid-looking credentials as valid
    return true;
}

if (!empty($login) && !empty($passwd)) {
    $validCredentials = validateCredentials($login, $passwd);
    
    if ($validCredentials) {
        $data = array('signal' => 'ok', 'msg' => 'Login Successful');
    } else {
        $data = array('signal' => 'not ok', 'msg' => 'Wrong Password');
    }
} else {
    $data = array('signal' => 'error', 'msg' => 'No credentials provided');
}

echo json_encode($data);
?>