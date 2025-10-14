<?php

include 'php/features.php';

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    jsonResponse(
        "error",
        "Método inválido (POST obrigatório)",
    );
}

if (
    !isset($_POST['conteudo']) ||
    !isset($_POST['id_produto'])
) {
    jsonResponse(
        "error",
        "Parâmetros insuficientes (conteudo e id_produto obrigatórios)",
    );
}

$id_produto = intval($_POST['id_produto']);
$conteudo = $_POST['conteudo'];

$resultado = Produto::adicionarComentario($id_produto, $conteudo);

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