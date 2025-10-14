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

// Preparar parâmetros para edição
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : null;
$descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : null;
$preco = isset($_POST['preco']) ? floatval($_POST['preco']) : null;
$ativo = isset($_POST['ativo']) ? ($_POST['ativo'] === 'true' || $_POST['ativo'] === '1') : null;

// Validar valores se foram fornecidos
if ($nome !== null && empty($nome)) {
    jsonResponse(
        "error",
        "Nome do produto não pode ser vazio",
    );
}

if ($descricao !== null && empty($descricao)) {
    jsonResponse(
        "error",
        "Descrição do produto não pode ser vazia",
    );
}

if ($preco !== null && $preco < 0) {
    jsonResponse(
        "error",
        "Preço não pode ser negativo",
    );
}

// Tratar upload de imagem se fornecida
$caminhoImg = null;
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $caminhoImg = $_FILES['imagem']['tmp_name'];
}

// Editar o produto
$resultado = $produto->editar($nome, $descricao, $caminhoImg, $preco, $ativo);

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
