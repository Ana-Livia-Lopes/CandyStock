<?php

class Produto {
    public readonly int $id;
    private string $nome;
    private string $descricao;
    private Imagem $imagem;
    private int $estoque;
    private float $preco;
    private bool $ativo;

    public function getId(): int { return $this->id; }

    public function getNome(): string { return $this->nome; }
    public function getDescricao(): string { return $this->descricao; }
    public function getImagem(): Imagem { return $this->imagem; }
    public function getEstoque(): int { return $this->estoque; }
    public function getPreco(): float { return $this->preco; }
    public function getAtivo(): bool { return $this->ativo; }

    public function hasEstoqueBaixo(): bool { return $this->estoque < Produto::ESTOQUE_BAIXO_LIMITE; }
    CONST ESTOQUE_BAIXO_LIMITE = 50;

    private function __construct(
        int $id,
        string $nome,
        string $descricao,
        int $estoque,
        float $preco,
        bool $ativo
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->imagem = new Imagem("produto_$id");
        $this->estoque = $estoque;
        $this->preco = $preco;
        $this->ativo = $ativo;
    }

    public function editar(
        ?string $nome,
        ?string $descricao,
        ?string $caminhoImg,
        ?float $preco,
        ?bool $ativo
    ): Resultado {
        if (!Usuario::hasSessao()) {
            return new Resultado(false, "Nenhuma sessão ativa");
        }

        $setCampos = [];
        $setTipos = [];
        $params = [];

        if ($nome !== null) {
            $setCampos[] = "nome = ?";
            $setTipos[] = "s";
            $params[] = $nome;
        }
        if ($descricao !== null) {
            $setCampos[] = "descricao = ?";
            $setTipos[] = "s";
            $params[] = $descricao;
        }
        if ($preco !== null) {
            $setCampos[] = "preco = ?";
            $setTipos[] = "d";
            $params[] = $preco;
        }
        if ($ativo !== null) {
            $setCampos[] = "ativo = ?";
            $setTipos[] = "i";
            $params[] = $ativo ? 1 : 0;
        }

        if ($caminhoImg !== null) {
            $resultadoImg = $this->imagem->editar($caminhoImg);
            if (!$resultadoImg->sucesso) {
                return new Resultado(false, "Erro ao atualizar imagem: " . $resultadoImg->mensagem);
            }
        }

        if (count($setCampos) === 0) {
            if ($caminhoImg === null) {
                return new Resultado(true, "Nenhum campo para atualizar");
            } else {
                return new Resultado(true, "Imagem atualizada com sucesso");
            }
        }

        global $conn;
        $sql = "UPDATE produtos SET " . implode(", ", $setCampos) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return new Resultado(false, "Erro na preparação da consulta");
        }

        $setTipos[] = "i";
        $params[] = $this->id;

        $stmt->bind_param(implode("", $setTipos), ...$params);

        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao executar atualização");
        }

        if ($nome !== null) { $this->nome = $nome; }
        if ($descricao !== null) { $this->descricao = $descricao; }
        if ($preco !== null) { $this->preco = $preco; }
        if ($ativo !== null) { $this->ativo = $ativo; }
        return new Resultado(true, "Produto atualizado com sucesso");
    }

    public function comentar(string $conteudo) {
        if (!Usuario::hasSessao()) {
            return new Resultado(false, "Nenhuma sessão ativa");
        }

        $usuario = Usuario::getSessao();
        $conteudo = trim($conteudo);
        if (empty($conteudo)) {
            return new Resultado(false, "Conteúdo do comentário não pode ser vazio");
        }

        global $conn;
        $sql = "INSERT INTO comentarios (id_produto, id_usuario, conteudo, data_hora) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return new Resultado(false, "Erro na preparação da consulta");
        }

        $id = $this->getId();
        $stmt->bind_param("iis", $id, $usuario->id, $conteudo);

        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao adicionar comentário");
        }

        return new Resultado(true, "Comentário adicionado com sucesso");
    }

    public function excluir() {
        if (!Usuario::hasSessao()) {
            return new Resultado(false, "Nenhuma sessão ativa");
        }

        $id = $this->getId();

        global $conn;
        $sql = "DELETE FROM produtos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao excluir produto");
        }

        $sql = "DELETE FROM comentarios WHERE id_produto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao excluir comentários do produto");
        }

        return new Resultado(true, "Produto excluído com sucesso");
    }

    public function adicionar(int $quantidade): Resultado {
        return $this->movimentar(abs($quantidade));
    }

    public function remover(int $quantidade): Resultado {
        return $this->movimentar(-abs($quantidade));
    }

    private function movimentar(int $quantidade): Resultado {
        if ($quantidade === 0) {
            return new Resultado(true, "Nenhuma movimentação realizada");
        }
        
        $direcao = $quantidade > 0 ? Direcao::ENTRADA : Direcao::SAIDA;
        $quantidadeAbs = abs($quantidade);

        if (!Usuario::hasSessao()) {
            return new Resultado(false, "Nenhuma sessão ativa");
        }

        if ($direcao === Direcao::SAIDA && $this->estoque < $quantidadeAbs) {
            return new Resultado(false, "Estoque insuficiente para remoção");
        }
        $usuario = Usuario::getSessao();
        global $conn;
        $sql = "INSERT INTO movimentacoes (id_produto, id_usuario, direcao, quantidade, data_hora) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return new Resultado(false, "Erro na preparação da consulta");
        }
        $id = $this->getId();
        $stmt->bind_param("iisi", $id, $usuario->id, $direcao, $quantidadeAbs);
        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao registrar movimentação");
        }
        $this->estoque += $quantidade;

        $sql = "UPDATE produtos SET estoque = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return new Resultado(false, "Erro na preparação da consulta");
        }
        $stmt->bind_param("ii", $this->estoque, $id);
        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao atualizar estoque");
        }
        
        return new Resultado(true, "Movimentação realizada com sucesso");
    }

    public function getComentarios(): array {
        $usuarios_pool = [];
        global $conn;
        $sql = "SELECT * FROM comentarios WHERE id_produto = ? ORDER BY data_hora DESC";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        $id = $this->getId();
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            return [];
        }
        $resultComentarios = $stmt->get_result();
        $tuplasComentarios = [];
        $idsUsuarios = [];
        while ($tupla = $resultComentarios->fetch_assoc()) {
            $tuplasComentarios[] = $tupla;
            $idsUsuarios[] = $tupla['id_usuario'];
        }

        if (count($idsUsuarios) === 0) {
            return [];
        }

        $sqlUsuarios = "SELECT * FROM usuarios WHERE id IN (" . implode(",", array_fill(0, count($idsUsuarios), "?")) . ")";
        $stmtUsuarios = $conn->prepare($sqlUsuarios);
        if ($stmtUsuarios === false) {
            return [];
        }
        $tipos = str_repeat("i", count($idsUsuarios));
        $stmtUsuarios->bind_param($tipos, ...$idsUsuarios);
        if (!$stmtUsuarios->execute()) {
            return [];
        }
        $resultUsuarios = $stmtUsuarios->get_result();
        while ($tupla = $resultUsuarios->fetch_assoc()) {
            $usuarios_pool[$tupla['id']] = new Usuario(
                $tupla['id'],
                $tupla['nome'],
                $tupla['telefone'],
                $tupla['email'],
                $tupla['senha']
            );
        }
        $comentarios = [];
        foreach ($tuplasComentarios as $tupla) {
            if (!isset($usuarios_pool[$tupla['id_usuario']])) {
                continue;
            }
            $comentarios[] = new Comentario(
                $tupla['id'],
                $this,
                $usuarios_pool[$tupla['id_usuario']],
                $tupla['conteudo'],
                new DateTime($tupla['data_hora'])
            );
        }
        return $comentarios;
    }

    public function getMovimentacoes(): array {
        $usuarios_pool = [];
        global $conn;
        $sql = "SELECT * FROM movimentacoes WHERE id_produto = ? ORDER BY data_hora DESC";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        $id = $this->getId();
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            return [];
        }
        $resultMovimentacoes = $stmt->get_result();
        $tuplasMovimentacoes = [];
        $idsUsuarios = [];
        while ($tupla = $resultMovimentacoes->fetch_assoc()) {
            $tuplasMovimentacoes[] = $tupla;
            $idsUsuarios[] = $tupla['id_usuario'];
        }

        if (count($idsUsuarios) === 0) {
            return [];
        }

        $sqlUsuarios = "SELECT * FROM usuarios WHERE id IN (" . implode(",", array_fill(0, count($idsUsuarios), "?")) . ")";
        $stmtUsuarios = $conn->prepare($sqlUsuarios);
        if ($stmtUsuarios === false) {
            return [];
        }
        $tipos = str_repeat("i", count($idsUsuarios));
        $stmtUsuarios->bind_param($tipos, ...$idsUsuarios);
        if (!$stmtUsuarios->execute()) {
            return [];
        }
        $resultUsuarios = $stmtUsuarios->get_result();
        while ($tupla = $resultUsuarios->fetch_assoc()) {
            $usuarios_pool[$tupla['id']] = new Usuario(
                $tupla['id'],
                $tupla['nome'],
                $tupla['telefone'],
                $tupla['email'],
                $tupla['senha']
            );
        }
        $movimentacoes = [];
        foreach ($tuplasMovimentacoes as $tupla) {
            if (!isset($usuarios_pool[$tupla['id_usuario']])) {
                continue;
            }
            $movimentacoes[] = new Movimentacao(
                $tupla['id'],
                $usuarios_pool[$tupla['id_usuario']],
                $this,
                $tupla['direcao'],
                $tupla['quantidade'],
                new DateTime($tupla['data_hora'])
            );
        }
        return $movimentacoes;
    }

    public static function criar(
        string $nome,
        string $descricao,
        float $preco,
        int $estoqueInicial,
        string $caminhoImg,
    ): ResultadoProduto {
        if (!Usuario::hasSessao()) {
            return new ResultadoProduto(null, "Nenhuma sessão ativa");
        }

        $nome = trim($nome);
        $descricao = trim($descricao);
        if (empty($nome)) {
            return new ResultadoProduto(null, "Nome do produto não pode ser vazio");
        }
        if (empty($descricao)) {
            return new ResultadoProduto(null, "Descrição do produto não pode ser vazia");
        }
        if ($preco < 0) {
            return new ResultadoProduto(null, "Preço não pode ser negativo");
        }
        if ($estoqueInicial < 0) {
            return new ResultadoProduto(null, "Estoque inicial não pode ser negativo");
        }

        global $conn;
        $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, ativo) VALUES (?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return new ResultadoProduto(null, "Erro na preparação da consulta");
        }
        $stmt->bind_param("ssdi", $nome, $descricao, $preco, $estoqueInicial);
        if (!$stmt->execute()) {
            return new ResultadoProduto(null, "Erro ao criar produto");
        }

        $id = $stmt->insert_id;
        $produto = new Produto($id, $nome, $descricao, $estoqueInicial, $preco, true);

        if (!empty($caminhoImg)) {
            $resultadoImg = $produto->imagem->editar($caminhoImg);
            if (!$resultadoImg->sucesso) {
                // Se falhar ao salvar a imagem, excluir o produto criado
                $produto->excluir();
                return new ResultadoProduto(null, "Erro ao salvar imagem: " . $resultadoImg->mensagem);
            }
        }

        return new ResultadoProduto($produto);
    }

    public static function pesquisar(
        ?string $nome = null,
        ?float $preco_min = null,
        ?float $preco_max = null,
        ?bool $ativo = null,
        ?int $estoque_min = null,
        ?int $estoque_max = null,
    ): array {
        global $conn;
        $whereClausulas = [];
        $tipos = [];
        $params = [];
        if ($nome !== null && trim($nome) !== "") {
            $whereClausulas[] = "nome LIKE ?";
            $tipos[] = "s";
            $params[] = "%" . $nome . "%";
        }
        if ($preco_min !== null) {
            $whereClausulas[] = "preco >= ?";
            $tipos[] = "d";
            $params[] = $preco_min;
        }
        if ($preco_max !== null) {
            $whereClausulas[] = "preco <= ?";
            $tipos[] = "d";
            $params[] = $preco_max;
        }
        if ($ativo !== null) {
            $whereClausulas[] = "ativo = ?";
            $tipos[] = "i";
            $params[] = $ativo ? 1 : 0;
        }
        if ($estoque_min !== null) {
            $whereClausulas[] = "estoque >= ?";
            $tipos[] = "i";
            $params[] = $estoque_min;
        }
        if ($estoque_max !== null) {
            $whereClausulas[] = "estoque <= ?";
            $tipos[] = "i";
            $params[] = $estoque_max;
        }
        $sql = "SELECT * FROM produtos";
        if (count($whereClausulas) > 0) {
            $sql .= " WHERE " . implode(" AND ", $whereClausulas);
        }
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        if (count($params) > 0) {
            $stmt->bind_param(implode("", $tipos), ...$params);
        }
        if (!$stmt->execute()) {
            return [];
        }
        $result = $stmt->get_result();
        $produtos = [];
        while ($tupla = $result->fetch_assoc()) {
            $produtos[] = new Produto(
                $tupla['id'],
                $tupla['nome'],
                $tupla['descricao'],
                $tupla['estoque'],
                $tupla['preco'],
                (bool)$tupla['ativo']
            );
        }
        return $produtos;
    }

    public static function byId(int $id) {
        global $conn;
        $sql = "SELECT * FROM produtos WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return null;
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            return null;
        }
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }
        $tupla = $result->fetch_assoc();
        return new Produto(
            $tupla['id'],
            $tupla['nome'],
            $tupla['descricao'],
            $tupla['estoque'],
            $tupla['preco'],
            (bool)$tupla['ativo']
        );
    }
}

class ResultadoProduto {
    public readonly ?Produto $produto;
    public readonly ?string $erro;

    public function __construct(?Produto $produto = null, ?string $erro = null) {
        $this->produto = $produto;
        $this->erro = $erro;
    }
}

class Imagem {
    private string $nome;

    public function __construct(string $nome) {
        $this->nome = $nome;
    }

    public function getCaminho(): string {
        if (pathinfo($this->nome, PATHINFO_EXTENSION)) {
            return './images/' . $this->nome;
        }
        
        $extensoes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        foreach ($extensoes as $ext) {
            $caminho = './images/' . $this->nome . '.' . $ext;
            if (file_exists($caminho)) {
                return $caminho;
            }
        }
        
        return './images/' . $this->nome . '.jpg';
    }

    public function editar(mixed $dadosArquivo): Resultado {
        $tempPath = '';
        $tamanhoArquivo = 0;
        $nomeOriginal = '';

        if (is_array($dadosArquivo)) {
            if ($dadosArquivo['error'] !== UPLOAD_ERR_OK) {
                $erros = [
                    UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
                    UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
                    UPLOAD_ERR_PARTIAL => 'Upload incompleto',
                    UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
                    UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não encontrado',
                    UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever arquivo',
                    UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
                ];
                
                $mensagem = $erros[$dadosArquivo['error']] ?? 'Erro desconhecido no upload';
                return new Resultado(false, $mensagem);
            }

            $tamanhoMaximo = 25 * 1024 * 1024;
            if ($dadosArquivo['size'] > $tamanhoMaximo) {
                return new Resultado(false, "Arquivo muito grande. Máximo: 5MB");
            }

            $tempPath = $dadosArquivo['tmp_name'];
            $tamanhoArquivo = $dadosArquivo['size'];
            $nomeOriginal = $dadosArquivo['name'];
        } else {
            $tempPath = $dadosArquivo;
            if (file_exists($tempPath)) {
                $tamanhoArquivo = filesize($tempPath);
                $nomeOriginal = basename($tempPath);
            }
        }

        if (!file_exists($tempPath)) {
            return new Resultado(false, "Arquivo temporário não encontrado");
        }

        $imageInfo = getimagesize($tempPath);
        if ($imageInfo === false) {
            return new Resultado(false, "Arquivo não é uma imagem válida");
        }

        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        $mimeType = $imageInfo['mime'];
        if (!array_key_exists($mimeType, $mimeTypes)) {
            return new Resultado(false, "Tipo de imagem não suportado. Use: JPG, PNG, GIF, WebP");
        }

        $extensao = $mimeTypes[$mimeType];

        $diretorioImagens = './images/';
        if (!is_dir($diretorioImagens)) {
            if (!mkdir($diretorioImagens, 0755, true)) {
                return new Resultado(false, "Erro ao criar diretório de imagens");
            }
        }

        $nomeArquivo = $this->nome . '.' . $extensao;
        $caminhoDestino = $diretorioImagens . $nomeArquivo;

        $sucesso = false;
        if (is_array($dadosArquivo)) {
            $sucesso = move_uploaded_file($tempPath, $caminhoDestino);
        } else {
            $sucesso = copy($tempPath, $caminhoDestino);
            if ($sucesso) {
                unlink($tempPath);
            }
        }

        if (!$sucesso) {
            return new Resultado(false, "Erro ao salvar a imagem");
        }

        if (!file_exists($caminhoDestino)) {
            return new Resultado(false, "Erro ao verificar salvamento da imagem");
        }

        // Remover imagem anterior se existir e for diferente da nova
        $caminhoAtual = $this->getCaminho();
        if (!empty($this->nome) && file_exists($caminhoAtual) && $caminhoAtual !== $caminhoDestino) {
            unlink($caminhoAtual);
        }

        return new Resultado(true, "Imagem salva com sucesso");
    }
}

class Movimentacao {
    public readonly int $id;
    public readonly Usuario $usuario;
    public readonly Produto $produto;
    public readonly string $direcao;
    public readonly int $quantidade;
    public readonly DateTime $data_hora;

    public function __construct(
        int $id,
        Usuario $usuario,
        Produto $produto,
        string $direcao,
        int $quantidade,
        DateTime $data_hora
    ) {
        if (!Direcao::validar($direcao)) {
            throw new InvalidArgumentException("Direcao invalida");
        }

        $this->id = $id;
        $this->usuario = $usuario;
        $this->produto = $produto;
        $this->direcao = $direcao;
        $this->quantidade = $quantidade;
        $this->data_hora = $data_hora;
    }
}

class Comentario {
    public readonly int $id;
    public readonly Produto $produto;
    public readonly Usuario $usuario;
    public readonly string $conteudo;
    public readonly DateTime $data_hora;

    public function __construct(
        int $id,
        Produto $produto,
        Usuario $usuario,
        string $conteudo,
        DateTime $data_hora
    ) {
        $this->id = $id;
        $this->produto = $produto;
        $this->usuario = $usuario;
        $this->conteudo = $conteudo;
        $this->data_hora = $data_hora;
    }

    public function excluir(): Resultado {
        global $conn;
        $sql = "DELETE FROM comentarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->id);
        if (!$stmt->execute()) {
            return new Resultado(false, "Erro ao excluir comentário");
        }
        return new Resultado(true, "Comentário excluído com sucesso");
    }

    public function toJSON(): array {
        return [
            "id" => $this->id,
            "produto_id" => $this->produto->id,
            "usuario" => [
                "id" => $this->usuario->id,
                "nome" => $this->usuario->getNome(),
                "email" => $this->usuario->getEmail()
            ],
            "conteudo" => $this->conteudo,
            "data_hora" => $this->data_hora->format(DateTime::ATOM)
        ];
    }
}

class Direcao {
    private static array $valores = ["entrada", "saida"];

    public static function validar(string $value): bool {
        return in_array($value, self::$valores);
    }

    public const ENTRADA = "entrada";
    public const SAIDA = "saida";
}