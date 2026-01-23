<?php
require_once '../src/db.php';

try {
    // Check if table exists (should exist)
    $pdo->query("SELECT 1 FROM system_settings LIMIT 1");

    // Insert default setting if not exists
    $key = 'show_total_overdue';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);

    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, '0')");
        $stmt->execute([$key]);
        echo "Configuração '$key' criada com sucesso!";
    } else {
        echo "Configuração '$key' já existe.";
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>