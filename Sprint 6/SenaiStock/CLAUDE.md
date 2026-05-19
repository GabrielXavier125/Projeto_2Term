# CLAUDE.md — SenaiStock

Guia de contexto para desenvolvimento assistido por IA. Leia este arquivo inteiro antes de qualquer ação no projeto.

---

## 1. Sobre o Projeto

**SenaiStock** é um sistema de controle de estoque de livros didáticos para o SENAI Limeira/SP.
Projeto escolar desenvolvido por Diogo Scherrer, Gabriel Furtunato e Gabriel Xavier (2026).

**Problema real:** O almoxarifado sabe quantos livros chegam, mas perde o controle das saídas, causando rupturas de estoque.

**Solução:** Painel administrativo (Filament) + API RESTful para registrar entradas, saídas, consultar saldos e monitorar estoque baixo.

---

## 2. Stack Tecnológica

| Camada | Tecnologia | Versão |
|---|---|---|
| Framework | Laravel | 13.x |
| Admin Panel | Filament | 5.x |
| Linguagem | PHP | 8.3+ |
| Banco de dados | MySQL | (via Laragon) |
| Frontend | Blade + Tailwind CSS 4 | — |
| Build tool | Vite | — |
| Auth API | Laravel Sanctum | — |

---

## 3. Princípios de Desenvolvimento

### 3.1 Ritmo — Parte por Parte
- Desenvolvemos **incrementalmente**, uma funcionalidade de cada vez.
- Nunca pular etapas. Cada parte deve estar funcionando antes de avançar.
- Seguir a ordem das Sprints definidas na documentação do projeto.

### 3.2 Estrutura — Respeitar o que existe
- Aproveitar ao máximo a estrutura padrão do Laravel e do Filament.
- Não criar abstrações desnecessárias. Só adicionar o que a funcionalidade exige.
- Filament Resources ficam em `app/Filament/Resources/`.
- Services ficam em `app/Services/`.
- Form Requests ficam em `app/Http/Requests/`.

### 3.3 Comentários — Obrigatórios
- **Todo arquivo criado deve ter comentários explicativos.**
- Comentar: o propósito da classe, cada método importante, regras de negócio aplicadas.
- Usar PHPDoc (`/** */`) em classes e métodos públicos.
- Comentários em **português**, já que é um projeto escolar que será apresentado.

### 3.4 Documentação — Sempre Atualizar
- Ao terminar cada parte do desenvolvimento, atualizar o arquivo `DOCUMENTACAO.md`.
- O arquivo de documentação serve como diário de desenvolvimento e guia de apresentação.

---

## 4. Banco de Dados

**Driver:** MySQL (configurado via Laragon)

### Tabelas do Projeto

#### `users` (tabela padrão do Laravel — com adição do campo `perfil`)
```
id | name | email (unique) | email_verified_at | password | perfil (enum) | remember_token | timestamps
perfil: 'almoxarife' | 'coordenador'
```

#### `livros`
```
id | titulo | isbn (unique, indexed) | materia | saldo_atual (int default 0) | estoque_minimo (int default 10) | timestamps
```

#### `movimentacoes`
```
id | livro_id (FK) | user_id (FK) | tipo (enum: 'entrada','saida') | quantidade (unsigned int) | observacao (text) | data_hora (timestamp) | timestamps
Índices: data_hora, tipo
Constraints: quantidade > 0, saldo_atual >= 0
```

### Regras de Negócio (implementar sempre)
- **RN1:** Saída bloqueada se quantidade > saldo atual (nunca estoque negativo)
- **RN2:** Quantidade deve ser > 0 em toda movimentação
- **RN3:** ISBN único na tabela `livros`
- **RN4:** Toda movimentação registra o usuário autenticado e timestamp
- **RN5:** Entrada e saída devem ser executadas em transação (`DB::transaction`)
- **RN6:** Estoque mínimo padrão = 10 (configurável)

---

## 5. Perfis de Usuário

| Perfil | Permissões |
|---|---|
| `almoxarife` | Cadastrar livros, registrar entradas e saídas |
| `coordenador` | Consultar relatórios, monitorar baixo estoque, registrar movimentações |

---

## 6. Estrutura de Pastas Relevante

```
app/
├── Filament/
│   ├── Resources/          ← Resources do painel admin (Livro, Movimentacao, User)
│   │   └── [Nome]Resource/
│   │       └── Pages/      ← List, Create, Edit pages
│   ├── Pages/              ← Páginas customizadas do painel
│   └── Widgets/            ← Widgets do dashboard
├── Http/
│   ├── Controllers/
│   │   └── Api/            ← Controllers da API RESTful
│   └── Requests/           ← Form Requests com validação
├── Models/                 ← Eloquent Models (User, Livro, Movimentacao)
├── Services/               ← Lógica de negócio (EstoqueService, AuthService)
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php

database/
├── migrations/             ← Todas as migrations do projeto
├── seeders/                ← Seeders para dados iniciais (users de teste)
└── factories/

routes/
├── web.php                 ← Rotas web (Filament usa estas)
└── api.php                 ← Rotas da API RESTful (Sanctum)
```

---

## 7. API RESTful — Endpoints Planejados

```
POST   /api/auth/login          → Login (retorna token Sanctum)
POST   /api/auth/logout         → Logout (invalida token)

GET    /api/livros              → Listagem com paginação e filtros
POST   /api/livros              → Cadastrar livro
GET    /api/livros/{id}         → Dados + saldo atual de um livro

POST   /api/stock/entries       → Entrada de estoque (RF5)
POST   /api/stock/exits         → Saída de estoque (RF6)
GET    /api/stock/low           → Livros abaixo do estoque mínimo (RF8)

GET    /api/movimentacoes       → Histórico paginado (filtros: livro, tipo, período, usuário)
```

---

## 8. Ordem de Desenvolvimento (Sprints)

1. **Sprint 1 — Base:** Configuração MySQL, migrations, models, Sanctum, autenticação API
2. **Sprint 2 — Livros:** Model Livro, migration, CRUD no Filament, endpoint API listagem/cadastro
3. **Sprint 3 — Estoque:** EstoqueService, entrada e saída com validações e transações
4. **Sprint 4 — Painel:** Filament Resources completos, widgets de dashboard, baixo estoque
5. **Sprint 5 — Histórico + Permissões:** Movimentações com filtros, controle de perfil por middleware
6. **Sprint 6 — Finalização:** Seeders, testes via Postman, ajustes, documentação final

---

## 9. Como Continuar em uma Nova Conversa

Ao iniciar uma nova conversa, diga ao Claude:

> "Estamos desenvolvendo o SenaiStock. Leia o CLAUDE.md e o DOCUMENTACAO.md para se sincronizar com o que já foi feito."

O Claude deve:
1. Ler este arquivo (`CLAUDE.md`)
2. Ler `DOCUMENTACAO.md` para saber o que já foi implementado
3. Continuar da próxima Sprint/tarefa pendente sem refazer o que já existe

---

## 10. Padrões de Código

- **Idioma dos comentários:** Português
- **Idioma do código:** Inglês (nomes de variáveis, métodos, classes)
- **PHPDoc obrigatório** em todas as classes e métodos públicos
- **Form Requests** para validação (nunca validar direto no controller)
- **Services** para lógica de negócio (controllers só orquestram)
- **Respostas JSON** padronizadas com status HTTP corretos
- Seguir **PSR-12** (o Laravel Pint já cuida disso)
