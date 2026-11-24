<?php

require_once dirname(__DIR__) . '/includes/auth.php';

iniciar_sessao();

// Apaga os dados da sessão
$_SESSION = [];

// Apaga o cookie da sessão no navegador
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Destrói a sessão no servidor
session_destroy();

header('Location: login.php');
exit;