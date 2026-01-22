<?php
require_once '../src/utils.php';
session_start();
session_destroy();
jsonResponse(['message' => 'Logout realizado com sucesso.', 'redirect' => 'index.php']);
?>