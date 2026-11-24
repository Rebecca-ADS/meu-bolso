<?php

const IMAGEM_MAX_BYTES = 2097152; // 2 MB

// UPLOAD (parte do CREATE e do UPDATE): valida e grava uma imagem enviada.
// Retorna [nome_do_arquivo, mensagem_de_erro].
function upload_imagem($arq, string $pasta): array
{
    if (!is_array($arq) || ($arq['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [null, 'Não foi possível receber o arquivo (máximo de 2 MB).'];
    }
    if ($arq['size'] > IMAGEM_MAX_BYTES) {
        return [null, 'A imagem deve ter no máximo 2 MB.'];
    }
    if (!is_uploaded_file($arq['tmp_name'])) {
        return [null, 'Arquivo inválido.'];
    }

    // O formato é conferido pelo conteúdo do arquivo, não pelo nome
    $info = @getimagesize($arq['tmp_name']);
    $extensoes = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];
    if ($info === false || !isset($extensoes[$info[2]])) {
        return [null, 'Formato inválido. Envie uma imagem JPG, PNG ou WEBP.'];
    }

    if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
    }

    // Nome aleatório: evita sobrescrever arquivos e nomes perigosos
    $nome = bin2hex(random_bytes(16)) . '.' . $extensoes[$info[2]];
    if (!move_uploaded_file($arq['tmp_name'], $pasta . $nome)) {
        return [null, 'Não foi possível gravar o arquivo no servidor.'];
    }
    return [$nome, ''];
}

// REMOÇÃO do arquivo (parte do DELETE e do UPDATE): apaga a imagem do disco
function upload_remover(string $pasta, ?string $nome): void
{
    if ($nome === null || $nome === '') {
        return;
    }
    $caminho = $pasta . basename($nome);
    if (is_file($caminho)) {
        unlink($caminho);
    }
}