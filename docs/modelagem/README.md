# Modelo Lógico — Meu Bolso

## Objetivo
O modelo lógico define a estrutura das tabelas do banco de dados do sistema Meu Bolso.

## Tabelas

### usuarios
Armazena os dados dos usuários cadastrados.

- `id_usuario`: chave primária.
- `nome`: nome do usuário.
- `email`: e-mail único.
- `senha_hash`: senha armazenada como hash.
- `criado_em`: data de cadastro.

### categorias
Classifica as movimentações financeiras.

- `id_categoria`: chave primária.
- `nome`: nome da categoria.
- `tipo`: receita ou despesa.

### transacoes
Armazena receitas e despesas.

- `id_transacao`: chave primária.
- `id_usuario`: chave estrangeira para `usuarios`.
- `id_categoria`: chave estrangeira para `categorias`.
- `tipo`: receita ou despesa.
- `descricao`: descrição da movimentação.
- `valor`: valor da transação.
- `data_transacao`: data da movimentação.
- `criado_em`: data do registro.

## Relacionamentos

- Um usuário pode registrar várias transações (1:N).
- Uma categoria pode classificar várias transações (1:N).

## Regra de saldo
O saldo é calculado pela diferença entre o total de receitas e o total de despesas.
