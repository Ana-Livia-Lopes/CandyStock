<?php
include 'php/features.php';

// Se o usuário já está logado, redirecionar para index
if (Usuario::hasSessao()) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <title>Redefinir senha - CandyStock</title>
    <link rel="stylesheet" href="login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <img src="./img/bordalogin_img.png" alt="borda decorativa" class="borda borda-esquerda">
    <img src="./img/bordalogin_img.png" alt="borda decorativa" class="borda borda-direita">

    <div class="container">
        <div class="logo">
            <img src="./img/logo-candy-senfundo.png" alt="Logo Candystock">
            <h1>Redefinir senha</h1>
        </div>

        <form id="form-redefinir-senha">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="senha">Nova senha:</label>
            <input type="password" id="senha" name="senha" required>

            <label for="confirmar-senha">Confirmar senha:</label>
            <input type="password" id="confirmar-senha" name="confirmar-senha" required>

            <button type="submit" class="btn-entrar">Confirmar</button>

        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: #864B93; text-decoration: none;">Voltar ao login</a>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#form-redefinir-senha').on('submit', async function(e) {
            e.preventDefault();

            const email = $('#email').val().trim();
            const senha = $('#senha').val();
            const confirmarSenha = $('#confirmar-senha').val();

            // Validações
            if (!email || !senha || !confirmarSenha) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Validação',
                    text: 'Todos os campos são obrigatórios.'
                });
                return;
            }

            if (senha !== confirmarSenha) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Validação',
                    text: 'As senhas não coincidem.'
                });
                return;
            }

            if (senha.length < 6) {
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
                formData.append('email', email);
                formData.append('senha', senha);

                const response = await fetch('./redefinir_senha.php', {
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
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Ocorreu um erro ao redefinir a senha.'
                });
            }
        });
    </script>

</body>

</html>
