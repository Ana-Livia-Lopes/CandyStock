<?php

include 'php/features.php';

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    jsonResponse(
        "error",
        "Método inválida (POST obrigatório)",
    );
}

if (
    !isset($_POST['nome']) ||
    !isset($_POST['descricao']) ||
    !isset($_POST['preco']) ||
    !isset($_POST['estoque']) ||
    !isset($_FILES['imagem'])
) {
    jsonResponse(
        "error",
        "Parâmetros insuficientes para adicionar produto",
    );
}

if (!Usuario::hasSessao()) {
    jsonResponse(
        "error",
        "Usuário não autenticado",
    );
}

Produto::criar(
    $_POST['nome'],
    $_POST['descricao'],
    floatval($_POST['preco']),
    intval($_POST['estoque']),
    $_FILES['imagem']['tmp_name'],
);

jsonResponse(
    "success",
    "Produto adicionado com sucesso",
);