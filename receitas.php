<?php
require_once 'src/db.php';
require_once 'src/auth.php';
require_once 'src/utils.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

// Admin sees ALL. User sees OWN.
// We need to fetch data similar to previous dashboard logic.

$receitas_list = [];
$total_receitas = 0;

if ($role === 'admin') {
    // Admin: List all Sobrados for the selected month
    $sql_users = "SELECT id, nome FROM users WHERE role != 'admin' ORDER BY nome ASC";
    $users = $pdo->query($sql_users)->fetchAll();

    foreach ($users as $u) {
        $stmt = $pdo->prepare("SELECT * FROM receitas WHERE user_id = ? AND MONTH(data) = ? AND YEAR(data) = ?");
        $stmt->execute([$u['id'], $month, $year]);
        $rec = $stmt->fetch();

        $val = $rec ? $rec['valor'] : 0;
        $receitas_list[] = [
            'sobrado' => $u['nome'],
            'user_id' => $u['id'],
            'valor' => $val,
            'obs' => $rec ? $rec['obs'] : '',
            'id' => $rec ? $rec['id'] : null,
            'paid' => ($val > 0)
        ];
        $total_receitas += $val;
    }
} else {
    // User: List OWN Receipts History (Not just one month, maybe all history?)
    // Prompt says "ir para a página receitas somente do usuário logado"
    // Let's show history + month filter.

    $where = "user_id = ?";
    $params = [$user_id];

    if ($month && $year) {
        $where .= " AND MONTH(data) = ? AND YEAR(data) = ?";
        $params[] = $month;
        $params[] = $year;
    }

    $stmt = $pdo->prepare("SELECT * FROM receitas WHERE $where ORDER BY data DESC");
    $stmt->execute($params);
    $receitas_list = $stmt->fetchAll();

    foreach ($receitas_list as $r)
        $total_receitas += $r['valor'];
}

require 'src/head.php';
?>

<body class="bg-gray-100 min-h-screen pb-24 font-sans overflow-y-auto">
    <!-- Header Simples -->
    <header class="bg-green-600 text-white p-4 shadow-md sticky top-0 z-10 flex justify-between items-center">
        <a href="dashboard.php"><ion-icon name="arrow-back" class="text-2xl"></ion-icon></a>
        <h1 class="font-bold text-lg">Receitas</h1>
        <div class="w-6"></div> <!-- Spacer -->
    </header>

    <!-- Filter -->
    <div class="bg-white p-4 shadow-sm mb-4">
        <form class="flex gap-2 justify-center">
            <select name="month" onchange="this.form.submit()" class="bg-slate-100 p-2 rounded-lg text-sm outline-none">
                <?php for ($i = 1; $i <= 12; $i++)
                    echo "<option value='$i' " . ($month == $i ? 'selected' : '') . ">" . date('M', mktime(0, 0, 0, $i, 1)) . "</option>"; ?>
            </select>
            <select name="year" onchange="this.form.submit()" class="bg-slate-100 p-2 rounded-lg text-sm outline-none">
                <?php $cy = date('Y');
                for ($i = $cy; $i >= $cy - 1; $i--)
                    echo "<option value='$i' " . ($year == $i ? 'selected' : '') . ">$i</option>"; ?>
            </select>
        </form>
    </div>

    <!-- Content -->
    <div class="px-4">
        <?php if ($role === 'admin'): ?>
            <div class="space-y-4">
                <?php
                // Fetch all users
                $users = $pdo->query("SELECT id, nome FROM users WHERE role != 'admin' ORDER BY nome ASC")->fetchAll();

                foreach ($users as $u):
                    $debt = calculateDebt($pdo, $u['id']);
                    $hasDebt = $debt['count'] > 0;
                    $cardColor = $hasDebt ? "border-l-red-500" : "border-l-green-500";
                    $badgeDetails = $hasDebt
                        ? "<span class='bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold'>{$debt['count']} em atraso</span>"
                        : "<span class='bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold'>Em dia</span>";
                    ?>
                    <div
                        class="bg-white rounded-xl shadow-sm border-l-4 <?php echo $cardColor; ?> overflow-hidden accordion-item">
                        <!-- Header / Summary -->
                        <button onclick="toggleAccordion(<?php echo $u['id']; ?>)"
                            class="w-full flex justify-between items-center p-4 hover:bg-slate-50 transition-colors text-left">
                            <div>
                                <p class="font-bold text-slate-700 text-lg"><?php echo $u['nome']; ?></p>
                                <div id="badge-<?php echo $u['id']; ?>"><?php echo $badgeDetails; ?></div>
                            </div>
                            <ion-icon name="chevron-down-outline" class="text-xl text-slate-400 transition-transform"
                                id="icon-<?php echo $u['id']; ?>"></ion-icon>
                        </button>

                        <!-- Expanded Month Grid -->
                        <div id="content-<?php echo $u['id']; ?>" class="hidden border-t border-slate-100 bg-slate-50 p-4">
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                                <?php
                                // Generate months from Jan 2024 to Current Month + 1 (or end of current year?)
                                // Let's go Jan 2024 to Dec 2025 as seen in image, or automated.
                                $start = new DateTime('2024-01-01');
                                $end = new DateTime('now');
                                $end->modify('+1 month'); // Show one month ahead? Or just to current.
                        
                                // Let's Loop explicitly like the Debug script
                                $y = 2025;
                                $currY = date('Y');
                                $currM = date('n');

                                // We need to fetch ALL receipts for this user
                                $stmt = $pdo->prepare("SELECT MONTH(data) as m, YEAR(data) as y, valor, is_quota_paid FROM receitas WHERE user_id = ?");
                                $stmt->execute([$u['id']]);
                                $paidMap = [];
                                while ($r = $stmt->fetch()) {
                                    $paidMap[$r['y'] . '-' . $r['m']] = ['paid' => ($r['is_quota_paid'] == 1), 'valor' => $r['valor']];
                                }

                                // Rendering 2025 onwards
                                $yearsToShow = range(2025, $currY);

                                foreach ($yearsToShow as $loopY):
                                    for ($loopM = 1; $loopM <= 12; $loopM++):
                                        // Don't show future if too far? User image showed 2025.
                                        if ($loopY > $currY || ($loopY == $currY && $loopM > $currM)) {
                                            // Optional: Hide future
                                        }

                                        $k = "$loopY-$loopM";
                                        $data = isset($paidMap[$k]) ? $paidMap[$k] : ['paid' => false, 'valor' => 0];
                                        $isPaid = $data['paid'];
                                        $valDisplay = number_format($data['valor'], 2, ',', '.');
                                        $monthName = ["", "Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"][$loopM];
                                        ?>
                                        <div class="flex flex-col items-center justify-center p-1 bg-white rounded border <?php echo $isPaid ? 'border-green-200 bg-green-50' : 'border-slate-200'; ?> transition-all hover:shadow-md h-24 gap-1">
                                            <span class="text-xs font-bold text-slate-500"><?php echo $monthName . '/' . substr($loopY, 2); ?></span>
                                            
                                            <!-- Checkbox for Quota Status -->
                                            <input type="checkbox"
                                                class="w-5 h-5 text-green-600 rounded focus:ring-green-500 cursor-pointer"
                                                <?php echo $isPaid ? 'checked' : ''; ?>
                                                onchange="togglePayment(<?php echo $u['id']; ?>, <?php echo $loopM; ?>, <?php echo $loopY; ?>, this)">
                                            
                                            <!-- Value Display/Edit -->
                                            <button onclick="editValue(<?php echo $u['id']; ?>, <?php echo $loopM; ?>, <?php echo $loopY; ?>, <?php echo $data['valor']; ?>)" 
                                                class="text-[10px] text-slate-500 hover:text-blue-600 border border-transparent hover:border-blue-200 rounded px-1 transition-colors">
                                                R$ <?php echo $valDisplay; ?>
                                            </button>
                                        </div>
                                    <?php endfor; endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
                function toggleAccordion(id) {
                    const content = document.getElementById(`content-${id}`);
                    const icon = document.getElementById(`icon-${id}`);
                    content.classList.toggle('hidden');
                    icon.classList.toggle('rotate-180');
                }

                async function editValue(userId, m, y, currentVal) {
                    const newVal = prompt("Informe o valor pago (ex: 300.00):\nValor conta para Receita, mas não quita parcela automaticamente.", currentVal || "0.00");
                    if (newVal === null) return; 

                    const floatVal = parseFloat(newVal.replace(',', '.'));
                    if (isNaN(floatVal)) { alert("Valor inválido"); return; }

                    try {
                        const res = await fetch('api/toggle_payment.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                user_id: userId,
                                month: m,
                                year: y,
                                custom_value: floatVal
                            })
                        });
                        if (res.ok) window.location.reload(); 
                        else alert("Erro ao atualizar valor");
                    } catch (e) { alert("Erro de conexão"); }
                }

                async function togglePayment(userId, m, y, checkbox) {
                    const originalState = !checkbox.checked;
                    checkbox.disabled = true;
                    const container = checkbox.parentElement;
                    container.classList.add('opacity-50');

                    try {
                        const res = await fetch('api/toggle_payment.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                user_id: userId,
                                month: m,
                                year: y,
                                is_quota_paid: checkbox.checked
                            })
                        });

                        if (res.ok) {
                            if (checkbox.checked) {
                                container.classList.remove('border-slate-200');
                                container.classList.add('border-green-200', 'bg-green-50');
                            } else {
                                container.classList.add('border-slate-200');
                                container.classList.remove('border-green-200', 'bg-green-50');
                            }
                            window.location.reload();
                        } else {
                            alert('Erro ao atualizar');
                            checkbox.checked = originalState;
                        }
                    } catch (e) {
                        alert('Erro de conexão');
                        checkbox.checked = originalState;
                    } finally {
                        checkbox.disabled = false;
                        container.classList.remove('opacity-50');
                    }
                }
            </script>


        <?php else: ?>
            <!-- User List -->
            <div class="space-y-3">
                <?php if (empty($receitas_list)): ?>
                    <div class="text-center py-10 text-slate-400">Nenhum registro encontrado.</div>
                <?php endif; ?>

                <?php foreach ($receitas_list as $r): ?>
                    <div
                        class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-700"><?php echo $r['descricao']; ?></p>
                            <p class="text-xs text-slate-400"><?php echo date('d/m/Y', strtotime($r['data'])); ?></p>
                            <?php if ($r['obs']): ?>
                                <p class="text-xs text-orange-500 mt-1"><?php echo $r['obs']; ?></p><?php endif; ?>
                        </div>
                        <span class="font-bold text-green-700">R$ <?php echo number_format($r['valor'], 2, ',', '.'); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require 'src/navbar.php'; ?>
</body>

</html>