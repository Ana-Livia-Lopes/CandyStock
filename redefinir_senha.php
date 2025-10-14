<?php

include 'php/features.php';

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    jsonResponse(
        "error",
        "Método inválido (POST obrigatório)",
    );
}

if (
    !isset($_POST['email']) ||
    !isset($_POST['senha'])
) {
    jsonResponse(
        "error",
        "Email e senha são obrigatórios",
    );
}

$email = $_POST['email'];
$senha = $_POST['senha'];

$resultado = Usuario::redefinirSenha($email, $senha);

if ($resultado->sucesso) {
    jsonResponse(
        "success",
        $resultado->mensagem . " Você pode fazer login agora.",
    );
} else {
    jsonResponse(
        "error",
        $resultado->mensagem,
    );
}
