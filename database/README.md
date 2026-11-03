# Banco de dados - Meu Bolso

Esta pasta contém os arquivos que documentam e criam a estrutura do banco de dados.

## Arquivos

| Arquivo | Para que serve |
|---|---|
| `meu-bolso.sql` | Script que cria o banco `meu_bolso`, as quatro tabelas e as 11 categorias padrão |
| `meu-bolso-modelo-logico.mwb` | Modelo lógico (MySQL Workbench) |
| `documentacao-banco-de-dados.md` | Dicionário de dados: tabelas, colunas, tipos e relacionamentos |

## Tabelas

- `usuarios`: contas de acesso
- `categorias`: categorias de receitas e despesas
- `transacoes`: lançamentos de cada usuário
- `redefinicoes_senha`: pedidos de recuperação de senha (token guardado como hash)

## Como criar o banco

1. Inicie o MySQL no XAMPP e abra `http://localhost/phpmyadmin`.
2. Na aba **SQL**, sem selecionar nenhum banco, cole o conteúdo de `meu-bolso.sql` e clique em **Executar**.
3. Confira que `meu_bolso` tem 4 tabelas e que `categorias` tem 11 linhas.

Execute o script apenas uma vez. Para recomeçar, rode antes `DROP DATABASE meu_bolso;`.

## Hospedagem

Em hospedagens que não permitem criar bancos, crie o banco pelo painel e execute o script **sem** as duas primeiras linhas (`CREATE DATABASE` e `USE`).
