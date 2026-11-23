<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/transacoes.php';
require_once dirname(__DIR__) . '/includes/imagens.php';
require_once dirname(__DIR__) . '/conecta.php';

exigir_login();

$idUsuario = (int) $_SESSION['usuario_id'];
$pastaUploads = dirname(__DIR__) . '/uploads/';

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$erro = '';
$tituloImg = '';
$idTransacao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $erro = 'Formulário inválido (ou arquivo maior que o limite do servidor). Tente novamente.';

    } elseif (isset($_POST['excluir'])) {
        // ---- REMOÇÃO ----
        $idImg = $_POST['id_imagem'] ?? '';
        if (!is_string($idImg) || !ctype_digit($idImg)) {
            $erro = 'Requisição inválida.';
        } else {
            $nomeArquivo = imagem_arquivo($conexao, (int) $idImg, $idUsuario);
            if ($nomeArquivo !== null && imagem_excluir($conexao, (int) $idImg, $idUsuario)) {
                $caminho = $pastaUploads . basename($nomeArquivo);
                if (is_file($caminho)) {
                    unlink($caminho);
                }
                $_SESSION['flash'] = 'Imagem removida.';
            } else {
                $_SESSION['flash'] = 'Imagem não encontrada.';
            }
            header('Location: imagens.php');
            exit;
        }

    } else {
        // ---- ENVIO (UPLOAD) ----
        $tituloImg = $_POST['titulo'] ?? '';
        $idTransacao = $_POST['id_transacao'] ?? '';
        $arq = $_FILES['arquivo'] ?? null;

        if (!is_string($tituloImg) || !is_string($idTransacao)) {
            $erro = 'Dados inválidos.';
        } else {
            $tituloImg = trim($tituloImg);
            $vinculo = null;

            if ($idTransacao !== '') {
                if (!ctype_digit($idTransacao) || !transacao_pertence($conexao, (int) $idTransacao, $idUsuario)) {
                    $erro = 'Lançamento inválido.';
                } else {
                    $vinculo = (int) $idTransacao;
                }
            }

            if ($erro === '') {
                if ($tituloImg === '' || mb_strlen($tituloImg, 'UTF-8') > 100) {
                    $erro = 'Informe um título (até 100 caracteres).';
                } elseif (!is_array($arq) || ($arq['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $erro = 'Escolha um arquivo de imagem (máximo de 2 MB).';
                } elseif ($arq['size'] > IMAGEM_MAX_BYTES) {
                    $erro = 'A imagem deve ter no máximo 2 MB.';
                } elseif (!is_uploaded_file($arq['tmp_name'])) {
                    $erro = 'Arquivo inválido.';
                } else {
                    $info = @getimagesize($arq['tmp_name']);
                    $extensoes = [
                        IMAGETYPE_JPEG => 'jpg',
                        IMAGETYPE_PNG  => 'png',
                        IMAGETYPE_WEBP => 'webp',
                    ];

                    if ($info === false || !isset($extensoes[$info[2]])) {
                        $erro = 'Formato inválido. Envie uma imagem JPG, PNG ou WEBP.';
                    } else {
                        $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$info[2]];

                        if (!is_dir($pastaUploads)) {
                            mkdir($pastaUploads, 0755, true);
                        }

                        if (move_uploaded_file($arq['tmp_name'], $pastaUploads . $nomeArquivo)) {
                            if (imagem_salvar($conexao, $idUsuario, $vinculo, $tituloImg, $nomeArquivo)) {
                                $_SESSION['flash'] = 'Imagem enviada com sucesso.';
                                header('Location: imagens.php');
                                exit;
                            }
                            unlink($pastaUploads . $nomeArquivo);
                            $erro = 'Não foi possível salvar a imagem agora.';
                        } else {
                            $erro = 'Não foi possível gravar o arquivo no servidor.';
                        }
                    }
                }
            }
        }
    }
}

$imagens = imagem_listar($conexao, $idUsuario);
$transacoes = transacao_listar($conexao, $idUsuario, 50);

$raiz = '../';
$titulo = 'Imagens';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Meus comprovantes (imagens)</h1>

<?php if ($flash !== ''): ?><p class="aviso ok" role="status"><?= e($flash) ?></p><?php endif; ?>
<?php if ($erro !== ''): ?><p class="aviso erro" role="alert"><?= e($erro) ?></p><?php endif; ?>

<section class="cartao">
    <h2>Enviar nova imagem</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="campo">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" maxlength="100" value="<?= e($tituloImg) ?>" required>
        </div>

        <div class="campo">
            <label for="id_transacao">Vincular a um lançamento (opcional)</label>
            <select id="id_transacao" name="id_transacao">
                <option value="">— Sem vínculo —</option>
                <?php foreach ($transacoes as $t): ?>
                    <option value="<?= (int) $t['id_transacao'] ?>" <?= (string) $idTransacao === (string) $t['id_transacao'] ? 'selected' : '' ?>>
                        <?= e(date('d/m/Y', strtotime($t['data_transacao'])) . ' - ' . $t['descricao'] . ' (' . moeda($t['valor']) . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="arquivo">Arquivo (JPG, PNG ou WEBP, até 2 MB)</label>
            <input type="file" id="arquivo" name="arquivo" accept="image/jpeg,image/png,image/webp" required>
        </div>

        <button type="submit">Enviar imagem</button>
    </form>
</section>

<?php if ($imagens === []): ?>
    <p class="vazio">Nenhuma imagem enviada ainda.</p>
<?php else: ?>
    <section class="galeria">
        <?php foreach ($imagens as $im): ?>
            <figure class="imagem-item">
                <img src="../uploads/<?= e($im['arquivo']) ?>" alt="<?= e($im['titulo']) ?>">
                <figcaption>
                    <strong><?= e($im['titulo']) ?></strong><br>
                    <small>
                        <?= e(date('d/m/Y', strtotime($im['criado_em']))) ?>
                        <?php if ($im['transacao'] !== null): ?> · Lançamento: <?= e($im['transacao']) ?><?php endif; ?>
                    </small>
                </figcaption>
                <form method="POST" onsubmit="return confirm('Remover esta imagem?');">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id_imagem" value="<?= (int) $im['id_imagem'] ?>">
                    <button class="excluir" type="submit" name="excluir" value="1">Remover</button>
                </form>
            </figure>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>
