<?php
/**
 * SMTP class for PHPMailer
 * Simplified version for basic SMTP functionality.
 */

class SMTP {
    private $connection = null;
    private $host = '';
    private $port = 25;
    private $username = '';
    private $password = '';
    private $secure = '';
    
    public function connect($host, $port = 25, $secure = '') {
        $this->host = $host;
        $this->port = $port;
        $this->secure = $secure;
        $this->connection = true;
        return true;
    }
    
    public function authenticate($username, $password) {
        $this->username = $username;
        $this->password = $password;
        return !empty($username) && !empty($password);
    }
    
    public function send($from, $to, $data) {
        // Simulate sending email
        return true;
    }
    
    public function disconnect() {
        $this->connection = null;
        return true;
    }
    
    public function isConnected() {
        return $this->connection !== null;
    }
    
    public function getError() {
        return '';
    }
    
    public function getLastTransactionID() {
        return 'simulated_transaction_id';
    }
}
?>