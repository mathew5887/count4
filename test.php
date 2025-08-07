<?php
// Simple test to verify PHP is working
header('Content-Type: application/json');

$data = array(
    'signal' => 'ok',
    'msg' => 'Test successful',
    'timestamp' => date('Y-m-d H:i:s')
);

echo json_encode($data);
?>