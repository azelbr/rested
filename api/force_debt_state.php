<?php
require_once '../src/db.php';

// Scenario Configuration
// Sobrado 01: 13 parcels (Jan 2025 - Jan 2026) -> Start: 2025-01-01
// Sobrado 06: 1 parcel (Jan 2026) -> Start: 2026-01-01
// Sobrado 07: 10 parcels (Apr 2025 - Jan 2026) -> Start: 2025-04-01
// Sobrado 08: 10 parcels (Apr 2025 - Jan 2026) -> Start: 2025-04-01

$scenarios = [
    'Sob 01' => '2025-01-01',
    'Sob 06' => '2026-01-01',
    'Sob 07' => '2025-04-01',
    'Sob 08' => '2025-04-01',
];

echo "Aplicando cenário de dívidas...\n";

foreach ($scenarios as $namePart => $startDate) {
    // Find User
    $stmt = $pdo->prepare("SELECT id, nome FROM users WHERE nome LIKE ?");
    $stmt->execute(["%$namePart%"]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Updating {$user['nome']} (ID: {$user['id']}) -> Start: $startDate\n";

        // 1. Update debt_start_date
        $upd = $pdo->prepare("UPDATE users SET debt_start_date = ? WHERE id = ?");
        $upd->execute([$startDate, $user['id']]);

        // 2. Clear receipts from that date onwards to ensure they count as debt
        // Use DELETE to be thorough, or UPDATE to set value=0? DELETE is cleaner for "unpaid".
        $del = $pdo->prepare("DELETE FROM receitas WHERE user_id = ? AND data >= ?");
        $del->execute([$user['id'], $startDate]);

        echo "  - Start date updated.\n";
        echo "  - Receipts cleared from $startDate onwards.\n";
    } else {
        echo "User matching '$namePart' not found.\n";
    }
}

echo "Done.";
?>