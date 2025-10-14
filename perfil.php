<?php
include 'php/features.php';

// Verificar se o usuário está logado
if (!Usuario::hasSessao()) {
    header('Location: login.php');
    exit;
}

$usuario = Usuario::getSessao();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rethink+Sans:ital,wght@0,400..800;1,400..800&family=Space+Grotesk:wght@300..700&display=swap"
        rel="stylesheet">
    <title>Perfil - CandyStock</title>
    <link rel="stylesheet" href="perfil.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="menu">
        <div>
            <img src="./img/logo-candy-senfundo.png" alt="logo" class="logo">
            <p>Perfil</p>
        </div>

        <i class="fa-solid fa-bars" onclick="toggleMenu()"></i>

        <ul id="nav-links">
            <li><a href="index.php">Produtos</a></li>
            <li><a href="relatorios.php">Relatórios</a></li>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="sair.php">Sair</a></li>
        </ul>
    </div>


    <div class="container-perfil">
        <div class="perfil-esquerda">
            <img src="./img/logo-candy-branca.png" alt="logo versão branca" class="mini-logo">
        </div>

        <div class="perfil-direita">
            <h2>Editar perfil</h2>
            <form id="form-perfil">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario->getNome()) ?>" required>

                <label for="telefone">Telefone:</label>
                <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario->getTelefone()) ?>"
                    required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario->getEmail()) ?>"
                    required>

                <label for="senha">Nova Senha (deixe em branco para manter a atual):</label>
                <input type="password" id="senha" name="senha" placeholder="Digite uma nova senha (opcional)">

                <label for="confirmar_senha">Confirmar Nova Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a nova senha">

                <button type="submit" class="btn-salvar">Salvar alterações</button>
            </form>
        </div>
    </div>

    <footer>
        <div id="footer">
            <div class="contato">
                <h2>Informações de Contato</h2>
                <p><strong>Candy Stock:</strong></p>
                <p>Endereço: Av. alguma, 90, Caçapava - SP, 12084-090</p>
                <p>Telefone: (12) 9953-1976</p>
                <p>E-mail: candystock@gmail.com</p>
            </div>

            <div class="equipe">
                <h2>Equipe Desenvolvedora</h2>
                <ul>
                    <p>Ana Lívia dos Santos Lopes</p>
                    <p>Gabriel Reis de Brito</p>
                    <p>Isadora Gomes da Silva</p>
                    <p>Lucas Randal Abreu Balderrama</p>
                    <p>Flavia Glenda Guimarães Carvalho</p>
                    <p>Guilherme Ricardo de Paiva</p>
                </ul>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleMenu() {
            const menu = document.querySelector('.menu ul');
            menu.classList.toggle('active');
        }

        // Validação e envio do formulário
        $('#form-perfil').on('submit', async function (e) {
            e.preventDefault();

            const nome = $('#nome').val().trim();
            const telefone = $('#telefone').val().trim();
            const email = $('#email').val().trim();
            const senha = $('#senha').val();
            const confirmarSenha = $('#confirmar_senha').val();

            // Validações
            if (!nome || !telefone || !email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Validação',
                    text: 'Nome, telefone e email são obrigatórios.'
                });
                return;
            }

            if (senha && senha !== confirmarSenha) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Validação',
                    text: 'As senhas não coincidem.'
                });
                return;
            }

            if (senha && senha.length < 6) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Validação',
                    text: 'A senha deve ter pelo menos 6 caracteres.'
                });
                return;
            }

            // Enviar dados
            try {
                const formData = new FormData();
                formData.append('nome', nome);
                formData.append('telefone', telefone);
                formData.append('email', email);
                if (senha) {
                    formData.append('senha', senha);
                }

                const response = await fetch('./editar_perfil.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message
                    }).then(() => {
                        // Limpar campos de senha
                        $('#senha').val('');
                        $('#confirmar_senha').val('');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.message
                    });
                }
            } catch (error) {
                console.log(error)
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Ocorreu um erro ao atualizar o perfil.'
                });
            }
        });
    </script>
</body>

</html>