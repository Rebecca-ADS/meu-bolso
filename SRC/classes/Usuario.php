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
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() !== false;
    }

    public function cadastrar(
        string $nome,
        string $email,
        string $senha
    ): bool {
        $email = mb_strtolower(trim($email), 'UTF-8');

        if ($this->emailExiste($email)) {
            return false;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios
                    (nome, email, senha_hash)
                VALUES
                    (:nome, :email, :senha_hash)";

        $stmt = $this->conexao->prepare($sql);

        try {
            return $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'senha_hash' => $senhaHash,
            ]);
        } catch (PDOException $e) {
            // 23000 = violação de UNIQUE (e-mail já cadastrado)
            if ($e->getCode() === '23000') {
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
             FROM usuarios
             WHERE email = :email
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
            return null;
        }

        if (password_needs_rehash($usuario['senha_hash'], PASSWORD_DEFAULT)) {
            $novoHash = password_hash($senha, PASSWORD_DEFAULT);

            $upd = $this->conexao->prepare(
                "UPDATE usuarios
                 SET senha_hash = :hash
                 WHERE id_usuario = :id"
            );
            $upd->execute([
                'hash' => $novoHash,
                'id' => $usuario['id_usuario'],
            ]);
        }

        unset($usuario['senha_hash']);

        return $usuario;
    }
}
