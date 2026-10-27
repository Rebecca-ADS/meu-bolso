<?php

declare(strict_types=1);

class Transacao
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function cadastrar(
        int $idUsuario,
        int $idCategoria,
        string $tipo,
        string $descricao,
        string $valor,
        string $data
    ): bool {
        $stmt = $this->conexao->prepare(
            "INSERT INTO transacoes
                (id_usuario, id_categoria, tipo, descricao, valor, data_transacao)
             VALUES
                (:id_usuario, :id_categoria, :tipo, :descricao, :valor, :data)"
        );

        return $stmt->execute([
            'id_usuario'   => $idUsuario,
            'id_categoria' => $idCategoria,
            'tipo'         => $tipo,
            'descricao'    => $descricao,
            'valor'        => $valor,
            'data'         => $data,
        ]);
    }

    public function listar(int $idUsuario, int $limite = 10): array
    {
        $stmt = $this->conexao->prepare(
            "SELECT t.id_transacao, t.tipo, t.descricao, t.valor,
                    t.data_transacao, c.nome AS categoria
             FROM transacoes t
             INNER JOIN categorias c ON c.id_categoria = t.id_categoria
             WHERE t.id_usuario = :id_usuario
             ORDER BY t.data_transacao DESC, t.id_transacao DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Totais de receitas e despesas. Sem datas = todo o período.
     */
    public function totais(int $idUsuario, ?string $inicio = null, ?string $fim = null): array
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor END), 0) AS receitas,
                    COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor END), 0) AS despesas
                FROM transacoes
                WHERE id_usuario = :id_usuario";
        $params = ['id_usuario' => $idUsuario];

        if ($inicio !== null && $fim !== null) {
            $sql .= " AND data_transacao BETWEEN :inicio AND :fim";
            $params['inicio'] = $inicio;
            $params['fim'] = $fim;
        }

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);
        $linha = $stmt->fetch();

        return [
            'receitas' => (float) $linha['receitas'],
            'despesas' => (float) $linha['despesas'],
        ];
    }

    public function excluir(int $idTransacao, int $idUsuario): bool
    {
        $stmt = $this->conexao->prepare(
            "DELETE FROM transacoes
             WHERE id_transacao = :id AND id_usuario = :id_usuario"
        );
        $stmt->execute(['id' => $idTransacao, 'id_usuario' => $idUsuario]);

        // false se a transação não existe ou é de outro usuário
        return $stmt->rowCount() > 0;
    }
}