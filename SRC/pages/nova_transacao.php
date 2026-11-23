<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/transacoes.php';
require_once dirname(__DIR__) . '/conecta.php';

exigir_login();

$idUsuario = (int) $_SESSION['usuario_id'];
$categorias = categoria_listar($conexao);

$erro = '';
$tipo = 'despesa';
$idCategoria = '';
$descricao = '';
$valor = '';
$data = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? '';
    $idCategoria = $_POST['id_categoria'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $data = $_POST['data'] ?? '';

    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $erro = 'Formulário inválido. Atualize a página e tente novamente.';
    } elseif (
        !is_string($tipo) || !is_string($idCategoria) || !is_string($descricao) ||
        !is_string($valor) || !is_string($data)
    ) {
        $erro = 'Dados inválidos.';
    } else {
        $descricao = trim($descricao);
        $valor = trim($valor);

        $dataOk = false;
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $data, $p)) {
            $dataOk = checkdate((int) $p[2], (int) $p[3], (int) $p[1]);
        }

        if (!in_array($tipo, ['receita', 'despesa'], true)) {
            $erro = 'Escolha o tipo do lançamento.';
        } elseif (!ctype_digit($idCategoria) || categoria_tipo($conexao, (int) $idCategoria) !== $tipo) {
            $erro = 'Escolha uma categoria compatível com o tipo.';
        } elseif ($descricao === '' || mb_strlen($descricao, 'UTF-8') > 100) {
            $erro = 'Informe a descrição (até 100 caracteres).';
        } elseif (!is_numeric($valor) || (float) $valor <= 0 || (float) $valor > 9999999.99) {
            $erro = 'Informe um valor maior que zero.';
        } elseif (!$dataOk) {
            $erro = 'Informe uma data válida.';
        } elseif (transacao_criar($conexao, $idUsuario, (int) $idCategoria, $tipo, $descricao, round((float) $valor, 2), $data)) {
            $_SESSION['flash'] = 'Lançamento salvo com sucesso.';
            header('Location: dashboard.php');
            exit;
        } else {
            $erro = 'Não foi possível salvar agora. Tente novamente.';
        }
    }
}

$raiz = '../';
$titulo = 'Novo lançamento';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<section class="cartao estreito">
    <h1>Novo lançamento</h1>

    <?php if ($erro !== ''): ?><p class="aviso erro" role="alert"><?= e($erro) ?></p><?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

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
                <?php foreach ($categorias as $c): ?>
                    <option
                        value="<?= (int) $c['id_categoria'] ?>"
                        data-tipo="<?= e($c['tipo']) ?>"
                        <?= (string) $idCategoria === (string) $c['id_categoria'] ? 'selected' : '' ?>
                    ><?= e($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="descricao">Descrição</label>
            <input type="text" id="descricao" name="descricao" maxlength="100" value="<?= e($descricao) ?>" required>
        </div>

        <div class="campo">
            <label for="valor">Valor (R$)</label>
            <input type="number" id="valor" name="valor" step="0.01" min="0.01" value="<?= e($valor) ?>" required>
        </div>

        <div class="campo">
            <label for="data">Data</label>
            <input type="date" id="data" name="data" value="<?= e($data) ?>" required>
        </div>

        <button class="cheio" type="submit">Salvar lançamento</button>
    </form>
</section>

<script>
// Mostra só as categorias do tipo escolhido (receita ou despesa)
(function () {
    var tipo = document.getElementById('tipo');
    var cat = document.getElementById('id_categoria');

    function filtrar() {
        var primeira = null;
        for (var i = 0; i < cat.options.length; i++) {
            var op = cat.options[i];
            var combina = op.getAttribute('data-tipo') === tipo.value;
            op.hidden = !combina;
            op.disabled = !combina;
            if (combina && primeira === null) { primeira = op; }
        }
        if (cat.selectedOptions.length === 0 || cat.selectedOptions[0].disabled) {
            if (primeira !== null) { cat.value = primeira.value; }
        }
    }

    tipo.addEventListener('change', filtrar);
    filtrar();
})();
</script>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>