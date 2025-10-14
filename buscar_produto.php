<?php

include 'php/features.php';

if ($_SERVER['REQUEST_METHOD'] != "GET") {
    jsonResponse(
        "error",
        "Método inválido (GET obrigatório)",
    );
}

if (!isset($_GET['id'])) {
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

$id = intval($_GET['id']);
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

jsonResponse(
    "success",
    "Produto encontrado",
    [
        "id" => $produto->getId(),
        "nome" => $produto->getNome(),
        "descricao" => $produto->getDescricao(),
        "preco" => $produto->getPreco(),
        "estoque" => $produto->getEstoque(),
        "ativo" => $produto->getAtivo(),
        "imagem" => $produto->getImagem()->getCaminho()
    ]
);
