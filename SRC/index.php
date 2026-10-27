<?php

declare(strict_types=1);

session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Bolso | Controle Financeiro</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #243047;
        }

        header {
            background: #173b67;
            color: white;
            padding: 22px 8%;
        }

        header h1 {
            margin: 0;
        }

        main {
            max-width: 900px;
            margin: 70px auto;
            padding: 24px;
            text-align: center;
        }

        .painel {
            background: white;
            padding: 40px 24px;
            border-radius: 14px;
            box-shadow: 0 4px 18px #00000010;
        }

        .botoes {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 28px;
        }

        a {
            display: inline-block;
            padding: 13px 22px;
            border-radius: 8px;
            background: #1769aa;
            color: white;
            text-decoration: none;
        }

        a:hover {
            background: #104d80;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #65738a;
        }
    </style>
</head>
<body>

    <header>
        <h1>Meu Bolso</h1>
        <p>Organize suas finanças com mais tranquilidade.</p>
    </header>

    <main>
        <section class="painel">
            <h2>Bem-vindo ao Meu Bolso!</h2>

            <p>
                Controle suas receitas, despesas e saldo
                em um só lugar.
            </p>

            <div class="botoes">
                <a href="pages/login.php">Entrar</a>
                <a href="pages/cadastro.php">Criar minha conta</a>
            </div>
        </section>
    </main>

    <footer>
        <p>Meu Bolso &copy; 2026</p>
    </footer>

</body>
</html>
