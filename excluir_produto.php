<?php

include 'php/features.php';

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    jsonResponse(
        "error",
        "Método inválido (POST obrigatório)",
    );
}

if (!isset($_POST['id'])) {
    jsonResponse(
        "error",
        "ID do produto é obrigatório",
    );
}

if (!Usuario::hasSessao()) {
    jsonResponse(
        "error",
        "Usuário não autenticado",
    );
}

$id = intval($_POST['id']);
if ($id <= 0) {
    jsonResponse(
        "error",
        "ID do produto inválido",
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

// Excluir o produto
$resultado = $produto->excluir();

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
