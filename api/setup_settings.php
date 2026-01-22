<?php
require_once '../src/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    )");

    // Insert default if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = 'saldo_inicial_sistema'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES ('saldo_inicial_sistema', '0.00')");
        echo "Configuração inicial criada.";
    } else {
        echo "Tabela já existe.";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>