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

        .botoes a {
            display: inline-block;
            padding: 13px 22px;
            border-radius: 8px;
            background: #1769aa;
            color: white;
            text-decoration: none;
        }

        .botoes a:hover {
            background: #104d80;
        }

        .cofrinho {
            display: block;
            width: 170px;
            max-width: 60%;
            margin: 0 auto 18px;
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
            <svg class="cofrinho" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 220 170" role="img" aria-label="Cofrinho de porquinho">
                <!-- moeda -->
                <circle cx="108" cy="22" r="14" fill="#f4c430" stroke="#c99a10" stroke-width="3"/>
                <text x="108" y="28" text-anchor="middle" font-family="Arial" font-size="16" font-weight="bold" fill="#8a6a00">$</text>
                <!-- patas -->
                <rect x="65" y="118" width="18" height="30" rx="7" fill="#e88ca5"/>
                <rect x="135" y="118" width="18" height="30" rx="7" fill="#e88ca5"/>
                <!-- rabinho -->
                <path d="M28 82 q-16 -6 -8 -20 q6 10 14 6" fill="none" stroke="#e88ca5" stroke-width="5" stroke-linecap="round"/>
                <!-- corpo -->
                <ellipse cx="108" cy="88" rx="82" ry="55" fill="#f7a8bd"/>
                <!-- orelha -->
                <path d="M138 42 L158 18 L170 50 Z" fill="#e88ca5"/>
                <!-- focinho -->
                <ellipse cx="190" cy="92" rx="18" ry="15" fill="#e88ca5"/>
                <circle cx="185" cy="92" r="3" fill="#b5506b"/>
                <circle cx="196" cy="92" r="3" fill="#b5506b"/>
                <!-- olho -->
                <circle cx="162" cy="74" r="5" fill="#243047"/>
                <!-- fenda da moeda -->
                <rect x="88" y="40" width="42" height="7" rx="3.5" fill="#b5506b"/>
            </svg>

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