<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/ordenacao.php';
require_once dirname(__DIR__) . '/includes/imagens.php';
require_once dirname(__DIR__) . '/includes/categorias.php';
require_once dirname(__DIR__) . '/conecta.php';

exigir_nivel(NIVEL_ADMIN);

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$erro = '';
$nome = '';
$tipo = 'despesa';

// Ordenação da tabela (lista fixa)
$ordem = $_GET['ordem'] ?? 'nome';
$dir = $_GET['dir'] ?? 'asc';
if (!is_string($ordem) || !in_array($ordem, ['id', 'nome', 'tipo', 'uso'], true)) {
    $ordem = 'nome';
}
$dir = ($dir === 'desc') ? 'desc' : 'asc';

// Converte o campo do formulário em id de imagem (null = sem imagem). Retorna false se inválido.
function ler_id_imagem($conexao, $valor)
{
    if (!is_string($valor) || $valor === '') {
        return null;
    }
    if (!ctype_digit($valor) || imagem_buscar($conexao, (int) $valor) === null) {
        return false;
    }
    return (int) $valor;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $erro = 'Formulário inválido. Atualize a página e tente novamente.';

    } elseif (isset($_POST['excluir'])) {
        // ---- EXCLUIR CATEGORIA ----
        $id = $_POST['id_categoria'] ?? '';
        if (!is_string($id) || !ctype_digit($id)) {
            $erro = 'Requisição inválida.';
        } elseif (categoria_em_uso($conexao, (int) $id)) {
            $_SESSION['flash'] = 'Não é possível excluir: a categoria já tem lançamentos.';
            header('Location: categorias.php');
            exit;
        } else {
            $_SESSION['flash'] = categoria_excluir($conexao, (int) $id) ? 'Categoria excluída.' : 'Categoria não encontrada.';
            header('Location: categorias.php');
            exit;
        }

    } elseif (isset($_POST['definir_imagem'])) {
        // ---- TROCAR A IMAGEM DA CATEGORIA ----
        $id = $_POST['id_categoria'] ?? '';
        $idImagem = ler_id_imagem($conexao, $_POST['id_imagem'] ?? '');

        if (!is_string($id) || !ctype_digit($id) || $idImagem === false) {
            $erro = 'Requisição inválida.';
        } elseif (categoria_definir_imagem($conexao, (int) $id, $idImagem)) {
            $_SESSION['flash'] = 'Imagem da categoria atualizada.';
            header('Location: categorias.php');
            exit;
        } else {
            $erro = 'Não foi possível salvar agora.';
        }

    } else {
        // ---- CADASTRAR CATEGORIA ----
        $nome = $_POST['nome'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $idImagem = ler_id_imagem($conexao, $_POST['id_imagem'] ?? '');

        if (!is_string($nome) || !is_string($tipo)) {
            $erro = 'Dados inválidos.';
        } else {
            $nome = trim($nome);
            if ($nome === '' || mb_strlen($nome, 'UTF-8') > 50) {
                $erro = 'Informe o nome (até 50 caracteres).';
            } elseif (!in_array($tipo, ['receita', 'despesa'], true)) {
                $erro = 'Escolha o tipo da categoria.';
            } elseif ($idImagem === false) {
                $erro = 'Imagem inválida.';
            } elseif (categoria_existe($conexao, $nome, $tipo)) {
                $erro = 'Já existe uma categoria com esse nome e tipo.';
            } elseif (categoria_cadastrar($conexao, $nome, $tipo, $idImagem)) {
                $_SESSION['flash'] = 'Categoria cadastrada.';
                header('Location: categorias.php');
                exit;
            } else {
                $erro = 'Não foi possível salvar agora.';
            }
        }
    }
}

$categorias = categoria_listar_com_uso($conexao, $ordem, $dir);
$biblioteca = imagem_listar($conexao, 'titulo', 'asc');

$raiz = '../';
$titulo = 'Categorias';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Categorias</h1>

<?php if ($flash !== ''): ?><p class="aviso ok" role="status"><?= e($flash) ?></p><?php endif; ?>
<?php if ($erro !== ''): ?><p class="aviso erro" role="alert"><?= e($erro) ?></p><?php endif; ?>

<section class="cartao">
    <h2>Nova categoria</h2>
    <form method="POST" action="categorias.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="campo">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" maxlength="50" value="<?= e($nome) ?>" required>
        </div>

        <div class="campo">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo" required>
                <option value="despesa" <?= $tipo === 'despesa' ? 'selected' : '' ?>>Despesa</option>
                <option value="receita" <?= $tipo === 'receita' ? 'selected' : '' ?>>Receita</option>
            </select>
        </div>

        <div class="campo">
            <label for="id_imagem">Imagem (da biblioteca de Imagens)</label>
            <select id="id_imagem" name="id_imagem">
                <option value="">— Sem imagem —</option>
                <?php foreach ($biblioteca as $im): ?>
                    <option value="<?= (int) $im['id_imagem'] ?>"><?= e($im['titulo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">Cadastrar categoria</button>
    </form>
</section>

<div class="tabela">
    <table>
        <thead>
            <tr>
                <th>Imagem</th>
                <th><?= titulo_ordenavel('id', 'ID', $ordem, $dir) ?></th>
                <th><?= titulo_ordenavel('nome', 'Nome', $ordem, $dir) ?></th>
                <th><?= titulo_ordenavel('tipo', 'Tipo', $ordem, $dir) ?></th>
                <th><?= titulo_ordenavel('uso', 'Lançamentos', $ordem, $dir) ?></th>
                <th>Trocar imagem</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($categorias as $c): ?>
            <tr>
                <td>
                    <?php if (!empty($c['imagem'])): ?>
                        <img class="miniatura" src="../uploads/<?= e($c['imagem']) ?>" alt="Imagem da categoria <?= e($c['nome']) ?>">
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= (int) $c['id_categoria'] ?></td>
                <td><?= e($c['nome']) ?></td>
                <td><?= $c['tipo'] === 'receita' ? 'Receita' : 'Despesa' ?></td>
                <td><?= (int) $c['total'] ?></td>
                <td>
                    <form method="POST" action="categorias.php" class="filtros">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id_categoria" value="<?= (int) $c['id_categoria'] ?>">
                        <select name="id_imagem" style="max-width: 180px;">
                            <option value="">— Sem imagem —</option>
                            <?php foreach ($biblioteca as $im): ?>
                                <option value="<?= (int) $im['id_imagem'] ?>" <?= (int) $c['id_imagem'] === (int) $im['id_imagem'] ? 'selected' : '' ?>>
                                    <?= e($im['titulo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="definir_imagem" value="1">Salvar</button>
                    </form>
                </td>
                <td>
                    <?php if ((int) $c['total'] > 0): ?>
                        <small>Em uso</small>
                    <?php else: ?>
                        <form method="POST" action="categorias.php" onsubmit="return confirm('Excluir esta categoria?');">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id_categoria" value="<?= (int) $c['id_categoria'] ?>">
                            <button class="excluir" type="submit" name="excluir" value="1">Excluir</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>