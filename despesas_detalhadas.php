<?php
require_once 'src/db.php';
require_once 'src/auth.php';
require_once 'src/utils.php';

requireLogin();

$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

$stmt = $pdo->prepare("SELECT * FROM despesas WHERE MONTH(data) = ? AND YEAR(data) = ? ORDER BY data DESC");
$stmt->execute([$month, $year]);
$despesas = $stmt->fetchAll();

$total = 0;
foreach ($despesas as $d)
    $total += $d['valor'];

require 'src/head.php';
?>

<body class="bg-gray-100 min-h-screen pb-24 font-sans overflow-y-auto">
    <header class="bg-orange-600 text-white p-4 shadow-md sticky top-0 z-10 flex justify-between items-center">
        <a href="dashboard.php"><ion-icon name="arrow-back" class="text-2xl"></ion-icon></a>
        <h1 class="font-bold text-lg">Despesas</h1>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <button onclick="document.getElementById('despesaModal').classList.remove('hidden')"
                class="bg-white/20 p-1 rounded hover:bg-white/30"><ion-icon name="add" class="text-xl"></ion-icon></button>
        <?php else: ?>
            <div class="w-6"></div>
        <?php endif; ?>
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

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3">Descrição / Obs</th>
                        <th class="p-3 text-right">Valor</th>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <th class="p-3 w-10"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($despesas as $d): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3">
                                <div class="font-bold text-slate-700">
                                    <?php echo $d['descricao']; ?>
                                </div>
                                <?php if ($d['obs']): ?>
                                    <div class="text-xs text-orange-600">
                                        <?php echo $d['obs']; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="text-[10px] text-slate-400">
                                    <?php echo date('d/m', strtotime($d['data'])); ?> •
                                    <?php echo $d['categoria']; ?>
                                </div>
                            </td>
                            <td class="p-3 text-right font-bold text-slate-800">R$
                                <?php echo number_format($d['valor'], 2, ',', '.'); ?>
                            </td>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <td class="p-3 text-right flex gap-2 justify-end">
                                    <button onclick='editDespesa(<?php echo json_encode($d); ?>)'
                                        class="text-blue-500 hover:text-blue-700">
                                        <ion-icon name="create-outline" class="text-lg"></ion-icon>
                                    </button>
                                    <button onclick='deleteDespesa(<?php echo $d['id']; ?>)'
                                        class="text-red-500 hover:text-red-700">
                                        <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-orange-50 border-t border-orange-100">
                    <tr>
                        <td class="p-3 font-bold text-orange-800 text-right uppercase text-xs">Total</td>
                        <td class="p-3 font-bold text-orange-700 text-right">R$
                            <?php echo number_format($total, 2, ',', '.'); ?>
                        </td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <td></td><?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Modal Logic -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <div id="despesaModal"
            class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl">
                <h2 class="text-xl font-bold mb-4 text-slate-800" id="modalTitle">Nova Despesa</h2>
                <form id="formDespesa" class="space-y-3">
                    <input type="hidden" name="id" id="despesaId">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Data</label>
                        <input type="date" name="data" id="despesaData"
                            value="<?php echo "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01"; ?>"
                            class="w-full border-b border-slate-200 focus:border-red-500 outline-none py-2" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Descrição</label>
                        <input type="text" name="descricao" id="despesaDescricao"
                            class="w-full border-b border-slate-200 focus:border-red-500 outline-none py-2" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Categoria</label>
                        <input type="text" name="categoria" id="despesaCategoria" list="catList"
                            class="w-full border-b border-slate-200 focus:border-red-500 outline-none py-2">
                        <datalist id="catList">
                            <option value="Água">
                            <option value="Luz">
                            <option value="Manutenção">
                        </datalist>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Obs</label>
                        <input type="text" name="obs" id="despesaObs"
                            class="w-full border-b border-slate-200 focus:border-red-500 outline-none py-2">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Valor</label>
                        <input type="number" step="0.01" name="valor" id="despesaValor"
                            class="w-full border-b border-slate-200 focus:border-red-500 outline-none py-2" required>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeModal()" class="flex-1 py-2 text-slate-500">Cancelar</button>
                        <button type="submit"
                            class="flex-1 py-2 bg-orange-600 text-white font-bold rounded-lg">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            function closeModal() {
                document.getElementById('despesaModal').classList.add('hidden');
                document.getElementById('formDespesa').reset();
                document.getElementById('despesaId').value = '';
                document.getElementById('modalTitle').innerText = 'Nova Despesa';
            }

            function editDespesa(item) {
                document.getElementById('despesaModal').classList.remove('hidden');
                document.getElementById('modalTitle').innerText = 'Editar Despesa';
                document.getElementById('despesaId').value = item.id;
                document.getElementById('despesaData').value = item.data;
                document.getElementById('despesaDescricao').value = item.descricao;
                document.getElementById('despesaCategoria').value = item.categoria;
                document.getElementById('despesaObs').value = item.obs;
                document.getElementById('despesaValor').value = item.valor;
            }

            async function deleteDespesa(id) {
                if (!confirm('Tem certeza que deseja excluir esta despesa?')) return;
                try {
                    const res = await fetch('api/delete_despesa.php', {
                        method: 'POST',
                        body: JSON.stringify({ id })
                    });
                    if (res.ok) window.location.reload();
                    else alert('Erro ao excluir');
                } catch (e) { alert('Erro de conexão'); }
            }

            document.getElementById('formDespesa').addEventListener('submit', async (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target));
                const endpoint = data.id ? 'api/update_despesa.php' : 'api/create_despesa.php';

                try {
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        body: JSON.stringify(data)
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