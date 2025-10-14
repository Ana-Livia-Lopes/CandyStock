<?php

include 'php/features.php';

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    jsonResponse(
        "error",
        "Método inválido (POST obrigatório)",
    );
}

if (!Usuario::hasSessao()) {
    jsonResponse(
        "error",
        "Usuário não autenticado",
    );
}

$usuario = Usuario::getSessao();

// Validar campos obrigatórios
if (
    !isset($_POST['nome']) ||
    !isset($_POST['telefone']) ||
    !isset($_POST['email'])
) {
    jsonResponse(
        "error",
        "Nome, telefone e email são obrigatórios",
    );
}

$nome = trim($_POST['nome']);
$telefone = trim($_POST['telefone']);
$email = trim($_POST['email']);
$senha = isset($_POST['senha']) ? trim($_POST['senha']) : null;

// Validações
if (empty($nome)) {
    jsonResponse(
        "error",
        "Nome não pode estar vazio",
    );
}

if (empty($telefone)) {
    jsonResponse(
        "error",
        "Telefone não pode estar vazio",
    );
}

if (empty($email)) {
    jsonResponse(
        "error",
        "Email não pode estar vazio",
    );
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(
        "error",
        "Email inválido",
    );
}

if ($senha !== null && strlen($senha) < 6) {
    jsonResponse(
        "error",
        "A senha deve ter pelo menos 6 caracteres",
    );
}

// Verificar se o email já está sendo usado por outro usuário
global $conn;
$sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    jsonResponse(
        "error",
        "Erro na preparação da consulta",
    );
}

$id = $usuario->id;
$stmt->bind_param("si", $email, $id);
if (!$stmt->execute()) {
    jsonResponse(
        "error",
        "Erro ao verificar email",
    );
}

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    jsonResponse(
        "error",
        "Este email já está sendo usado por outro usuário",
    );
}

// Determinar quais campos foram alterados
$nomeAlterado = $nome !== $usuario->getNome() ? $nome : null;
$telefoneAlterado = $telefone !== $usuario->getTelefone() ? $telefone : null;
$emailAlterado = $email !== $usuario->getEmail() ? $email : null;
$senhaAlterada = !empty($senha) ? $senha : null;

// Se nenhum campo foi alterado
if (!$nomeAlterado && !$telefoneAlterado && !$emailAlterado && !$senhaAlterada) {
    jsonResponse(
        "success",
        "Nenhuma alteração foi feita",
    );
}

// Editar o usuário
$resultado = $usuario->editar($nomeAlterado, $telefoneAlterado, $emailAlterado, $senhaAlterada);

if ($resultado->sucesso) {
    // Atualizar a sessão com os novos dados se houver mudanças
    if ($nomeAlterado || $telefoneAlterado || $emailAlterado) {
        // Buscar dados atualizados do banco
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario->id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $usuarioAtualizado = new SessaoUsuario(
                $row['id'],
                $row['nome'],
                $row['telefone'],
                $row['email'],
                (bool)$row['admin']
            );
            $_SESSION['sessao'] = serialize($usuarioAtualizado);
        }
    }

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
