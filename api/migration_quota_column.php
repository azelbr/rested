<?php
require_once '../src/db.php';

try {
    // Add is_quota_paid column
    // Default 0 (not paid)
    $pdo->exec("ALTER TABLE receitas ADD COLUMN is_quota_paid TINYINT(1) DEFAULT 0");
    echo "Coluna is_quota_paid adicionada.\n";

    // Migrate existing data: If valor > 0, assume it was paid (quota cleared)
    $pdo->exec("UPDATE receitas SET is_quota_paid = 1 WHERE valor > 0");
    echo "Dados migrados: Registros com valor > 0 marcados como is_quota_paid = 1.\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column") !== false) {
        echo "Coluna is_quota_paid já existe.\n";
    } else {
        echo "Erro: " . $e->getMessage();
    }
}
?>