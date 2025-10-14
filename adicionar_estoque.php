<?php

include 'php/features.php';

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    jsonResponse(
        "error",
        "Método inválido (POST obrigatório)",
    );
}

if (
    !isset($_POST['id']) ||
    !isset($_POST['quantidade'])
) {
    jsonResponse(
        "error",
        "ID do produto e quantidade são obrigatórios",
    );
}

if (!Usuario::hasSessao()) {
    jsonResponse(
        "error",
        "Usuário não autenticado",
    );
}

$id = intval($_POST['id']);
$quantidade = intval($_POST['quantidade']);

if ($id <= 0) {
    jsonResponse(
        "error",
        "ID do produto inválido",
    );
}

if ($quantidade <= 0) {
    jsonResponse(
        "error",
        "Quantidade deve ser maior que zero",
    );
}

// Buscar o produto
$produto = Produto::byId($id);
if ($produto === null) {
    jsonResponse(
        "error",
        "Produto não encontrado",
    );
}

// Adicionar quantidade ao estoque
$resultado = $produto->adicionar($quantidade);

if ($resultado->sucesso) {
    jsonResponse(
        "success",
        $resultado->mensagem,
    );
} else {
    jsonResponse(
        "error",
        $resultado->mensagem,
    );
}
