# Plano de Implementação: Ocultar Seletor de Idioma no Admin quando Multi-idioma estiver Desativado

## Resumo
O objetivo deste plano é garantir que o seletor de idiomas (EN / PT) presente na barra lateral do painel administrativo seja ocultado caso a configuração global da loja "Multi-idioma" (`i18n_multilang_enabled`) esteja desativada. Isso evitará confusão para o administrador, já que, com a opção desligada, o back-end força o idioma único e ignora tentativas de mudança via URL.

## Análise do Estado Atual
- **Lógica Core (`src/i18n.php`)**: Quando a configuração `i18n_multilang_enabled` é `0` (false), o sistema define a variável global `$isMultilangEnabled = false;` e ignora o parâmetro `?lang=` da URL, forçando a sessão a usar o idioma único escolhido.
- **Interface Pública (`templates/layout.php`)**: O seletor de idioma já respeita a variável `$isMultilangEnabled` e é ocultado corretamente quando a opção está desativada.
- **Interface Admin (Problema)**: Os arquivos da barra lateral do admin (`dashboard.php`, `product-form.php`, `customer_detail.php`, `order_detail.php`) exibem o seletor de idioma fixamente, sem condicional. Quando o admin clica, a URL muda, mas o idioma não, causando a frustração relatada.

## Mudanças Propostas

Em todos os arquivos de template do painel administrativo que contêm a barra lateral (sidebar), o bloco HTML responsável por renderizar o seletor de idiomas será envolvido por uma condicional PHP verificando a variável global `$isMultilangEnabled`.

### Arquivos a serem modificados:
1. `templates/admin/dashboard.php`
2. `templates/admin/product-form.php`
3. `templates/admin/customer_detail.php`
4. `templates/admin/order_detail.php`

### Alteração Específica (O que e Como):
Localizar o seguinte bloco (pode variar ligeiramente a linha, mas a estrutura é a mesma):
```php
<div class="border-t border-gray-800 my-2"></div>
<div class="px-4 py-2">
    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-2"><?php echo __('Language'); ?></span>
    <div class="flex gap-2">
        <a href="<?php $q = $_GET; $q['lang'] = 'en'; echo '?' . http_build_query($q); ?>" class="...">EN</a>
        <a href="<?php $q = $_GET; $q['lang'] = 'pt'; echo '?' . http_build_query($q); ?>" class="...">PT</a>
    </div>
</div>
```

E envolvê-lo com o `if`:
```php
<?php if (isset($isMultilangEnabled) && $isMultilangEnabled): ?>
<div class="border-t border-gray-800 my-2"></div>
<div class="px-4 py-2">
    ... (seletor de idioma) ...
</div>
<?php endif; ?>
```
*Nota: Usa-se `isset()` por precaução, embora a variável seja garantida pelo `i18n.php` que roda no `index.php` antes de incluir os templates.*

## Premissas e Decisões
- **Experiência do Usuário (UX)**: Faz total sentido ocultar a opção se ela não tem efeito prático. Isso limpa a interface e evita cliques inúteis.
- **Consistência**: O painel admin passará a ter o mesmo comportamento já existente na vitrine da loja (layout público).
- **Sem alterações no Banco ou Lógica Core**: A regra de negócio no `i18n.php` já está correta e segura. O problema é puramente de exibição (UI).

## Verificação
Após a implementação:
1. Acessar o painel admin (`/admin`).
2. Ir em *Settings* e **desativar** a opção "Enable multi-language (EN/PT)". Salvar.
3. Verificar se o seletor de idiomas sumiu da barra lateral do Dashboard.
4. Navegar para a edição de um produto, detalhes de um cliente e detalhes de um pedido para confirmar que o seletor também sumiu dessas telas.
5. Voltar em *Settings*, **ativar** o multi-idioma e confirmar se o seletor volta a aparecer em todas as telas e se a troca de idioma volta a funcionar.