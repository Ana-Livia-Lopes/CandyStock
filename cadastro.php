<?php
include 'php/features.php';

if (Usuario::hasSessao()) {
    header("Location: index.php");
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (
        isset($_POST['nome']) &&
        isset($_POST['email']) &&
        isset($_POST['telefone']) &&
        isset($_POST['senha'])
    ) {
        $resultado = Usuario::registar(
            $_POST['nome'],
            $_POST['telefone'],
            $_POST['email'],
            $_POST['senha'],
        );

        if ($resultado->erro != null) {
            $erro = $resultado->erro;
        } else {
            header("Location: index.php");
        }
    } else {
        $erro = "Parâmetros insuficientes para cadastrar";
    }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Cadastro</title>
    <link rel="stylesheet" href="./css/login.css">
</head>

<body>
    <script>
        <?php
            if ($erro) {
                echo "Swal.fire({
                    icon: 'error',
                    title: 'Erro ao cadastrar',
                    text: '$erro'
                })";
            }
        ?>
    </script>

    <img src="./img/bordalogin_img.png" alt="borda decorativa" class="borda borda-esquerda">
    <img src="./img/bordalogin_img.png" alt="borda decorativa" class="borda borda-direita">

    <div class="container">
        <div class="logo">
            <img src="./img/logo-candy-senfundo.png" alt="Logo Candystock">
            <h1>Cadastrar</h1>
        </div>

        <form action="cadastro.php" method="POST">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" maxlength="256" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" maxlength="24" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit" class="btn-entrar">
                Cadastrar
            </button>


            <p class="cadastro">
                Já possui conta? <a href="./login.php">Entrar</a>
            </p>
        </form>

    </div>

</body>

</html>