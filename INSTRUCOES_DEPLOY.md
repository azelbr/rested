# Como colocar o site no seu Ubuntu Server (VirtualBox)

Como seu servidor é uma Máquina Virtual (VM) no seu próprio computador, você não precisa necessariamente do GitHub. Existem duas formas principais de fazer isso:

## Opção 1: Pastas Compartilhadas (Recomendado - Automático)
Nesta opção, a pasta do seu Windows "aparece" dentro do Linux. Tudo que você muda no Windows atualiza na hora no servidor.

1.  **No VirtualBox**:
    *   Vá nas **Configurações** da sua VM > **Pastas Compartilhadas**.
    *   Clique no ícone **+**.
    *   **Caminho da Pasta**: Escolha a pasta do projeto no Windows (`C:\Users\azel_\Documentos\programação\residencialtedesco`).
    *   **Nome da Pasta**: `projeto_site` (exemplo).
    *   Marque: [x] Montar Automaticamente e [x] Tornar Permanente.

2.  **No Ubuntu Server (Terminal)**:
    *   Instale os adicionais de convidado (se não tiver).
    *   Adicione seu usuário ao grupo do VirtualBox:
        ```bash
        sudo usermod -aG vboxsf $(whoami)
        ```
    *   A pasta costuma aparecer em `/media/sf_projeto_site`.
    *   Você pode criar um link simbólico para o Apache/Nginx:
        ```bash
        sudo ln -s /media/sf_projeto_site /var/www/html/residencial
        ```

---

## Opção 2: SFTP (Manual - Cópia de Arquivos)
Se você não quiser configurar pastas compartilhadas, pode usar um programa como **FileZilla** ou **WinSCP** para jogar os arquivos do Windows para o Linux via rede.

1.  **Descubra o IP da VM**: No terminal do Ubuntu digite `ip addr` ou `ifconfig`.
2.  **No FileZilla (Windows)**:
    *   **Host**: sftp://<IP_DA_VM>
    *   **Usuário/Senha**: Os mesmos que usa para entrar no Ubuntu.
3.  **Transferir**:
    *   Arraste os arquivos da sua pasta local para `/var/www/html` no servidor.
    *   *Dica*: Talvez precise alterar as permissões da pasta `/var/www/html` no Linux para seu usuário poder escrever lá (`sudo chown -R seu_usuario /var/www/html`).

---

## ⚠️ Atenção ao `.env`
O arquivo `.env` (onde estão as senhas) é **oculto**.
*   Se usar **Pastas Compartilhadas**: Ele já estará lá.
*   Se usar **SFTP**: Configure o FileZilla para "Mostrar arquivos ocultos" para garantir que você copiou ele também.

## ⚠️ Permissões de Upload
Para que o upload de documentos funcione, a pasta `uploads` precisa ter permissão de escrita no Linux:
```bash
chmod 777 uploads/
```
*(Ou dar permissão específica para o usuário do servidor web `www-data`)*
