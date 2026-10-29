# Meu Bolso — Documentação do Banco de Dados

## 1. Status da implementação

A estrutura inicial do banco de dados `meu_bolso` foi criada localmente no MariaDB, utilizando o phpMyAdmin disponibilizado pelo XAMPP.

## 2. Tabelas criadas

- **usuarios:** armazena os dados de cadastro dos usuários.
- **categorias:** armazena as categorias de receitas e despesas.
- **transacoes:** registra as movimentações financeiras e seus relacionamentos com usuários e categorias.

## 3. Relacionamentos

- `transacoes.id_usuario` referencia `usuarios.id_usuario`.
- `transacoes.id_categoria` referencia `categorias.id_categoria`.

As relações foram definidas por meio de chaves estrangeiras.

## 4. Tecnologias utilizadas

- MariaDB/MySQL
- phpMyAdmin
- XAMPP

## 5. Arquivos relacionados

- `meu-bolso-modelo-logico.mwb`: modelo lógico desenvolvido no MySQL Workbench.
- `meu-bolso.sql`: script SQL utilizado na criação da estrutura.

## 6. Próximas atividades

1. Conferir as colunas, chaves primárias e chaves estrangeiras.
2. Validar as regras de negócio e as restrições de dados.
3. Implementar a conexão entre PHP e o banco de dados.
4. Testar as operações de cadastro, consulta, atualização e exclusão.

**Observação:** o banco foi criado no ambiente local de desenvolvimento. A documentação e o script SQL devem ser versionados no GitHub para facilitar a manutenção e a reprodução da estrutura.

## Tabela `redefinicoes_senha`

Guarda os pedidos de recuperação de senha (RF13). O token em texto nunca é gravado, só o hash.

| Coluna | Tipo | Nulo | Chave | Descrição |
|---|---|---|---|---|
| id_redefinicao | INT AUTO_INCREMENT | Não | PK | Identificador do pedido |
| id_usuario | INT | Não | FK -> usuarios | Dono do pedido |
| token_hash | CHAR(64) | Não | UNIQUE | SHA-256 do token enviado ao usuário |
| expira_em | DATETIME | Não | | Validade (1 hora após o pedido) |
| usado_em | DATETIME | Sim | | Preenchido quando o link é usado |
| criado_em | TIMESTAMP | Não | | Data do pedido |

Relacionamento: um usuário pode ter vários pedidos (1:N). Ao excluir o usuário, os pedidos são apagados (`ON DELETE CASCADE`).
