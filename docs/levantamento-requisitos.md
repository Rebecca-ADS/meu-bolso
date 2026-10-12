# Meu Bolso — Levantamento de Requisitos

## 1. Descrição do problema
Muitas pessoas apresentam dificuldades para organizar suas finanças pessoais, controlar receitas e despesas e acompanhar o saldo disponível. O Meu Bolso propõe uma aplicação web para facilitar esse controle.

## 2. Objetivo geral
Desenvolver um sistema web de controle financeiro pessoal utilizando PHP e MySQL, permitindo registrar movimentações, consultar o saldo e acompanhar os gastos.

## 3. Público-alvo
Jovens e adultos que desejam organizar suas finanças pessoais por meio de uma interface simples e intuitiva.

## 4. Requisitos funcionais
- RF01 — Cadastrar usuários.
- RF02 — Permitir login e logout.
- RF03 — Exibir o dashboard financeiro.
- RF04 — Cadastrar receitas e despesas.
- RF05 — Consultar o histórico de movimentações.
- RF06 — Consultar detalhes de uma transação.
- RF07 — Excluir transações mediante confirmação.
- RF08 — Calcular o saldo automaticamente.
- RF09 — Exibir gráficos de gastos.
- RF10 — Consultar e editar dados básicos do perfil.
- RF11 — Acessar configurações da conta.
- RF12 — Organizar movimentações por categoria.

## 5. Requisitos não funcionais
- RNF01 — Utilizar PHP e MySQL.
- RNF02 — Apresentar interface responsiva.
- RNF03 — Armazenar senhas com hash seguro.
- RNF04 — Utilizar consultas SQL parametrizadas.
- RNF05 — Restringir o acesso aos dados de cada usuário.
- RNF06 — Validar os dados recebidos pelos formulários.
- RNF07 — Manter consistência visual com o protótipo do Figma.
- RNF08 — Organizar e documentar o código.
- RNF09 — Apresentar mensagens claras de erro e sucesso.
- RNF10 — Utilizar Git e GitHub para versionamento.

## 6. Regras de negócio
- RN01 — O e-mail de cadastro deve ser único.
- RN02 — Somente usuários autenticados podem acessar as funcionalidades privadas.
- RN03 — Receitas aumentam o saldo.
- RN04 — Despesas diminuem o saldo.
- RN05 — O saldo corresponde ao total de receitas menos o total de despesas.
- RN06 — Os valores das movimentações devem ser positivos.
- RN07 — O histórico deve apresentar descrição, valor, categoria e data.
- RN08 — A exclusão de uma transação deve atualizar o saldo e os gráficos.
- RN09 — Cada usuário pode consultar e modificar apenas seus próprios dados.
- RN10 — Os gráficos devem refletir as movimentações registradas no período selecionado.

## 7. Escopo do MVP
O MVP contemplará cadastro, autenticação, dashboard, registro de receitas e despesas, histórico, detalhes, exclusão, cálculo de saldo e gráficos básicos.

Recursos avançados, como integração bancária, notificações e metas financeiras, poderão ser desenvolvidos posteriormente.

## 8. Tecnologias
- PHP
- MySQL
- HTML
- CSS
- JavaScript
- Git e GitHub
- Figma

## 9. Critérios de aceitação
- O usuário consegue criar uma conta e fazer login.
- É possível cadastrar receitas e despesas.
- As movimentações são armazenadas no MySQL.
- O saldo é calculado corretamente.
- O histórico apresenta as movimentações do usuário.
- A exclusão atualiza os valores apresentados.
- Usuários não conseguem acessar dados financeiros de outras contas.
- As funcionalidades principais são testadas e documentadas.
