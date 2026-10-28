<?php

declare(strict_types=1);

class Usuario
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function emailExiste(string $email): bool
    {
        $stmt = $this->conexao->prepare(
            "SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() !== false;
    }

    public function cadastrar(string $nome, string $email, string $senha): bool
    {
        $email = mb_strtolower(trim($email), 'UTF-8');

        if ($this->emailExiste($email)) {
            return false;
        }

        $stmt = $this->conexao->prepare(
            "INSERT INTO usuarios (nome, email, senha_hash)
             VALUES (:nome, :email, :senha_hash)"
        );

        try {
            return $stmt->execute([
                'nome'       => $nome,
                'email'      => $email,
                'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // e-mail duplicado
                return false;
            }
            throw $e;
        }
    }

    public function autenticar(string $email, string $senha): ?array
    {
        $email = mb_strtolower(trim($email), 'UTF-8');

        $stmt = $this->conexao->prepare(
            "SELECT id_usuario, nome, email, senha_hash
             FROM usuarios WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
            return null;
        }

        if (password_needs_rehash($usuario['senha_hash'], PASSWORD_DEFAULT)) {
            $upd = $this->conexao->prepare(
                "UPDATE usuarios SET senha_hash = :hash WHERE id_usuario = :id"
            );
            $upd->execute([
                'hash' => password_hash($senha, PASSWORD_DEFAULT),
                'id'   => $usuario['id_usuario'],
            ]);
        }

        unset($usuario['senha_hash']);

        return $usuario;
    }

    public function buscarPorId(int $idUsuario): ?array
    {
        $stmt = $this->conexao->prepare(
            "SELECT id_usuario, nome, email, criado_em
             FROM usuarios WHERE id_usuario = :id LIMIT 1"
        );
        $stmt->execute(['id' => $idUsuario]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        return $linha === false ? null : $linha;
    }

    /**
     * Atualiza nome e e-mail. Devolve false se o e-mail já é de outra conta (RN01).
     */
    public function atualizarPerfil(int $idUsuario, string $nome, string $email): bool
    {
        $email = mb_strtolower(trim($email), 'UTF-8');

        $chk = $this->conexao->prepare(
            "SELECT 1 FROM usuarios
             WHERE email = :email AND id_usuario <> :id LIMIT 1"
        );
        $chk->execute(['email' => $email, 'id' => $idUsuario]);

        if ($chk->fetchColumn() !== false) {
            return false;
        }

        try {
            $upd = $this->conexao->prepare(
                "UPDATE usuarios SET nome = :nome, email = :email
                 WHERE id_usuario = :id"
            );
            $upd->execute(['nome' => $nome, 'email' => $email, 'id' => $idUsuario]);

            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Troca a senha exigindo a senha atual.
     */
    public function alterarSenha(int $idUsuario, string $senhaAtual, string $novaSenha): bool
    {
        $stmt = $this->conexao->prepare(
            "SELECT senha_hash FROM usuarios WHERE id_usuario = :id LIMIT 1"
        );
        $stmt->execute(['id' => $idUsuario]);
        $hash = $stmt->fetchColumn();

        if ($hash === false || !password_verify($senhaAtual, (string) $hash)) {
            return false;
        }

        $upd = $this->conexao->prepare(
            "UPDATE usuarios SET senha_hash = :hash WHERE id_usuario = :id"
        );
        $upd->execute([
            'hash' => password_hash($novaSenha, PASSWORD_DEFAULT),
            'id'   => $idUsuario,
        ]);

        return true;
    }

    /**
     * Exclui a conta e todas as transações dela. Exige a senha.
     */
    public function excluirConta(int $idUsuario, string $senha): bool
    {
        $stmt = $this->conexao->prepare(
            "SELECT senha_hash FROM usuarios WHERE id_usuario = :id LIMIT 1"
        );
        $stmt->execute(['id' => $idUsuario]);
        $hash = $stmt->fetchColumn();

        if ($hash === false || !password_verify($senha, (string) $hash)) {
            return false;
        }

        $this->conexao->beginTransaction();

        try {
            $this->conexao->prepare("DELETE FROM transacoes WHERE id_usuario = :id")
                ->execute(['id' => $idUsuario]);
            $this->conexao->prepare("DELETE FROM usuarios WHERE id_usuario = :id")
                ->execute(['id' => $idUsuario]);

            $this->conexao->commit();
            return true;
        } catch (Throwable $e) {
            $this->conexao->rollBack();
            throw $e;
        }
    }
}
