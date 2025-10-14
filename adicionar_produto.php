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

$resultado = Produto::criar(
    $_POST['nome'],
    $_POST['descricao'],
    floatval($_POST['preco']),
    intval($_POST['estoque']),
    $_FILES['imagem']['tmp_name'],
);

if ($resultado->produto) {
    $produto = $resultado->produto;
    jsonResponse(
        "success",
        "Produto adicionado com sucesso",
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
} else {
    jsonResponse(
        "error",
        $resultado->erro ?? "Erro ao criar produto",
    );
}