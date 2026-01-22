<?php
// Secure Session Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();
require_once __DIR__ . '/utils.php';

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Não autorizado. Faça login.'], 401);
    }
}

function requireAdmin()
{
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        jsonResponse(['error' => 'Acesso proibido. Apenas administradores.'], 403);
    }
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function getCurrentUser()
{
    if (!isLoggedIn())
        return null;
    return [
        'id' => $_SESSION['user_id'],
        'nome' => $_SESSION['nome'],
        'role' => $_SESSION['role']
    ];
}
?>