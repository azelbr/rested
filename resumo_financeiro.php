<?php
require_once 'src/db.php';
require_once 'src/auth.php';
require_once 'src/utils.php';

requireLogin();

$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

// Calculate Summaries
// Total Receitas Month
$stmt = $pdo->prepare("SELECT SUM(valor) FROM receitas WHERE MONTH(data) = ? AND YEAR(data) = ?");
$stmt->execute([$month, $year]);
$total_receitas = $stmt->fetchColumn() ?: 0;

// Total Despesas Month
$stmt = $pdo->prepare("SELECT SUM(valor) FROM despesas WHERE MONTH(data) = ? AND YEAR(data) = ?");
$stmt->execute([$month, $year]);
$total_despesas = $stmt->fetchColumn() ?: 0;

// Previous Balance (Start counting from 2025-01-01)
$sql_ant_rec = "SELECT SUM(valor) FROM receitas WHERE data < DATE(CONCAT(?, '-', ?, '-01')) AND data >= '2025-01-01'";
$stmt = $pdo->prepare($sql_ant_rec);
$stmt->execute([$year, $month]);
$ant_rec = $stmt->fetchColumn() ?: 0;

$sql_ant_desp = "SELECT SUM(valor) FROM despesas WHERE data < DATE(CONCAT(?, '-', ?, '-01')) AND data >= '2025-01-01'";
$stmt = $pdo->prepare($sql_ant_desp);
$stmt->execute([$year, $month]);
$ant_desp = $stmt->fetchColumn() ?: 0;

// Fetch Initial System Balance (History)
$stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'saldo_inicial_sistema'");
$saldo_inicial_sistema = (float) ($stmt->fetchColumn() ?: 0);

$saldo_anterior = $saldo_inicial_sistema + $ant_rec - $ant_desp;
$saldo_atual = $saldo_anterior + $total_receitas - $total_despesas;

require 'src/head.php';
?>

<body class="bg-gray-100 min-h-screen pb-24 font-sans overflow-y-auto">
    <header class="bg-blue-800 text-white p-4 shadow-md sticky top-0 z-10 flex justify-between items-center">
        <a href="dashboard.php"><ion-icon name="arrow-back" class="text-2xl"></ion-icon></a>
        <h1 class="font-bold text-lg">Resumo Financeiro</h1>
        <div class="w-6"></div>
    </header>

    <div class="p-4">
        <!-- Filter -->
        <div class="bg-white p-3 rounded-xl shadow-sm mb-4 flex justify-center gap-2">
            <select name="month" onchange="window.location.href='?month='+this.value+'&year=<?php echo $year; ?>'"
                class="bg-slate-100 p-2 rounded-lg text-sm">
                <?php for ($i = 1; $i <= 12; $i++)
                    echo "<option value='$i' " . ($month == $i ? 'selected' : '') . ">" . date('M', mktime(0, 0, 0, $i, 1)) . "</option>"; ?>
            </select>
            <select name="year" onchange="window.location.href='?month=<?php echo $month; ?>&year='+this.value"
                class="bg-slate-100 p-2 rounded-lg text-sm">
                <?php $cy = date('Y');
                for ($i = $cy; $i >= $cy - 1; $i--)
                    echo "<option value='$i' " . ($year == $i ? 'selected' : '') . ">$i</option>"; ?>
            </select>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
            <table class="w-full text-sm text-left">
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-blue-50">
                        <td class="p-4 font-medium text-slate-700 flex items-center gap-2">
                            Saldo Mês Anterior
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <button onclick="editSaldoInicial()"
                                    class="text-blue-500 hover:text-blue-700 bg-blue-100 p-1 rounded-full"><ion-icon
                                        name="pencil" class="text-xs"></ion-icon></button>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right font-bold text-blue-700">R$
                            <?php echo number_format($saldo_anterior, 2, ',', '.'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-4 font-medium text-slate-700">Receitas</td>
                        <td class="p-4 text-right font-bold text-green-600">R$
                            <?php echo number_format($total_receitas, 2, ',', '.'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-4 font-medium text-slate-700">Despesas</td>
                        <td class="p-4 text-right font-bold text-red-600">R$
                            <?php echo number_format($total_despesas, 2, ',', '.'); ?>
                        </td>
                    </tr>
                    <tr class="bg-slate-50 border-t-2 border-slate-200">
                        <td class="p-4 font-bold text-slate-800 text-lg">Saldo Atual</td>
                        <td
                            class="p-4 text-right font-bold text-lg <?php echo $saldo_atual >= 0 ? 'text-blue-800' : 'text-red-600'; ?>">
                            R$
                            <?php echo number_format($saldo_atual, 2, ',', '.'); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="mt-4 text-xs text-center text-slate-400">
                Saldo Inicial Sistema: R$ <?php echo number_format($saldo_inicial_sistema, 2, ',', '.'); ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Modal Saldo Inicial -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <div id="saldoModal"
            class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl">
                <h2 class="text-xl font-bold mb-4 text-slate-800">Saldo Inicial (Histórico)</h2>
                <p class="text-xs text-slate-500 mb-4">Insira o saldo acumulado de anos anteriores ao sistema.</p>
                <form id="formSaldo" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Valor</label>
                        <input type="number" step="0.01" name="valor" id="saldoInput"
                            value="<?php echo $saldo_inicial_sistema; ?>"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('saldoModal').classList.add('hidden')"
                            class="flex-1 py-2 text-slate-500">Cancelar</button>
                        <button type="submit"
                            class="flex-1 py-2 bg-blue-600 text-white font-bold rounded-lg">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            function editSaldoInicial() {
                document.getElementById('saldoModal').classList.remove('hidden');
            }
            document.getElementById('formSaldo').addEventListener('submit', async (e) => {
                e.preventDefault();
                const val = document.getElementById('saldoInput').value;
                try {
                    const res = await fetch('api/update_settings.php', {
                        method: 'POST',
                        body: JSON.stringify({ key: 'saldo_inicial_sistema', value: val })
                    });
                    if (res.ok) window.location.reload();
                    else alert('Erro ao salvar');
                } catch (e) { alert('Erro de conexão'); }
            });
        </script>
    <?php endif; ?>

    <?php require 'src/navbar.php'; ?>
</body>

</html>