# Plano: migrar todos os uploads para FilePond

## Objetivo
Padronizar todos os uploads do sistema com FilePond, mantendo os endpoints PHP existentes e melhorando UX, validação e consistência entre formulários.

## Estratégia técnica
- Usar FilePond via CDN no admin (sem alterar stack para npm/build).
- Operar em modo de envio por formulário (`storeAsFile: true`) para preservar fluxo atual com `$_FILES` e evitar refatoração grande de backend para upload assíncrono agora.
- Aplicar configuração global + presets por tipo de upload (imagem e CSV).
- Manter fallback nativo de `<input type="file">` caso JS falhe.

## Escopo de migração (todos os uploads atuais)
1. Upload múltiplo de imagens de produto em `/admin/product-form`.
2. Importação de CSV de produtos em `/admin` (aba Products).
3. Upload de logo (branding) em `/admin` (aba Settings).
4. Upload de banner principal e banner direito em `/admin` (aba Settings).

## Passos de implementação

1. **Criar base FilePond reutilizável no admin**
- Incluir CSS/JS do FilePond no template admin que já renderiza as telas com upload.
- Incluir plugins necessários:
  - validação de tipo (`file type validation`);
  - validação de tamanho (`file size validation`);
  - preview de imagem (`image preview`) para uploads de imagem.
- Criar inicializador JS único que detecta inputs marcados com atributos `data-filepond-*` e aplica preset correto.

2. **Definir presets de configuração**
- Preset `image-single`:
  - `allowMultiple: false`;
  - `acceptedFileTypes: image/*`;
  - limite de tamanho por arquivo (ex.: 5MB).
- Preset `image-multi`:
  - `allowMultiple: true`;
  - `maxFiles` configurável;
  - mesmos filtros de imagem.
- Preset `csv-single`:
  - `allowMultiple: false`;
  - aceitar `.csv` e `text/csv`;
  - sem preview de imagem.

3. **Aplicar FilePond no formulário de produto**
- Converter `product_images[]` para preset `image-multi`.
- Preservar lógica atual de “imagem principal”, “imagens existentes” e “remover imagem”.
- Garantir que o name continue `product_images[]` para backend atual seguir funcional.

4. **Aplicar FilePond na importação CSV**
- Converter input `products_csv` para preset `csv-single`.
- Manter `multipart/form-data` e rota `/admin/products/import-csv` sem mudanças de contrato.

5. **Aplicar FilePond nos uploads de branding/banner**
- Converter:
  - `brand_logo_file` para `image-single`;
  - `banner_image_file` para `image-single`;
  - `banner_right_image_file` para `image-single`.
- Manter lógica atual de fallback por URL textual e prioridade de arquivo já implementada no backend.

6. **Higienizar validações no backend (compatibilidade + segurança)**
- Padronizar validações server-side para tipo e tamanho em todos os handlers de upload (logo, banners e imagens de produto).
- Garantir que FilePond seja somente camada de UX: backend continua como fonte da verdade.
- Confirmar permissões e criação de diretórios de upload já existentes.

7. **Padronizar feedback visual no admin**
- Exibir mensagens claras de erro de tipo/tamanho via FilePond.
- Ajustar textos para PT/EN usando mecanismo existente de tradução quando aplicável.
- Garantir consistência visual entre abas (Products/Settings).

8. **Validação obrigatória**
- Rodar lint/sintaxe PHP nos arquivos alterados.
- Testar manualmente todos os fluxos:
  - upload múltiplo produto + principal;
  - import CSV;
  - upload logo;
  - upload banner e banner direito.
- Subir preview e validar no navegador as telas de admin e reflexo no frontend.

## Critérios de aceite
- Todos os uploads do sistema usam FilePond no frontend.
- Nenhum endpoint de upload atual é quebrado.
- Validações de tipo/tamanho funcionam no cliente e no servidor.
- Uploads continuam persistindo nos diretórios corretos.
- Fluxo de imagens do produto (principal + galeria) permanece íntegro.

## Ordem de execução recomendada
1) Base FilePond compartilhada  
2) Produto (mais complexo)  
3) CSV  
4) Branding/Banners  
5) Hardening backend  
6) Testes finais e preview
