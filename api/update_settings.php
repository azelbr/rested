<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin();

$input = getJsonInput();
$key = $input['key'] ?? '';
$value = $input['value'] ?? '';

if (empty($key)) {
    jsonResponse(['error' => 'Chave inválida'], 400);
}

try {
    // Check if key exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    } else {
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    jsonResponse(['message' => 'Configuração salva']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao salvar'], 500);
}
?>