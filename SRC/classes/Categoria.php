<?php

declare(strict_types=1);

class Categoria
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar(): array
    {
        $stmt = $this->conexao->query(
            "SELECT id_categoria, nome, tipo
             FROM categorias
             ORDER BY tipo, nome"
        );

        return $stmt->fetchAll();
    }

    public function buscar(int $idCategoria): ?array
    {
        $stmt = $this->conexao->prepare(
            "SELECT id_categoria, nome, tipo
             FROM categorias
             WHERE id_categoria = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $idCategoria]);

        $categoria = $stmt->fetch();

        return $categoria === false ? null : $categoria;
    }
}