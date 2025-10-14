<?php

class Usuario {
    public readonly int $id;
    protected string $nome;
    protected string $telefone;
    protected string $email;
    protected bool $admin;

    public function getNome(): string { return $this->nome; }
    public function getTelefone(): string { return $this->telefone; }
    public function getEmail(): string { return $this->email; }
    public function isAdmin(): bool { return $this->admin; }

    public function __construct(
        int $id,
        string $nome,
        string $telefone,
        string $email,
        bool $admin
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->admin = $admin;
    }

    public function editar(
        ?string $nome,
        ?string $telefone,
        ?string $email,
        ?string $senha
    ): Resultado {
        if (!self::hasSessao()) {
            return new Resultado(false, "Nenhuma sessão ativa");
        }
        $sessao = Usuario::getSessao();
        if (!$sessao->isAdmin() && $sessao->id !== $this->id) {
            return new Resultado(false, "Permissão negada");
        }

        $setCampos = [];
        $setTipos = [];
        $params = [];

        if ($nome !== null) {
            $setCampos[] = "nome = ?";
            $setTipos[] = "s";
            $params[] = $nome;
        }
        if ($telefone !== null) {
            $setCampos[] = "telefone = ?";
            $setTipos[] = "s";
            $params[] = $telefone;
        }
        if ($email !== null) {
            $setCampos[] = "email = ?";
            $setTipos[] = "s";
            $params[] = $email;
        }
        if ($senha !== null) {
            $setCampos[] = "senha = ?";
            $setTipos[] = "s";
            $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);
            $params[] = $hashedSenha;
        }

        if (count($setCampos) === 0) {
            return new Resultado(true, "Nenhum campo para atualizar");
        }

        $setClause = implode(", ", $setCampos);
        $setTiposStr = implode("", $setTipos) . "i";
        
        global $conn;
        $sql = "UPDATE usuarios SET $setClause WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return new Resultado(false, "Erro na preparação da consulta");
        }
        $params[] = $this->id;
        $stmt->bind_param($setTiposStr, ...$params);
        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao atualizar usuário");
        }
        if ($stmt->affected_rows === 0) {
            return new Resultado(false, "Nenhum campo foi alterado");
        }
        if ($nome !== null) {
            $this->nome = $nome;
        }
        if ($telefone !== null) {
            $this->telefone = $telefone;
        }
        if ($email !== null) {
            $this->email = $email;
        }
        return new Resultado(true, "Usuário atualizado com sucesso");
    }

    public function excluir(): Resultado {
        if (!self::hasSessao()) {
            return new Resultado(false, "Nenhuma sessão ativa");
        }
        $sessao = Usuario::getSessao();
        if (!$sessao->isAdmin()) {
            return new Resultado(false, "Permissão negada");
        }

        if ($sessao->id === $this->id) {
            return new Resultado(false, "Não é possível excluir a própria conta");
        }

        global $conn;
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->id);
        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao excluir usuário");
        }

        $sql = "DELETE FROM comentarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->id);
        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao excluir comentários do usuário");
        }

        return new Resultado(true, "Usuário excluído com sucesso");
    }

    public static function registar(
        string $nome,
        string $telefone,
        string $email,
        string $senha
    ): ResultadoSessao {
        // if (!self::hasSessao()) {
        //     return new ResultadoSessao(null, "Nenhuma sessão ativa");
        // }
        // $sessao = Usuario::getSessao();

        // if (!$sessao->isAdmin()) {
        //     return new ResultadoSessao(null, "Permissão negada");
        // }

        $vazios = [];

        $nome = trim($nome);
        if ($nome === "") {
            $vazios[] = "nome";
        }
        $telefone = trim($telefone);
        if ($telefone === "") {
            $vazios[] = "telefone";
        }
        $email = trim($email);
        if ($email === "") {
            $vazios[] = "email";
        }
        $senha = trim($senha);
        if ($senha === "") {
            $vazios[] = "senha";
        }

        if (count($vazios) > 0) {
            $lista = implode(", ", $vazios);
            return new ResultadoSessao(null, "Os seguintes campos estão vazios: $lista");
        }

        if (!str_contains($email, "@")) {
            return new ResultadoSessao(null, "E-mail inválido");
        }
        
        global $conn;
        $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return new ResultadoSessao(null, "E-mail já cadastrado");
        }

        $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome, telefone, email, senha, admin)
                VALUES (?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nome, $telefone, $email, $hashedSenha);
        if (!$stmt->execute()) {
            return new ResultadoSessao(null, "Erro ao registrar usuário");
        }

        $id = $stmt->insert_id;
        $usuario = new SessaoUsuario($id, $nome, $telefone, $email, false);

        $_SESSION['sessao'] = serialize($usuario);

        return new ResultadoSessao($usuario, null);
    }

    public static function entrar(
        string $email,
        string $senha
    ): ResultadoSessao {
        global $conn;
        $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return new ResultadoSessao(null, "Usuário não encontrado");
        }

        $row = $result->fetch_assoc();
        if (!password_verify($senha, $row['senha'])) {
            return new ResultadoSessao(null, "Senha incorreta");
        }

        $usuario = new SessaoUsuario(
            $row['id'],
            $row['nome'],
            $row['telefone'],
            $row['email'],
            (bool)$row['admin']
        );

        $_SESSION['sessao'] = serialize($usuario);

        return new ResultadoSessao($usuario, null);
    }

    public static function hasSessao(): bool {
        return isset($_SESSION['sessao']);
    }

    public static function getSessao(): SessaoUsuario {
        return unserialize($_SESSION['sessao'], ['allowed_classes' => [SessaoUsuario::class]]);
    }
}

class SessaoUsuario extends Usuario {
    private bool $ativa = false;

    public function sair() {
        $ativa = false;
        unset($_SESSION['sessao']);
    }
}

class ResultadoRegistro {
    public readonly ?Usuario $usuario;
    public readonly ?string $erro;

    public function __construct(?Usuario $usuario = null, ?string $erro = null) {
        $this->usuario = $usuario;
        $this->erro = $erro;
    }
}

class ResultadoSessao {
    public readonly ?SessaoUsuario $sessao;
    public readonly ?string $erro;

    public function __construct(?SessaoUsuario $sessao = null, ?string $erro = null) {
        $this->sessao = $sessao;
        $this->erro = $erro;
    }
}