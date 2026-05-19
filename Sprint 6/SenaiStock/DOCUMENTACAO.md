# Documentação Técnica — SenaiStock

**Projeto:** Controle de Estoque de Livros Didáticos  
**Instituição:** SENAI Limeira/SP  
**Equipe:** Diogo Scherrer, Gabriel Furtunato, Gabriel Xavier  
**Ano:** 2026  
**Stack:** Laravel 13 + Filament 5 + MySQL + PHP 8.3

---

## Índice

1. [Visão Geral do Sistema](#1-visão-geral-do-sistema)
2. [Estrutura do Projeto](#2-estrutura-do-projeto)
3. [Banco de Dados](#3-banco-de-dados)
4. [Autenticação e Perfis](#4-autenticação-e-perfis)
5. [Painel Administrativo (Filament)](#5-painel-administrativo-filament)
6. [API RESTful](#6-api-restful)
7. [Serviços (Services)](#7-serviços-services)
8. [Regras de Negócio Implementadas](#8-regras-de-negócio-implementadas)
9. [Log de Desenvolvimento](#9-log-de-desenvolvimento)

---

## 1. Visão Geral do Sistema

O SenaiStock resolve um problema real do SENAI: o almoxarifado recebia grandes remessas de livros didáticos, mas não tinha controle das saídas. Isso causava rupturas — o estoque zerava sem aviso e as turmas ficavam sem material.

**O sistema oferece:**
- Cadastro de livros com ISBN único por título
- Registro de entradas (abastecimento) e saídas (retiradas para turmas)
- Saldo atualizado em tempo real
- Alerta de livros com estoque abaixo do mínimo
- Histórico completo de todas as movimentações
- Controle de acesso por perfil (Almoxarife / Coordenador)

**Acesso:**
- Painel administrativo visual: `http://localhost/SenaiStock/public/admin`
- API RESTful: `http://localhost/SenaiStock/public/api/...`

---

## 2. Estrutura do Projeto

A estrutura segue o padrão do Laravel com adições do Filament:

```
SenaiStock/
├── app/
│   ├── Filament/              ← Painel admin (Resources, Pages, Widgets)
│   ├── Http/
│   │   ├── Controllers/Api/   ← Controllers da API REST
│   │   └── Requests/          ← Validação de entrada (Form Requests)
│   ├── Models/                ← Modelos Eloquent (User, Livro, Movimentacao)
│   ├── Services/              ← Lógica de negócio (EstoqueService)
│   └── Providers/Filament/    ← Configuração do painel Filament
├── database/
│   ├── migrations/            ← Estrutura do banco de dados
│   └── seeders/               ← Dados iniciais para testes
├── routes/
│   ├── api.php                ← Rotas da API REST
│   └── web.php                ← Rotas web
├── CLAUDE.md                  ← Guia para desenvolvimento assistido por IA
└── DOCUMENTACAO.md            ← Este arquivo
```

### Por que essa estrutura?

- **Filament** cuida do painel visual completo (CRUD, dashboard, filtros) sem precisar criar HTML manualmente.
- **Controllers/Api** são finos — só recebem a requisição, chamam o Service e retornam JSON.
- **Services** concentram toda a lógica de negócio (validação de saldo, transações), isolando do controller.
- **Form Requests** validam os dados de entrada antes de chegarem ao controller.

---

## 3. Banco de Dados

### 3.1 Diagrama de Relacionamentos

```
usuarios (users)
    │
    │ 1:N (um usuário faz várias movimentações)
    ▼
movimentacoes ◄──── N:1 ──── livros
                              (um livro tem várias movimentações)
```

### 3.2 Tabela `users` (padrão Laravel + campo `perfil`)

> Arquivo: `database/migrations/0001_01_01_000000_create_users_table.php` (padrão Laravel)  
> Modificação: adicionado campo `perfil` via migration própria.

| Campo | Tipo | Descrição |
|---|---|---|
| id | INT PK | Identificador único |
| name | VARCHAR(100) | Nome do usuário |
| email | VARCHAR(150) UNIQUE | Email de login |
| password | VARCHAR(255) | Senha com hash bcrypt |
| perfil | ENUM | `almoxarife` ou `coordenador` |
| email_verified_at | TIMESTAMP | Data de verificação do email |
| remember_token | VARCHAR(100) | Token "lembrar-me" |
| created_at / updated_at | TIMESTAMP | Controle automático do Laravel |

### 3.3 Tabela `livros`

> Arquivo: `database/migrations/[data]_create_livros_table.php`

| Campo | Tipo | Descrição |
|---|---|---|
| id | INT PK | Identificador único |
| titulo | VARCHAR(200) | Título do livro |
| isbn | VARCHAR(20) UNIQUE | ISBN — deve ser único (RN3) |
| materia | VARCHAR(188) | Disciplina/matéria do livro |
| saldo_atual | INT DEFAULT 0 | Quantidade disponível em estoque |
| estoque_minimo | INT DEFAULT 10 | Limite para alerta de baixo estoque (RN6) |
| created_at / updated_at | TIMESTAMP | Controle automático |

**Índices:** `isbn` (busca frequente), `materia`

### 3.4 Tabela `movimentacoes`

> Arquivo: `database/migrations/[data]_create_movimentacoes_table.php`

| Campo | Tipo | Descrição |
|---|---|---|
| id | INT PK | Identificador único |
| livro_id | INT FK → livros.id | Qual livro foi movimentado |
| user_id | INT FK → users.id | Quem realizou a operação (RN4) |
| tipo | ENUM | `entrada` ou `saida` |
| quantidade | INT UNSIGNED | Quantidade movimentada (deve ser > 0, RN2) |
| observacao | TEXT | Justificativa / turma (obrigatório em saídas) |
| data_hora | TIMESTAMP | Data e hora da movimentação (RN4) |
| created_at / updated_at | TIMESTAMP | Controle automático |

**Índices:** `data_hora`, `tipo`, `livro_id`, `user_id`

---

## 4. Autenticação e Perfis

> Implementado em: Sprint 1

**Tecnologia:** Laravel Sanctum (tokens de API)

### Fluxo de autenticação
1. Usuário envia `POST /api/auth/login` com email e senha
2. O sistema valida as credenciais e retorna um token Sanctum
3. Todas as rotas protegidas exigem o header: `Authorization: Bearer {token}`
4. `POST /api/auth/logout` invalida o token

### Perfis e permissões

| Perfil | O que pode fazer |
|---|---|
| `almoxarife` | Cadastrar livros, registrar entradas e saídas |
| `coordenador` | Consultar relatórios, monitorar baixo estoque, registrar movimentações |

O controle é feito via **middleware** nas rotas da API e via **política de acesso** no Filament.

---

## 5. Painel Administrativo (Filament)

> Acesso: `/admin` — requer login

O Filament gera automaticamente interfaces de CRUD completas a partir dos **Resources**.

### Resources criados

| Resource | Rota | Quem acessa |
|---|---|---|
| `LivroResource` | `/admin/livros` | Almoxarife (CRUD) e Coordenador (somente leitura) |
| `MovimentacaoResource` | `/admin/movimentacoes` | Ambos (registrar e visualizar) |
| `UserResource` | `/admin/users` | Coordenador (CRUD) e Almoxarife (somente leitura) |

### Widgets do Dashboard

| Widget | Função |
|---|---|
| `ResumoEstoqueWidget` | 4 cards: total de títulos, livros em baixo estoque, entradas e saídas do dia |
| `BaixoEstoqueWidget` | Tabela com livros cujo saldo está abaixo do mínimo configurado |
| `MovimentacoesRecentesWidget` | Tabela com as 8 movimentações mais recentes |

---

## 6. API RESTful

> Base URL: `/api`  
> Autenticação: Bearer Token (Sanctum)  
> Formato: JSON

### Endpoints implementados

| Método | Rota | Autenticação | Descrição | Status |
|---|---|---|---|---|
| POST | `/api/auth/login` | Não | Login, retorna token | ⏳ Pendente |
| POST | `/api/auth/logout` | Sim | Invalida o token | ⏳ Pendente |
| GET | `/api/livros` | Sim | Lista livros com filtros e paginação | ⏳ Pendente |
| POST | `/api/livros` | Sim (almoxarife) | Cadastra novo livro | ⏳ Pendente |
| GET | `/api/livros/{id}` | Sim | Dados + saldo de um livro | ⏳ Pendente |
| POST | `/api/stock/entries` | Sim (almoxarife) | Registra entrada de estoque | ⏳ Pendente |
| POST | `/api/stock/exits` | Sim | Registra saída de estoque | ⏳ Pendente |
| GET | `/api/stock/low` | Sim | Livros abaixo do mínimo | ⏳ Pendente |
| GET | `/api/movimentacoes` | Sim | Histórico paginado com filtros | ⏳ Pendente |

### Padrão de resposta JSON

**Sucesso:**
```json
{
    "success": true,
    "data": { ... },
    "message": "Operação realizada com sucesso"
}
```

**Erro:**
```json
{
    "success": false,
    "message": "Estoque insuficiente",
    "errors": { ... }
}
```

### Status HTTP utilizados
| Código | Quando usar |
|---|---|
| 200 | Sucesso em GET, PUT |
| 201 | Sucesso em POST (criação) |
| 400 | Erro de regra de negócio (ex: estoque insuficiente) |
| 401 | Não autenticado (sem token) |
| 403 | Sem permissão para a ação |
| 404 | Recurso não encontrado |
| 409 | Conflito (ex: ISBN duplicado) |
| 422 | Erro de validação dos campos |

---

## 7. Serviços (Services)

### EstoqueService

> Arquivo: `app/Services/EstoqueService.php`  
> Status: ⏳ Pendente

Centraliza toda a lógica de estoque para não poluir os controllers.

| Método | Descrição |
|---|---|
| `registrarEntrada(Livro, int quantidade, User)` | Soma ao saldo, registra movimentação em transação |
| `registrarSaida(Livro, int quantidade, string observacao, User)` | Valida saldo, subtrai, registra movimentação em transação |
| `validarSaldo(Livro, int quantidade)` | Verifica se há saldo suficiente (RN1) |
| `listarBaixoEstoque(int minimo = 10)` | Retorna livros com saldo abaixo do mínimo |

---

## 8. Regras de Negócio Implementadas

| Regra | Descrição | Onde é aplicada | Status |
|---|---|---|---|
| RN1 | Estoque não pode ficar negativo | `EstoqueService::registrarSaida()` | ✅ |
| RN2 | Quantidade deve ser > 0 | `EstoqueService` + formulário (`minValue(1)`) | ✅ |
| RN3 | ISBN deve ser único | Migration (UNIQUE) + `LivroResource` (unique rule) | ✅ |
| RN4 | Movimentação registra usuário e timestamp | `EstoqueService` (user_id + data_hora = now()) | ✅ |
| RN5 | Operações críticas em transação | `EstoqueService` (`DB::transaction`) | ✅ |
| RN6 | Nível mínimo padrão = 10 | `EstoqueService::listarBaixoEstoque()` + `BaixoEstoqueWidget` | ✅ |

---

## 9. Log de Desenvolvimento

### Configuração inicial (base do projeto)
**Data:** 2026-05-18  
**O que foi feito:**
- Projeto iniciado com `laravel new SenaiStock` usando o preset Filament
- **Laravel 13** instalado com PHP 8.3
- **Filament 5.6** configurado no painel `/admin` com cor primária Amber
- Banco configurado inicialmente com SQLite (driver padrão do Laravel)
- Migrations padrão do Laravel aplicadas: `users`, `cache`, `jobs`, `sessions`
- Estrutura de pastas base do Laravel criada
- `AdminPanelProvider` configurado com auto-descoberta de Resources/Pages/Widgets

**Estado do banco após esta etapa:**
- Tabelas existentes: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`
- Tabelas do projeto: nenhuma ainda

**Próximo passo:** Criar Resources do Filament para Livros e Movimentações.

---

### Sprint 1 — MySQL + Tela de Login
**Data:** 2026-05-18  
**O que foi feito:**

#### 1. Migração para MySQL
- `.env` atualizado: `DB_CONNECTION=mysql`, banco `senaistock`, usuário `root`
- Banco `senaistock` criado no MySQL 8.4 (Laragon) com charset `utf8mb4`
- `php artisan migrate` rodado — tabelas padrão do Laravel criadas no MySQL:
  `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`

#### 2. Usuários de teste (Seeder)
- `database/seeders/DatabaseSeeder.php` atualizado com dois usuários iniciais:

| E-mail | Senha | Perfil futuro |
|---|---|---|
| almoxarife@senai.br | senha123 | almoxarife |
| coordenador@senai.br | senha123 | coordenador |

- Utiliza `firstOrCreate` para evitar duplicatas ao rodar o seeder mais de uma vez

#### 3. Tela de login personalizada
- Arquivo criado: `resources/views/auth/login.blade.php`
- CSS próprio embutido na view (sem dependência do Vite/Tailwind por enquanto)
- Design simples com cor amber (padrão do Filament/SENAI), ícone SVG de livro
- Exibe erros de autenticação em português
- Usa `@csrf` (proteção contra ataques CSRF)
- Campo e-mail preserva o valor digitado em caso de erro (`old('email')`)

#### 4. LoginController
- Arquivo criado: `app/Http/Controllers/Auth/LoginController.php`
- Três métodos:
  - `showLogin()` — exibe a view; redireciona para `/admin` se já autenticado
  - `login()` — valida campos, chama `Auth::attempt()`, regenera sessão, redireciona para `/admin`
  - `logout()` — invalida sessão, regenera token CSRF, redireciona para `/login`
- Mensagens de validação e erro em português

#### 5. Rotas web
- `routes/web.php` atualizado:
  - `GET /` → redireciona para `/login`
  - `GET /login` → exibe tela de login (nome: `login`)
  - `POST /login` → processa autenticação
  - `POST /logout` → encerra sessão (nome: `logout`)

#### Fluxo completo de autenticação
```
Usuário acessa /  →  redireciona para /login
         ↓
  Preenche e-mail + senha  →  POST /login
         ↓
  LoginController::login() valida os campos
         ↓
  Auth::attempt() consulta a tabela users no MySQL
         ↓
  ✅ Credenciais corretas  →  redireciona para /admin (painel Filament)
  ❌ Credenciais erradas   →  volta para /login com mensagem de erro
```

---

### Sprint 1.1 — Controle de Perfil e Nível de Acesso (RF2)
**Data:** 2026-05-18

#### Problema identificado
Os usuários existiam no banco sem distinção de perfil — qualquer um autenticado acessava tudo no painel Filament.

#### O que foi implementado

**1. Enum `PerfilUsuario`**
- Arquivo: `app/Enums/PerfilUsuario.php`
- PHP Enum (tipo: string) com dois casos: `Almoxarife` e `Coordenador`
- Método `label()` — retorna o nome legível para exibição na interface
- Método `cor()` — retorna a cor de destaque para o painel (`warning` / `info`)
- O Enum garante que apenas os valores válidos sejam aceitos no banco

**2. Migration `add_perfil_to_users_table`**
- Arquivo: `database/migrations/2026_05_18_172402_add_perfil_to_users_table.php`
- Adiciona coluna `perfil` ENUM(`almoxarife`, `coordenador`) na tabela `users`
- Coluna `nullable` para não quebrar registros já existentes
- Posicionada após o campo `password` no banco

**3. Model `User` atualizado**
- Arquivo: `app/Models/User.php`
- Implementa a interface `FilamentUser` (exigida pelo Filament para controle de acesso)
- Campo `perfil` adicionado ao `$fillable`
- Cast automático: `'perfil' => PerfilUsuario::class` — o campo retorna o Enum, não string
- Método `canAccessPanel(Panel $panel): bool` — bloqueia usuários sem perfil definido
- Métodos helpers: `isAlmoxarife()` e `isCoordenador()` — simplificam verificações no código

**4. Seeder atualizado**
- Arquivo: `database/seeders/DatabaseSeeder.php`
- Alterado de `firstOrCreate` para `updateOrCreate` — atualiza usuários já existentes
- Agora inclui o campo `perfil` com o Enum correto para cada usuário

#### Estado do banco após esta sprint

Tabela `users`:
```
id | name              | email                  | perfil
1  | Almoxarife SENAI  | almoxarife@senai.br    | almoxarife
2  | Coordenador SENAI | coordenador@senai.br   | coordenador
```

#### Como o controle de acesso funciona

```
Usuário tenta acessar /admin
         ↓
Filament chama $user->canAccessPanel($panel)
         ↓
canAccessPanel() verifica se $this->perfil !== null
         ↓
✅ perfil definido  →  acesso liberado ao painel
❌ perfil null      →  acesso negado (403)
```

**Restrição por recurso (próximas sprints):**  
Quando os Resources de Livros e Movimentações forem criados, cada um terá métodos como `canCreate()`, `canDelete()` etc., que usarão `$user->isAlmoxarife()` para controlar ações individuais por perfil.

---

### Sprint 2 — Cadastro de Livros (RF3 + RF4)
**Data:** 2026-05-18

#### O que foi implementado

**1. Migration `create_livros_table`**
- Arquivo: `database/migrations/2026_05_18_173529_create_livros_table.php`
- Cria a tabela `livros` com:
  - `titulo` VARCHAR(200) — com índice para busca
  - `isbn` VARCHAR(20) UNIQUE — garante RN3 no nível do banco
  - `materia` VARCHAR(188) — com índice para busca
  - `saldo_atual` INT DEFAULT 0 — atualizado por movimentações
  - `estoque_minimo` INT DEFAULT 10 — limite de alerta (RN6)
  - `timestamps` — created_at e updated_at automáticos

**2. Model `Livro`**
- Arquivo: `app/Models/Livro.php`
- `$fillable`: titulo, isbn, materia, saldo_atual, estoque_minimo
- Casts: saldo_atual e estoque_minimo como `integer`
- Relacionamento `movimentacoes()` → `HasMany` com Movimentacao (preparado para Sprint 3)
- Helper `estaBaixoEstoque()` → retorna true se saldo <= estoque_minimo
- Helper `temSaldoSuficiente(int $quantidade)` → usado pelo EstoqueService (Sprint 3)

**3. LivroResource (Filament)**
- Arquivo principal: `app/Filament/Resources/LivroResource.php`
- Páginas: `Pages/ListLivros.php`, `Pages/CreateLivro.php`, `Pages/EditLivro.php`
- Rota no painel: `/admin/livros`

**Formulário de cadastro/edição:**
- Seção "Dados do Livro": Título (2 colunas), ISBN (unique), Matéria
- Seção "Configuração de Estoque": Estoque Mínimo (padrão: 10)
- `saldo_atual` NÃO aparece no formulário — só é alterado por movimentações

**Tabela de listagem:**
- Colunas: Título (bold), ISBN (copiável), Matéria, Saldo (badge colorido), Mínimo, Cadastrado em
- Badge de saldo: 🟢 verde (ok) | 🟡 amarelo (abaixo do mínimo) | 🔴 vermelho (zerado)
- Busca: título, ISBN e matéria
- Filtros: "Somente baixo estoque" e "Sem estoque (saldo = 0)"
- Ordenação padrão: título A→Z

**Controle de acesso por perfil (RF2):**
| Ação | Almoxarife | Coordenador |
|---|---|---|
| Visualizar lista | ✅ | ✅ |
| Criar livro | ✅ | ❌ |
| Editar livro | ✅ | ❌ |
| Excluir livro | ✅ | ❌ |

#### Observação técnica — Filament 5
Filament 5 mudou a API em relação ao Filament 3/4:
- `Filament\Forms\Form` → `Filament\Schemas\Schema`
- `Filament\Forms\Components\Section` → `Filament\Schemas\Components\Section`
- Assinatura do método: `form(Schema $schema): Schema`
- Propriedades de navegação com tipos incompatíveis (`$navigationIcon`, `$navigationGroup`) devem ser substituídas por métodos override

---

### Sprint 3 — Entrada e Saída de Estoque (RF5, RF6, RF9)
**Data:** 2026-05-18

#### O que foi implementado

**1. Enum `TipoMovimentacao`**
- Arquivo: `app/Enums/TipoMovimentacao.php`
- Casos: `Entrada = 'entrada'` e `Saida = 'saida'`
- Métodos: `label()` (nome legível) e `cor()` (cor do badge no painel)

**2. Migration `create_movimentacoes_table`**
- Arquivo: `database/migrations/2026_05_18_175555_create_movimentacoes_table.php`
- Campos: `livro_id` (FK), `user_id` (FK), `tipo` (ENUM), `quantidade` (UNSIGNED INT), `observacao` (TEXT nullable), `data_hora` (TIMESTAMP)
- `onDelete('restrict')` em ambas as FKs — impede excluir livro ou usuário com histórico
- Índices em: `tipo`, `data_hora`, `livro_id`, `user_id`

**3. Model `Movimentacao`**
- Arquivo: `app/Models/Movimentacao.php`
- Relacionamentos: `livro()` → BelongsTo Livro | `user()` → BelongsTo User
- Cast automático: `tipo` → `TipoMovimentacao`, `data_hora` → datetime

**4. `EstoqueService` — coração do sistema**
- Arquivo: `app/Services/EstoqueService.php`
- `registrarEntrada(Livro, int, User, string)`: soma ao saldo em transação (RN5)
- `registrarSaida(Livro, int, User, string)`: valida saldo (RN1), subtrai em transação (RN5)
- `listarBaixoEstoque(?int)`: retorna livros abaixo do mínimo (RN6)
- Todas as operações registram `user_id` e `data_hora` automaticamente (RN4)
- Lança `\DomainException` para estoque insuficiente (RN1)
- Lança `\InvalidArgumentException` para quantidade inválida (RN2)

**5. `MovimentacaoResource` (Filament)**
- Rota: `/admin/movimentacoes`
- Formulário: Select de livro (com saldo atual), Select de tipo, Quantidade, Observação
- Observação obrigatória quando tipo = saída (RF6) — validação reativa com `->live()`
- Tabela: data/hora, tipo (badge colorido), livro, quantidade, observação, quem registrou
- Filtros: por tipo (entrada/saída) e por livro
- **Sem página de edição** — movimentações são imutáveis (RN4)

**Como o fluxo funciona:**
```
Usuário preenche o formulário no Filament
         ↓
CreateMovimentacao::handleRecordCreation() intercepta
         ↓
Chama EstoqueService::registrarEntrada() ou registrarSaida()
         ↓
EstoqueService valida regras (RN1, RN2)
         ↓
DB::transaction() {
    livro->increment/decrement('saldo_atual')  ← atualiza saldo
    Movimentacao::create([...])                ← grava histórico
}
         ↓
✅ Sucesso → notificação + volta para /admin/movimentacoes
❌ Erro    → notificação de erro + nada é salvo (rollback)
```

**Regras de negócio aplicadas:**
| Regra | Onde é aplicada |
|---|---|
| RN1 — Estoque não fica negativo | `EstoqueService::registrarSaida()` |
| RN2 — Quantidade > 0 | `EstoqueService` + formulário (`minValue(1)`) |
| RN4 — Registra usuário e timestamp | `EstoqueService` (user_id + data_hora = now()) |
| RN5 — Operações atômicas | `DB::transaction()` em ambos os métodos |
| RN6 — Nível mínimo | `EstoqueService::listarBaixoEstoque()` |

---

---

### Sprint 4 — Dashboard e Widgets
**Data:** 2026-05-18

#### O que foi implementado

**3 widgets customizados** adicionados ao dashboard (`/admin`):

**1. `ResumoEstoqueWidget`** — `app/Filament/Widgets/ResumoEstoqueWidget.php`
- Tipo: `StatsOverviewWidget` (cards de estatísticas)
- Exibe 4 indicadores em cards:
  - **Títulos Cadastrados** — total de livros no sistema
  - **Baixo Estoque** — quantidade de livros com saldo ≤ mínimo (badge vermelho se > 0)
  - **Entradas Hoje** — movimentações de entrada do dia
  - **Saídas Hoje** — movimentações de saída do dia
- Atualização automática a cada 30 segundos

**2. `BaixoEstoqueWidget`** — `app/Filament/Widgets/BaixoEstoqueWidget.php`
- Tipo: `TableWidget`
- Lista todos os livros com `saldo_atual <= estoque_minimo`
- Ordenados por saldo crescente (mais críticos primeiro)
- Badge vermelho para saldo = 0, amarelo para abaixo do mínimo
- Mensagem amigável quando todos os livros estão ok
- Atualização automática a cada 60 segundos

**3. `MovimentacoesRecentesWidget`** — `app/Filament/Widgets/MovimentacoesRecentesWidget.php`
- Tipo: `TableWidget`
- Exibe as 8 movimentações mais recentes com data relativa ("há 2 horas")
- Colunas: quando, tipo (badge colorido), livro, quantidade, quem registrou
- Atualização automática a cada 30 segundos

**Polimento do painel:**
- `AdminPanelProvider` atualizado: `->brandName('SenaiStock')` — nome exibido no cabeçalho
- Widget padrão `FilamentInfoWidget` removido (irrelevante para o projeto)

---

### Sprint 5 — Histórico + Permissões (RF2, RF7)
**Data:** 2026-05-18

#### O que foi implementado

**1. Filtro por período no histórico de movimentações**
- Arquivo: `app/Filament/Resources/MovimentacaoResource.php`
- Novo filtro "Período" com dois campos `DatePicker` (De / Até)
- Filtra pela coluna `data_hora` com `whereDate`
- Funciona de forma independente: só "De", só "Até", ou ambos juntos
- Exibe indicador visual no cabeçalho da tabela quando o filtro está ativo

**2. `UserResource` — Gerenciamento de usuários pelo painel**
- Arquivo principal: `app/Filament/Resources/UserResource.php`
- Páginas: `Pages/ListUsers.php`, `Pages/CreateUser.php`, `Pages/EditUser.php`
- Rota: `/admin/users` (grupo "Administração" no menu lateral)

**Formulário:**
- Nome completo, e-mail (com validação de unicidade), perfil (Select com Enum)
- Senha: obrigatória na criação, **opcional na edição** — se deixada em branco, a senha atual é preservada
- Senha sempre salva com `Hash::make()` (bcrypt) — nunca em texto puro

**Controle de acesso:**
| Ação | Almoxarife | Coordenador |
|---|---|---|
| Visualizar lista | ✅ | ✅ |
| Criar usuário | ❌ | ✅ |
| Editar usuário | ❌ | ✅ |
| Excluir usuário | ❌ | ✅ (exceto a si mesmo) |

**Proteção especial:** O coordenador não consegue excluir o próprio usuário — verificação via `$usuario->id !== $record->id`.

---

### Sprint 6 — Finalização
**Data:** 2026-05-18

#### O que foi implementado

**1. Seeders completos para demonstração**

`LivroSeeder` — `database/seeders/LivroSeeder.php`
- 13 livros didáticos reais de cursos do SENAI (Informática, Eletrotécnica, Administração, Segurança)
- Saldos variados para demonstrar os cenários:
  - Livros ok (saldo acima do mínimo)
  - Livros em alerta (saldo abaixo do mínimo)
  - Livros zerados (saldo = 0)
- Usa `updateOrCreate` pelo ISBN — pode ser re-executado sem duplicar

`MovimentacaoSeeder` — `database/seeders/MovimentacaoSeeder.php`
- 12 movimentações de exemplo dos últimos 30 dias (5 entradas + 7 saídas)
- Timestamps retroativos para simular histórico realista
- Turmas reais dos cursos técnicos do SENAI como observação
- Skip automático se já houver movimentações no banco

`DatabaseSeeder` — ordem de execução:
```
1. Usuários (diretamente)  →  2. LivroSeeder  →  3. MovimentacaoSeeder
```

**2. Polimento do painel**
- Nome "SenaiStock" exibido no cabeçalho do painel (`brandName`)
- Usuários de demonstração com nomes realistas: "Carlos Almoxarife" e "Prof. Ana Coordenadora"

**Para resetar e popular o banco:**
```bash
php artisan migrate:fresh --seed
```

---

## 10. Credenciais de Acesso (Demonstração)

| Perfil | E-mail | Senha | Permissões |
|---|---|---|---|
| Almoxarife | almoxarife@senai.br | senha123 | Cadastra livros, registra movimentações |
| Coordenador | coordenador@senai.br | senha123 | Gerencia usuários, monitora estoque |

**URL do painel:** `http://localhost/SenaiStock/public/admin`

---

## 11. Resumo das Funcionalidades por Sprint

| Sprint | Funcionalidade | Status |
|---|---|---|
| Base | Laravel 13 + Filament 5 + MySQL | ✅ |
| 1 | Tela de login + autenticação + perfis | ✅ |
| 2 | Cadastro de livros (CRUD completo) | ✅ |
| 3 | Registro de entradas e saídas de estoque | ✅ |
| 4 | Dashboard com widgets de monitoramento | ✅ |
| 5 | Histórico com filtro por período + gerenciamento de usuários | ✅ |
| 6 | Seeders completos + polimento final | ✅ |

---

> **Legenda de status:** ✅ Concluído | 🔄 Em andamento | ⏳ Pendente
