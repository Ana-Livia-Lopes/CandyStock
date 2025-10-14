<?php
include 'php/features.php';

if (Usuario::hasSessao()) {
    header("Location: index.php");
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (
        isset($_POST['email']) &&
        isset($_POST['senha'])
    ) {
        $resultado = Usuario::entrar(
            $_POST['email'],
            $_POST['senha']
        );

        if ($resultado->erro != null) {
            $erro = $resultado->erro;
        } else {
            header("Location: index.php");
        }
    } else {
        $erro = "Campos faltando";
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
  <title>Login</title>
  <link rel="stylesheet" href="./css/login.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

  <img src="./img/bordalogin_img.png" alt="borda decorativa" class="borda borda-esquerda">
  <img src="./img/bordalogin_img.png" alt="borda decorativa" class="borda borda-direita">

  <div class="container">
    <div class="logo">
      <img src="./img/logo-candy-senfundo.png" alt="Logo Candystock">
      <h1>Entrar</h1>
    </div>

    <form action="login.php" method="POST">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>

      <label for="senha">Senha:</label>
      <input type="password" id="senha" name="senha" required>

      <div class="opcoes">
        <a href="esqueceuSenha.php">Esqueceu a senha?</a>
      </div>

      <button type="submit" class="btn-entrar">
        Entrar
      </button>

      <p class="cadastro">
        Não possui conta? <a href="./cadastro.php">Cadastrar</a>
      </p>
    </form>
  </div>

</body>

</html>