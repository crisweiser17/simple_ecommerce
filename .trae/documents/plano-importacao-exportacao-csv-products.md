# Plano: Importação/Exportação CSV em Admin > Products

## Objetivo
Adicionar no admin (aba Products) três capacidades: baixar CSV modelo, importar CSV de produtos e exportar produtos para CSV, garantindo compatibilidade com o banco e com o fluxo atual de CRUD.

## Escopo funcional
1. Disponibilizar ação de **Download CSV Modelo** na aba Products.
2. Disponibilizar ação de **Importar CSV** com upload de arquivo.
3. Disponibilizar ação de **Exportar CSV** com todos os produtos atuais.
4. Validar e persistir dados com segurança, mantendo comportamento consistente com o cadastro manual.

## Levantamento técnico já confirmado
- A listagem/admin de produtos fica em `templates/admin/dashboard.php` (tab Products).
- As rotas admin ficam em `index.php` (switch principal).
- Persistência de produtos está em `src/products.php` (`createProduct`, `updateProduct`, etc.).
- O schema `products` contém no mínimo: `name` (NOT NULL), `sku`, `price`, `image_url`, `category_id`, `short_desc`, `long_desc`, `pdf_url`, `created_at`.
- O CRUD atual usa também `pdf_label`, mas essa coluna não está garantida em todos scripts de criação/migração; isso será tratado para evitar falha na importação.

## Definição do formato CSV
### Colunas do CSV modelo
- `name` (obrigatória)
- `sku` (recomendada)
- `price` (recomendada, numérica)
- `category` (texto da categoria; usado para resolver `category_id`)
- `image_url` (opcional)
- `short_desc` (opcional)
- `long_desc` (opcional)
- `pdf_url` (opcional)
- `pdf_label` (opcional)

### Regras de importação
- Separador padrão: vírgula; aceitar arquivos UTF-8.
- Primeira linha obrigatoriamente como cabeçalho.
- Mapeamento por nome de coluna (não por posição).
- Resolver categoria por nome (`category`) com normalização de espaços/case; se não existir, criar categoria com slug seguro.
- `name` vazio invalida a linha.
- `price` vazio vira `NULL`; se preenchido, validar como número decimal.
- Campos ausentes no CSV serão preenchidos com valor padrão seguro (string vazia ou `NULL`) para evitar `Undefined array key`.
- Importação em modo upsert por `sku` quando presente:
  - se já existir produto com mesmo `sku`, atualizar;
  - se não existir, criar.
  - sem `sku`, criar novo registro.
- Retornar resumo ao admin: total processado, inseridos, atualizados, ignorados e erros por linha.

## Implementação planejada
1. **Criar camada de serviço CSV de produtos** (novo arquivo em `src/`):
   - geração de CSV modelo;
   - parser de importação com validações;
   - geração de CSV de exportação;
   - utilitários de normalização (categoria, números e strings).
2. **Ajustar camada de dados de produtos (`src/products.php`)**:
   - adicionar funções auxiliares para buscar por `sku`;
   - adicionar criação/atualização robusta com defaults;
   - manter compatibilidade com fluxo atual de formulário.
3. **Adicionar rotas admin em `index.php`**:
   - `GET /admin/products/csv-template`
   - `POST /admin/products/import-csv`
   - `GET /admin/products/export-csv`
   - proteger com `isAdmin()`;
   - responder com headers corretos de download (`text/csv`, filename, UTF-8).
4. **Atualizar UI da aba Products (`templates/admin/dashboard.php`)**:
   - botões de baixar modelo e exportar;
   - formulário de upload CSV (multipart/form-data) para importação;
   - área de feedback pós-importação (sucesso/erros resumidos).
5. **Tratar consistência de schema**:
   - garantir coluna `pdf_label` em bases existentes (migração condicional, idempotente);
   - manter fluxo resiliente se banco antigo estiver sem essa coluna até migração.

## Estratégia de validação
1. Validar sintaxe PHP dos arquivos alterados.
2. Executar fluxo manual no navegador:
   - baixar template;
   - importar CSV válido;
   - exportar e conferir conteúdo;
   - importar CSV com erros para validar mensagens e contadores.
3. Verificar que criação/edição manual de produto continua funcional.
4. Conferir logs/erros de runtime para garantir ausência de warnings e regressões.

## Critérios de aceite
- O admin consegue baixar um CSV modelo com cabeçalhos corretos.
- O admin consegue importar produtos por CSV sem quebrar dados existentes.
- O admin consegue exportar produtos em CSV compatível com o modelo.
- Produtos importados aparecem corretamente na listagem e no frontend.
- O fluxo é protegido por autenticação admin e não introduz regressões no painel.
