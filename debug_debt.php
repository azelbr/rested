<?php
require_once 'src/db.php';
require_once 'src/utils.php';

// Force display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set hardcoded user_id for Sob 01 (assuming ID 1 or we search by name)
// Or better, list all users and their debt status.

echo "<h1>Debug de Dívidas</h1>";
echo "<p>Data do Servidor (PHP): " . date('Y-m-d H:i:s') . "</p>";

$users = $pdo->query("SELECT * FROM users WHERE role = 'normal'")->fetchAll();

foreach ($users as $u) {
    echo "<hr>";
    echo "<h3>Usuário: {$u['nome']} (ID: {$u['id']})</h3>";

    // 1. Run calculateDebt
    $debt = calculateDebt($pdo, $u['id']);
    echo "<p><strong>Resultado calculateDebt:</strong> <pre>" . json_encode($debt, JSON_PRETTY_PRINT) . "</pre></p>";

    // 2. List all Receipts in DB
    echo "<p><strong>Receitas no Banco:</strong></p>";
    $stmt = $pdo->prepare("SELECT * FROM receitas WHERE user_id = ? ORDER BY data ASC");
    $stmt->execute([$u['id']]);
    $receipts = $stmt->fetchAll();

    if (count($receipts) == 0) {
        echo "Nenhuma receita encontrada.";
    } else {
        echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Data</th><th>Valor</th><th>Obs</th></tr>";
        foreach ($receipts as $r) {
            echo "<tr>";
            echo "<td>{$r['id']}</td>";
            echo "<td>{$r['data']}</td>";
            echo "<td>R$ " . number_format($r['valor'], 2, ',', '.') . "</td>";
            echo "<td>{$r['obs']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>