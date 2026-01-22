<?php require 'src/head.php';
require 'src/auth.php';
requireAdmin(); ?>

<body class="bg-gray-100 min-h-screen pb-24 font-sans overflow-y-auto">

    <!-- Header Customizado (Match Dashboard) -->
    <header class="bg-blue-900 text-white p-6 rounded-b-3xl shadow-lg relative z-10 flex justify-between items-center">
        <div>
            <h1 class="font-bold text-lg flex items-center gap-2">
                <ion-icon name="people" class="text-xl"></ion-icon> Gerenciar Usuários
            </h1>
            <p class="text-sm text-blue-200">Administração de Moradores</p>
        </div>
        <button onclick="openModal()"
            class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-xl flex items-center gap-2 transition-colors">
            <ion-icon name="person-add" class="text-xl"></ion-icon>
            <span class="text-sm font-medium hidden sm:inline">Adicionar</span>
        </button>
    </header>

    <main class="p-4 -mt-4 relative z-20 space-y-4">
        <!-- Grid Container -->
        <div id="lista" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-w-6xl mx-auto">
            <div class="col-span-full text-center py-10 text-slate-400">
                <ion-icon name="sync" class="animate-spin text-2xl"></ion-icon>
            </div>
        </div>
    </main>

    <!-- Modal Create/Edit User -->
    <div id="modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4 text-slate-800" id="modalTitle">Novo Usuário</h2>
            <form id="formUser" class="space-y-4">
                <input type="hidden" name="id" id="userId">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome (Identificador)</label>
                    <input type="text" name="nome" placeholder="Ex: Sobrado 99" required
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">CPF (Opcional/Obs)</label>
                    <input type="text" name="cpf" placeholder="000.000.000-00" required
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Início da Cobrança</label>
                    <input type="date" name="debt_start_date"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Dívidas anteriores a esta data serão ignoradas.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Senha</label>
                    <input type="text" name="senha" minlength="6" placeholder="Deixe em branco para manter"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-blue-500 outline-none">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal()"
                        class="flex-1 py-3 text-slate-600 font-medium hover:bg-slate-50 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit"
                        class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg hover:bg-blue-700 transition-transform active:scale-95">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <?php require 'src/navbar.php'; ?>

    <!-- Custom Scripts -->
    <script>
        loadItems();

        async function loadItems() {
            const lista = document.getElementById('lista');
            try {
                const res = await fetch('api/list_usuarios.php');
                const data = await res.json();

                if (data.length === 0) {
                    lista.innerHTML = '<div class="col-span-full text-center py-10 text-slate-400">Nenhum usuário encontrado.</div>';
                    return;
                }

                let html = '';
                data.forEach(user => {
                    const saldo = parseFloat(user.saldo || 0);
                    const saldoFmt = saldo.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    const isGood = saldo >= 0;
                    const saldoClass = isGood ? 'text-green-600' : 'text-red-600';
                    const icon = isGood ? 'shield-checkmark' : 'alert-circle';

                    const editBtn = `<button onclick='editUser(${JSON.stringify(user)})' class="p-2 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"><ion-icon name="create-outline" class="text-lg"></ion-icon></button>`;

                    const deleteBtn = user.id != <?php echo $_SESSION['user_id']; ?> ?
                        `<button onclick="deleteUser(${user.id})" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"><ion-icon name="trash-outline" class="text-lg"></ion-icon></button>` : '';

                    html += `
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col gap-3 relative group hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-sm">
                                    ${user.nome.substring(0, 2).toUpperCase()}
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 leading-tight">${user.nome}</h3>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wide font-bold">${user.role}</p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                ${editBtn}
                                ${deleteBtn}
                            </div>
                        </div>

                        <div class="space-y-1 my-1">
                            <p class="text-xs text-slate-500 flex items-center gap-1.5" title="CPF">
                                <ion-icon name="id-card-outline" class="text-slate-400"></ion-icon> 
                                ${user.cpf || '---'}
                            </p>
                            <p class="text-xs text-slate-500 flex items-center gap-1.5" title="Início da Cobrança">
                                <ion-icon name="calendar-outline" class="text-slate-400"></ion-icon> 
                                ${user.debt_start_date ? new Date(user.debt_start_date).toLocaleDateString('pt-BR', { timeZone: 'UTC' }) : '01/2024'}
                            </p>
                        </div>

                        <div class="mt-auto pt-3 border-t border-slate-50 flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-400">Saldo</span>
                            <span class="font-bold text-sm ${saldoClass} flex items-center gap-1">
                                ${saldoFmt}
                                <ion-icon name="${icon}"></ion-icon>
                            </span>
                        </div>
                    </div>`;
                });
                lista.innerHTML = html;

            } catch (e) {
                console.error(e);
                lista.innerHTML = '<div class="col-span-full text-center text-red-500 py-10">Erro ao carregar usuários.</div>';
            }
        }

        async function deleteUser(id) {
            if (!confirm('Tem certeza? Isso apagará TODO o histórico do usuário.')) return;
            try {
                const res = await fetch('api/delete_usuario.php', {
                    method: 'POST',
                    body: JSON.stringify({ id })
                });
                loadItems();
            } catch (e) { alert('Erro ao excluir'); }
        }

        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').innerText = 'Novo Usuário';
            document.getElementById('formUser').reset();
            document.getElementById('userId').value = '';
        }

        function closeModal() { document.getElementById('modal').classList.add('hidden'); }

        function editUser(user) {
            openModal();
            document.getElementById('modalTitle').innerText = 'Editar Usuário';
            document.getElementById('userId').value = user.id;
            document.querySelector('[name="nome"]').value = user.nome;
            document.querySelector('[name="cpf"]').value = user.cpf;
            document.querySelector('[name="senha"]').required = false;
            document.querySelector('[name="debt_start_date"]').value = user.debt_start_date || '2024-01-01';
        }

        document.getElementById('formUser').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const endpoint = data.id ? 'api/update_user.php' : 'api/register.php';

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                const json = await res.json();
                if (res.ok) {
                    closeModal();
                    loadItems();
                    e.target.reset();
                } else {
                    alert(json.error || 'Erro ao salvar');
                }
            } catch (e) { alert('Erro de conexão'); }
        });
    </script>
</body>

</html>