<?php

declare(strict_types=1);

class SenhaReset
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * Cria um token de redefinição (válido por 1 hora).
     * Devolve o token em texto, ou null se o e-mail não existe.
     */
    public function criar(string $email): ?string
    {
        $stmt = $this->conexao->prepare(
            "SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $idUsuario = $stmt->fetchColumn();

        if ($idUsuario === false) {
            return null;
        }

        // Invalida pedidos anteriores ainda abertos.
        $del = $this->conexao->prepare(
            "DELETE FROM redefinicoes_senha
             WHERE id_usuario = :id AND usado_em IS NULL"
        );
        $del->execute(['id' => $idUsuario]);

        $token = bin2hex(random_bytes(32));

        $ins = $this->conexao->prepare(
            "INSERT INTO redefinicoes_senha (id_usuario, token_hash, expira_em)
             VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
        );
        $ins->execute([
            'id'   => $idUsuario,
            'hash' => hash('sha256', $token),
        ]);

        return $token;
    }

    public function tokenValido(string $token): bool
    {
        $stmt = $this->conexao->prepare(
            "SELECT 1 FROM redefinicoes_senha
             WHERE token_hash = :hash
               AND usado_em IS NULL
               AND expira_em > NOW()
             LIMIT 1"
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Troca a senha se o token for válido. O token só funciona uma vez.
     */
    public function redefinir(string $token, string $novaSenha): bool
    {
        $this->conexao->beginTransaction();

        try {
            $stmt = $this->conexao->prepare(
                "SELECT id_redefinicao, id_usuario
                 FROM redefinicoes_senha
                 WHERE token_hash = :hash
                   AND usado_em IS NULL
                   AND expira_em > NOW()
                 LIMIT 1
                 FOR UPDATE"
            );
            $stmt->execute(['hash' => hash('sha256', $token)]);
            $linha = $stmt->fetch();

            if ($linha === false) {
                $this->conexao->rollBack();
                return false;
            }

            $upd = $this->conexao->prepare(
                "UPDATE usuarios SET senha_hash = :hash WHERE id_usuario = :id"
            );
            $upd->execute([
                'hash' => password_hash($novaSenha, PASSWORD_DEFAULT),
                'id'   => $linha['id_usuario'],
            ]);

            $uso = $this->conexao->prepare(
                "UPDATE redefinicoes_senha SET usado_em = NOW()
                 WHERE id_redefinicao = :id"
            );
            $uso->execute(['id' => $linha['id_redefinicao']]);

            $this->conexao->commit();
            return true;
        } catch (Throwable $e) {
            $this->conexao->rollBack();
            throw $e;
        }
    }
}