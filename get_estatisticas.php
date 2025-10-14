<?php

include 'php/features.php';

if (!Usuario::hasSessao()) {
    jsonResponse(
        "error",
        "Usuário não autenticado",
    );
}

// Buscar todos os produtos para calcular estatísticas
$produtos = Produto::pesquisar(ativo: true);

$total = count($produtos);
$semFalta = 0;
$emFalta = 0;

foreach ($produtos as $produto) {
    if ($produto->hasEstoqueBaixo()) {
        $emFalta++;
    } else {
        $semFalta++;
    }
}

jsonResponse(
    "success",
    "Estatísticas obtidas com sucesso",
    [
        "total" => $total,
        "sem_falta" => $semFalta,
        "em_falta" => $emFalta
    ]
);
