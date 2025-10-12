<?php

class Resultado {
    public readonly bool $sucesso;
    public readonly string $mensagem;

    public function __construct(bool $sucesso, string $mensagem) {
        $this->sucesso = $sucesso;
        $this->mensagem = $mensagem;
    }
}