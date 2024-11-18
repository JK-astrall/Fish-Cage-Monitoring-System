<?php
require_once 'config.php';

function login($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>