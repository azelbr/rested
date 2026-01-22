<?php

function calculateDebt($pdo, $user_id)
{
    // Fetch start date from user settings
    $stmt = $pdo->prepare("SELECT debt_start_date FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userRow = $stmt->fetch();

    // Default to Jan 2024 if not set
    $startDateStr = $userRow['debt_start_date'] ?? '2024-01-01';

    $start = new DateTime($startDateStr);
    $end = new DateTime('now'); // Count until today

    $missing_months = [];
    $total_owed = 0;
    $monthly_fee = 150.00;

    // Loop until we pass the current date
    // Loop until we pass the current date
    while ($start <= $end) {
        $m = (int) $start->format('n');
        $y = (int) $start->format('Y');

        // Check if Paid (Quota Cleared) via is_quota_paid
        $stmt = $pdo->prepare("SELECT id, is_quota_paid FROM receitas WHERE user_id = ? AND MONTH(data) = ? AND YEAR(data) = ?");
        $stmt->execute([$user_id, $m, $y]);
        $row = $stmt->fetch();

        // Debt if no record OR is_quota_paid is 0
        if (!$row || $row['is_quota_paid'] == 0) {
            $missing_months[] = [
                'month' => $m,
                'year' => $y,
                'label' => str_pad($m, 2, '0', STR_PAD_LEFT) . '/' . $y
            ];
            $total_owed += $monthly_fee;
        }

        $start->modify('+1 month');
    }

    return ['count' => count($missing_months), 'total' => $total_owed, 'months' => $missing_months];
}

function jsonResponse($data, $status = 200)
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getJsonInput()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)));
}
?>