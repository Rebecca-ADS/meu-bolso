<?php

const NIVEL_USUARIO = 1;
const NIVEL_GERENTE = 2;
const NIVEL_ADMIN   = 3;

function iniciar_sessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}

// SHA-256 com salt baseado no login: sha256( sha256(login) . senha )
function hash_senha(string $login, string $senha): string
{
    $login = mb_strtolower(trim($login), 'UTF-8');
    return hash('sha256', hash('sha256', $login) . $senha);
}

function usuario_logado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function nivel_atual(): int
{
    return (int) ($_SESSION['nivel'] ?? 0);
}

function nome_nivel(int $nivel): string
{
    if ($nivel === NIVEL_ADMIN) {
        return 'Administrador';
    }
    if ($nivel === NIVEL_GERENTE) {
        return 'Gerente';
    }
    return 'Usuário';
}

function exigir_login(string $destino = 'login.php'): void
{
    iniciar_sessao();
    if (!usuario_logado()) {
        header('Location: ' . $destino);
        exit;
    }
}

// Bloqueia quem não tem o nível mínimo exigido pela página
function exigir_nivel(int $minimo, string $destino = 'login.php'): void
{
    exigir_login($destino);
    if (nivel_atual() < $minimo) {
        http_response_code(403);
        exit('Acesso negado: seu nível não permite acessar esta página.');
    }
}