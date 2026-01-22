<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin();

$input = getJsonInput();
$user_id = $input['user_id'] ?? null;
$month = $input['month'] ?? null;
$year = $input['year'] ?? null;

if (!$user_id || !$month || !$year) {
    jsonResponse(['error' => 'Dados incompletos'], 400);
}

$date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$desc = "Condomínio " . date('M/Y', strtotime($date));
$categoria = "Condomínio";

// Inputs
$has_quota_input = isset($input['is_quota_paid']);
$quota_input = $has_quota_input ? (bool) $input['is_quota_paid'] : null;

$has_value_input = isset($input['custom_value']);
$value_input = $has_value_input ? floatval($input['custom_value']) : null;

// Check existing
$stmt = $pdo->prepare("SELECT * FROM receitas WHERE user_id = ? AND data = ?");
$stmt->execute([$user_id, $date]);
$existing = $stmt->fetch();

try {
    if ($existing) {
        // UPDATE
        $fields = [];
        $params = [];

        if ($has_quota_input) {
            $fields[] = "is_quota_paid = ?";
            $params[] = $quota_input ? 1 : 0;

            // Update obs if specific
            if ($quota_input) {
                $fields[] = "obs = ?";
                $params[] = "Pago via Checkbox";
            }
        }

        if ($has_value_input) {
            $fields[] = "valor = ?";
            $params[] = $value_input;
        }

        if (!empty($fields)) {
            $params[] = $existing['id'];
            $sql = "UPDATE receitas SET " . implode(", ", $fields) . " WHERE id = ?";
            $pdo->prepare($sql)->execute($params);
        }

    } else {
        // INSERT
        // Defaults
        $new_quota = $has_quota_input ? ($quota_input ? 1 : 0) : 0; // Default Not Paid
        $new_val = $has_value_input ? $value_input : 0.00; // Default 0
        $d_obs = $new_quota ? "Pago via Checkbox" : "NP";

        // If only updating value (e.g. paying debt), allow it.
        // If only updating quota (checking box), set val to 0 if not provided? 
        // Or if checking box without value, what should happen? default 150?
        // User requested: "clico... coloco 300... valor contas".
        // Let's assume if value not provided but quota set true, we might want 150 default?
        // But user said: "admin adiciona... 300... mas não seleciona checkbox".
        // So just trust inputs.

        $stmt = $pdo->prepare("INSERT INTO receitas (user_id, descricao, valor, categoria, data, obs, is_quota_paid) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $desc, $new_val, $categoria, $date, $d_obs, $new_quota]);
    }

    $debt = calculateDebt($pdo, $user_id);
    jsonResponse(['message' => 'Atualizado', 'new_debt' => $debt]);

} catch (Exception $e) {
    jsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
}
?>