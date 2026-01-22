<?php require 'src/head.php';
require 'src/auth.php';
requireAdmin(); ?>

<body class="bg-slate-50 pb-24">

    <header class="bg-white p-4 shadow-sm sticky top-0 z-10">
        <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <ion-icon name="people" class="text-brand-600"></ion-icon> Gerenciar Usuários
        </h1>
    </header>

    <main class="p-4 space-y-4" id="lista">
        <div class="text-center py-10 text-slate-400">
            <ion-icon name="sync" class="animate-spin text-2xl"></ion-icon>
        </div>
    </main>

    <!-- Add User Button (Not fully implemented on API create_user side, uses Regiser Page) -->
    <!-- Or we just link to Register? Prompt said "Criar e remover" -->
    <!-- Let's add a button that goes to a register page or simple modal for creating user -->
    <!-- Since I used standard register.php, I can reuse it or create a simple modal here calling register API -->
    <!-- But Register API auto-logs in? Not necessarily if called via fetch. -->
    <!-- Actually Register API auto-logs in if success? No, it just inserts. Login API sets session. -->
    <!-- So I can use Register API here to create users without logging in as them. -->

    <button onclick="openModal()"
        class="fixed bottom-20 right-4 bg-brand-600 hover:bg-brand-700 text-white w-14 h-14 rounded-full shadow-lg shadow-brand-600/30 flex items-center justify-center transition-transform active:scale-90 z-20">
        <ion-icon name="person-add" class="text-2xl"></ion-icon>
    </button>

    <!-- Modal Create User -->
    <div id="modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4 text-slate-800" id="modalTitle">Novo Usuário</h2>
            <form id="formUser" class="space-y-4">
                <input type="hidden" name="id" id="userId">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome</label>
                    <input type="text" name="nome" required
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">CPF</label>
                    <input type="text" name="cpf" required
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Início da Cobrança</label>
                    <input type="date" name="debt_start_date"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Antes desta data, não gera atraso.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Senha (Deixe em branco p/
                        manter)</label>
                    <input type="text" name="senha" minlength="6"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal()"
                        class="flex-1 py-3 text-slate-600 font-medium hover:bg-slate-50 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit"
                        class="flex-1 py-3 bg-brand-600 text-white font-bold rounded-xl shadow-lg hover:bg-brand-700 transition-transform active:scale-95">Criar</button>
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

                let html = '';
                data.forEach(user => {
                    const saldo = parseFloat(user.saldo);
                    const saldoFmt = saldo.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    const saldoClass = saldo >= 0 ? 'text-green-600' : 'text-red-600';
                    const statusIcon = saldo >= 0 ? 'checkmark-circle' : 'alert-circle';

                    // Edit Button for everyone (Admin view)
                    const editBtn = `<button onclick='editUser(${JSON.stringify(user)})' class="text-slate-400 hover:text-blue-600 p-2"><ion-icon name="create-outline"></ion-icon></button>`;

                    // Only show delete if not self
                    const deleteBtn = user.id != <?php echo $_SESSION['user_id']; ?> ?
                        `<button onclick="deleteUser(${user.id})" class="text-slate-400 hover:text-red-600 p-2"><ion-icon name="trash"></ion-icon></button>` : '';

                    html += `
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex justify-between items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800">${user.nome}</span>
                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500">${user.role}</span>
                    </div>
                    <p class="text-xs text-slate-400 mb-1">${user.cpf}</p>
                    <p class="text-xs text-slate-500">Início Cobrança: ${user.debt_start_date || '01/2024'}</p>
                    <p class="text-sm font-bold ${saldoClass} flex items-center gap-1">
                        <ion-icon name="${statusIcon}"></ion-icon> ${saldoFmt}
                    </p>
                </div>
                <div class="flex">
                    ${editBtn}
                    ${deleteBtn}
                </div>
            </div>`;
                });
                lista.innerHTML = html;

            } catch (e) {
                lista.innerHTML = '<p class="text-center text-red-500">Erro ao carregar.</p>';
            }
        }

        async function deleteUser(id) {
            if (!confirm('Excluir este usuário e TODO os dados dele?')) return;
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
            document.querySelector('[name="senha"]').required = false; // Password optional on edit
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
                    alert('Salvo com sucesso!');
                } else {
                    alert(json.error);
                }
            } catch (e) { alert('Erro'); }
        });
    </script>
</body>

</html>