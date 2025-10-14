<?php

include 'php/features.php';

if (!isset($_GET['id'])) {
    jsonResponse(
        "error",
        "Parâmetro insuficiente para buscar comentários",
    );
}

$produto = Produto::byId(intval($_GET['id']));

if ($produto == null) {
    jsonResponse(
        "error",
        "Produto não encontrado",
    );
}

$comentarios = [];

foreach ($produto->getComentarios() as $comentario) {
    array_push($comentarios, $comentario->toJSON());
}

jsonResponse(
    "success",
    "Comentários de produto carregados com sucesso",
    $comentarios
);