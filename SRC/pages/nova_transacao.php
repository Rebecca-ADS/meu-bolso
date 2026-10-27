<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/Categoria.php';
require_once dirname(__DIR__) . '/classes/Transacao.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$categoriaObj = new Categoria($pdo);
$categorias = $categoriaObj->listar();

$mensagem = '';
$tipo = 'despesa';
$idCategoria = '';
$descricao = '';
$valor = '';
$data = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $tipoPost = $_POST['tipo'] ?? '';
    $idCategoriaPost = $_POST['id_categoria'] ?? '';
    $descricaoPost = $_POST['descricao'] ?? '';
    $valorPost = $_POST['valor'] ?? '';
    $dataPost = $_POST['data'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
    } elseif (
        !is_string($tipoPost) || !is_string($idCategoriaPost) ||
        !is_string($descricaoPost) || !is_string($valorPost) || !is_string($dataPost)
    ) {
        $mensagem = 'Dados inválidos.';
    } else {
        $tipo = $tipoPost;
        $idCategoria = $idCategoriaPost;
        $descricao = trim($descricaoPost);
        $valor = trim($valorPost);
        $data = trim($dataPost);

        // Aceita "1.234,56", "1234,56" ou "1234.56"
        $valorNormalizado = $valor;
        if (str_contains($valorNormalizado, ',')) {
            $valorNormalizado = str_replace('.', '', $valorNormalizado);
            $valorNormalizado = str_replace(',', '.', $valorNormalizado);
        }

        $dataObj = DateTime::createFromFormat('Y-m-d', $data);
        $dataValida = $dataObj !== false && $dataObj->format('Y-m-d') === $data;

        $categoria = ctype_digit($idCategoria)
            ? $categoriaObj->buscar((int) $idCategoria)
            : null;

        if (!in_array($tipo, ['receita', 'despesa'], true)) {
            $mensagem = 'Escolha receita ou despesa.';
        } elseif ($descricao === '' || mb_strlen($descricao, 'UTF-8') > 150) {
            $mensagem = 'Informe uma descrição de até 150 caracteres.';
        } elseif (
            !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $valorNormalizado) ||
            (float) $valorNormalizado <= 0
        ) {
            $mensagem = 'Informe um valor positivo, por exemplo 25,90.';
        } elseif (!$dataValida) {
            $mensagem = 'Informe uma data válida.';
        } elseif ($categoria === null || $categoria['tipo'] !== $tipo) {
            $mensagem = 'Escolha uma categoria compatível com o tipo.';
        } else {
            try {
                $transacaoObj = new Transacao($pdo);
                $transacaoObj->cadastrar(
                    (int) $_SESSION['usuario_id'],
                    (int) $idCategoria,
                    $tipo,
                    $descricao,
                    number_format((float) $valorNormalizado, 2, '.', ''),
                    $data
                );

                $_SESSION['flash'] = 'Lançamento salvo com sucesso!';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: dashboard.php');
                exit;
            } catch (Throwable $e) {
                error_log('Erro ao salvar transação: ' . $e->getMessage());
                $mensagem = 'Não foi possível salvar. Tente novamente.';
            }
        }
    }
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo lançamento | Meu Bolso</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f6fb;
            color: #17365d;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            width: 100%;
            max-width: 440px;
            padding: 32px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
        }

        h1 { margin: 0 0 20px; text-align: center; font-size: 28px; }

        .mensagem {
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 8px;
            color: #842029;
            background: #f8d7da;
        }

        .campo { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }

        input, select {
            width: 100%;
            min-height: 44px;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 16px;
            background: #fff;
        }

        input:focus, select:focus { outline: 2px solid #8bbcf0; outline-offset: 1px; }

        button {
            width: 100%;
            min-height: 46px;
            border: 0;
            border-radius: 7px;
            background: #216eaf;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover { background: #185b94; }

        .voltar { display: block; margin-top: 22px; text-align: center; color: #0866c6; }
    </style>
</head>
<body>
    <main class="container">
        <h1>Novo lançamento</h1>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem" role="alert"><?= e($mensagem) ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

            <div class="campo">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo" required>
                    <option value="despesa" <?= $tipo === 'despesa' ? 'selected' : '' ?>>Despesa</option>
                    <option value="receita" <?= $tipo === 'receita' ? 'selected' : '' ?>>Receita</option>
                </select>
            </div>

            <div class="campo">
                <label for="id_categoria">Categoria</label>
                <select id="id_categoria" name="id_categoria" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option
                            value="<?= (int) $cat['id_categoria'] ?>"
                            data-tipo="<?= e($cat['tipo']) ?>"
                            <?= (string) $cat['id_categoria'] === $idCategoria ? 'selected' : '' ?>
                        >
                            <?= e($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="descricao">Descrição</label>
                <input type="text" id="descricao" name="descricao" maxlength="150"
                       value="<?= e($descricao) ?>" required>
            </div>

            <div class="campo">
                <label for="valor">Valor (R$)</label>
                <input type="text" id="valor" name="valor" inputmode="decimal"
                       placeholder="0,00" value="<?= e($valor) ?>" required>
            </div>

            <div class="campo">
                <label for="data">Data</label>
                <input type="date" id="data" name="data" value="<?= e($data) ?>" required>
            </div>

            <button type="submit">Salvar lançamento</button>
        </form>

        <a class="voltar" href="dashboard.php">Voltar ao dashboard</a>
    </main>

    <script>
        // Mostra só as categorias do tipo escolhido.
        const tipo = document.getElementById('tipo');
        const categoria = document.getElementById('id_categoria');

        function filtrarCategorias() {
            let selecionadaValida = false;

            for (const opt of categoria.options) {
                if (opt.value === '') continue;
                const visivel = opt.dataset.tipo === tipo.value;
                opt.hidden = !visivel;
                opt.disabled = !visivel;
                if (visivel && opt.selected) selecionadaValida = true;
            }

            if (!selecionadaValida) categoria.value = '';
        }

        tipo.addEventListener('change', filtrarCategorias);
        filtrarCategorias();
    </script>
</body>
</html>
