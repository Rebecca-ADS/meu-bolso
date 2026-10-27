<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Meu Bolso</title>
</head>
<body>
    <h1>Olá, <?= htmlspecialchars($_SESSION['usuario_nome'], ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p>Login funcionando.</p>
    <p><a href="logout.php">Sair</a></p>
</body>
</html>