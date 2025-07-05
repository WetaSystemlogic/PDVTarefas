<?php
require 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'not_authenticated']);
    } else {
        header('Location: login.php');
    }
    exit;
}