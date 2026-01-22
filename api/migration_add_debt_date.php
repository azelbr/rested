<?php
require_once '../src/db.php';

try {
    // Add debt_start_date to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN debt_start_date DATE DEFAULT '2024-01-01'");
    echo "Coluna debt_start_date adicionada com sucesso.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column") !== false) {
        echo "Coluna debt_start_date já existe.";
    } else {
        echo "Erro: " . $e->getMessage();
    }
}
?>