<?php

declare(strict_types=1);

class Relatorio
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * Soma das despesas por categoria no período (sempre do usuário logado).
     */
    public function despesasPorCategoria(int $idUsuario, ?string $inicio, ?string $fim): array
    {
        $sql = "SELECT c.nome AS categoria, SUM(t.valor) AS total
                FROM transacoes t
                INNER JOIN categorias c ON c.id_categoria = t.id_categoria
                WHERE t.id_usuario = :id_usuario
                  AND t.tipo = 'despesa'";
        $params = ['id_usuario' => $idUsuario];

        if ($inicio !== null && $fim !== null) {
            $sql .= " AND t.data_transacao BETWEEN :inicio AND :fim";
            $params['inicio'] = $inicio;
            $params['fim'] = $fim;
        }

        $sql .= " GROUP BY c.id_categoria, c.nome
                  ORDER BY total DESC";

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}