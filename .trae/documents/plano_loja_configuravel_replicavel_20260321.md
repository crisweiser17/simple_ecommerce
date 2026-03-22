# Plano de implementação — Loja replicável e configurável

## Objetivo
Transformar o sistema para permitir personalização por loja via Admin, cobrindo:
- identidade visual (cor do header, cor de fundo e cor da fonte),
- branding (nome da loja, logo por texto ou imagem com largura/altura),
- política de idioma (modo multilíngue ligado/desligado),
- tradução consistente do Front e do Admin conforme idioma selecionado.

## Premissas técnicas adotadas
- A fonte de verdade de configuração continuará sendo a tabela `settings` (chave/valor) via `getSetting` e `updateSetting`.
- O comportamento deve funcionar para instalações novas e bases já existentes.
- Quando multilíngue estiver desligado, o sistema exibirá apenas um idioma (EN ou PT), sem seletor.
- O idioma ativo deve ser aplicado para toda a interface renderizada (loja e administração).

## Etapas detalhadas

### 1) Definir e padronizar novas chaves de configuração
- Adicionar no bootstrap de defaults (instalação e atualização de DB) as chaves:
  - `store_name`
  - `brand_mode` (`text` ou `image`)
  - `brand_logo_url`
  - `brand_logo_width`
  - `brand_logo_height`
  - `theme_header_bg`
  - `theme_page_bg`
  - `theme_text_color`
  - `i18n_multilang_enabled` (`1`/`0`)
  - `i18n_single_lang` (`en`/`pt`)
- Garantir que valores vazios tenham fallback seguro em runtime para não quebrar layout.
- Criar/ajustar script de atualização para preencher chaves ausentes em bancos já existentes.

### 2) Evoluir backend de idioma para modo único vs multilíngue
- Refatorar `src/i18n.php` para:
  - ler as chaves `i18n_multilang_enabled` e `i18n_single_lang` da tabela `settings`,
  - bloquear troca por `?lang=` quando multilíngue estiver desligado,
  - forçar idioma único configurado para toda a sessão quando multilíngue estiver desligado,
  - manter comportamento atual de sessão + query param quando multilíngue estiver ligado.
- Manter fallback robusto para `en` caso valor inválido seja salvo.

### 3) Estender rota de salvamento de configurações no Admin
- Expandir `/admin/save-settings` em `index.php` para persistir todas as novas chaves de branding, tema e idioma.
- Tratar upload de logo (`multipart/form-data`) com validação mínima de arquivo de imagem.
- Salvar dimensões de logo como valores numéricos saneados (largura/altura).
- Garantir redirecionamento e comportamento atual sem regressão das configurações já existentes.

### 4) Atualizar UI do Admin para novas opções de configuração
- Ampliar a aba **Settings** em `templates/admin/dashboard.php` com blocos:
  - **Branding**: nome da loja, modo texto/imagem, URL/upload de logo, largura/altura.
  - **Tema**: color pickers + input textual para header, fundo da página e cor base da fonte.
  - **Idioma**: toggle de multilíngue e seletor do idioma único (EN/PT) quando toggle estiver desligado.
- Ajustar formulário para `enctype="multipart/form-data"` no bloco de settings geral.
- Pré-preencher valores com `getSetting(...)` e defaults coerentes.

### 5) Aplicar configuração visual no Front
- Alterar `templates/layout.php` para:
  - trocar textos hardcoded da marca por `store_name`,
  - renderizar logo em texto ou imagem conforme `brand_mode`,
  - aplicar largura/altura configuráveis no logo de imagem,
  - aplicar `theme_header_bg`, `theme_page_bg` e `theme_text_color` via estilos inline seguros.
- Controlar exibição do seletor EN/PT:
  - mostrar somente se `i18n_multilang_enabled = 1`,
  - ocultar quando desligado.

### 6) Aplicar idioma também no Admin
- Internacionalizar templates administrativos principais:
  - `templates/admin/dashboard.php`
  - `templates/admin/product-form.php`
  - `templates/admin/order_detail.php`
- Substituir textos de interface por `__()` e adicionar chaves faltantes em:
  - `lang/en.php`
  - `lang/pt.php`
- Garantir que o idioma ativo (definido no i18n) reflita no Admin sem seletor separado.

### 7) Eliminar hardcodes de nome da loja em saídas auxiliares
- Substituir nome fixo de loja por `getSetting('store_name', ...)` em:
  - e-mails (`src/mailer.php`, `src/auth.php`),
  - PDFs (`src/generate_pdf.php`),
  - título/metadata onde aplicável.
- Preservar fallback para nome padrão caso setting esteja vazio.

### 8) Verificação completa (obrigatória)
- Rodar validação de sintaxe PHP em arquivos alterados.
- Executar checagem de qualidade/lint disponível no projeto (se houver comando padronizado).
- Testar fluxo funcional manual no navegador:
  - salvar branding + tema e verificar reflexo imediato no Front,
  - alternar entre logo texto e imagem e validar dimensões,
  - testar multilíngue ligado (seletor aparece e alterna),
  - testar multilíngue desligado com EN e PT (seletor oculto e idioma único no Front/Admin),
  - validar que e-mail/PDF usam o nome de loja configurado.

## Critérios de aceite
- Todas as personalizações pedidas estão no Admin e persistem no banco.
- A loja pode ser replicada apenas ajustando configurações, sem editar código.
- Idioma único vs multilíngue funciona conforme toggle e afeta Front + Admin.
- Não há regressão nas funcionalidades existentes de settings/banner/SMTP.
