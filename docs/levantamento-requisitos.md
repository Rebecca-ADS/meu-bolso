# Meu Bolso — Levantamento de Requisitos

## 1. Descrição do problema
Muitas pessoas apresentam dificuldades para organizar suas finanças pessoais, controlar receitas e despesas e acompanhar o saldo disponível. O Meu Bolso propõe uma aplicação web para facilitar esse controle.

- [Documentação do banco de dados](database/documentacao-banco-de-dados.md)
- 
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

### Fundamentação Matemática

O sistema Meu Bolso utilizará conceitos de matemática financeira e aritmética para realizar o controle das movimentações financeiras dos usuários.

Os principais cálculos serão:

- **Saldo financeiro:** diferença entre o total de receitas e o total de despesas.
- **Percentual de despesas:** proporção das despesas em relação às receitas.
- **Consolidação por período:** soma das receitas e despesas por dia, mês ou ano.
- **Distribuição por categoria:** totalização das despesas conforme suas categorias.
- **Comparação financeira:** análise das variações entre períodos para auxiliar o acompanhamento das finanças pessoais.

Os cálculos serão realizados pela aplicação em PHP a partir dos dados armazenados no banco de dados MySQL. Os resultados serão apresentados em valores monetários, indicadores e gráficos, contribuindo para a organização e a tomada de decisões financeiras.

Quando não houver receitas registradas, o sistema deverá evitar divisão por zero no cálculo dos percentuais.


## Requisitos de Segurança

O sistema Meu Bolso deverá adotar medidas de segurança para proteger os dados pessoais e financeiros dos usuários, reduzir vulnerabilidades comuns em aplicações web e garantir que cada usuário tenha acesso somente às informações autorizadas.

### Requisitos não funcionais de segurança

- **RNF-S01 — Proteção de senhas:** as senhas deverão ser armazenadas por meio de funções próprias para hash seguro, como `password_hash()`, sendo verificadas com `password_verify()`.
- **RNF-S02 — Segurança das consultas:** as operações com o banco de dados deverão utilizar consultas preparadas, preferencialmente por meio da biblioteca PDO, para reduzir o risco de SQL Injection.
- **RNF-S03 — Controle de acesso:** o sistema deverá verificar a autenticação e a autorização antes de permitir o acesso a páginas e operações restritas.
- **RNF-S04 — Privacidade dos dados:** cada usuário deverá acessar exclusivamente suas próprias transações financeiras, impedindo consultas ou alterações de dados pertencentes a terceiros.
- **RNF-S05 — Validação de entradas:** os dados recebidos dos formulários deverão ser validados no servidor antes de serem processados ou armazenados.
- **RNF-S06 — Prevenção contra XSS:** os dados dinâmicos exibidos nas páginas deverão ser tratados adequadamente para reduzir o risco de execução de scripts maliciosos.
- **RNF-S07 — Proteção contra CSRF:** as operações que alterem dados deverão utilizar mecanismos de proteção contra requisições forjadas, como tokens CSRF.
- **RNF-S08 — Gerenciamento de sessões:** as sessões deverão ser configuradas de forma segura, com regeneração do identificador após a autenticação e encerramento adequado no logout.
- **RNF-S09 — Proteção de cookies:** os cookies deverão armazenar apenas informações necessárias, sem senhas ou dados financeiros sensíveis. Os cookies de sessão deverão utilizar atributos de segurança adequados ao ambiente de execução.
- **RNF-S10 — Proteção de credenciais:** as credenciais do banco de dados e demais segredos não deverão ser publicados no repositório de código-fonte.
- **RNF-S11 — Comunicação segura:** em ambiente de produção, o sistema deverá utilizar HTTPS para proteger os dados transmitidos entre o navegador e o servidor.

### Regras de negócio relacionadas à segurança

- **RN-S01:** somente usuários autenticados poderão acessar as funcionalidades financeiras privadas.
- **RN-S02:** toda operação sobre uma transação deverá verificar se ela pertence ao usuário autenticado.
- **RN-S03:** o sistema não deverá armazenar senhas em texto puro.
- **RN-S04:** o logout deverá encerrar a sessão autenticada, impedindo o acesso às páginas privadas sem nova autenticação.

### Critérios de verificação

A segurança deverá ser verificada por meio de testes de login, logout, acesso não autorizado, isolamento das transações entre usuários, validação de formulários e tratamento seguro dos dados exibidos nas páginas.

