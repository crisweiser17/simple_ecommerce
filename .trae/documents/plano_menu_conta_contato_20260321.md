# Plano: Menu lateral em /account + página Contact Us com formulário

## Objetivo
Implementar em `/account` um side menu com seções separadas de **Profile** e **Orders**, além de criar uma página pública **Contact Us** com formulário funcional, acessível para usuários logados e deslogados, com pré-preenchimento automático quando houver sessão ativa.

## Escopo funcional
1. Em `/account`, dividir visualmente o conteúdo em duas áreas:
   - **Profile**: formulário de dados do cliente.
   - **Orders**: tabela/histórico de pedidos.
2. Criar página `/contact` com formulário contendo:
   - nome
   - e-mail
   - telefone
   - assunto (select)
   - mensagem
3. Permitir acesso à página de contato para visitantes deslogados.
4. Se o usuário estiver logado, pré-preencher nome, e-mail e telefone no formulário de contato.
5. Tornar a página de contato listável e navegável no site (menu principal), sem depender de login.

## Assuntos sugeridos para o select
Além de **“dúvidas sobre compra”**, adotar:
- dúvidas sobre entrega
- trocas e devoluções
- pagamento e faturamento
- suporte técnico do site
- parceria comercial
- outro assunto

## Plano de implementação
1. **Mapear e ajustar a arquitetura de renderização de conta**
   - Atualizar `templates/account.php` para incluir navegação lateral interna com links âncora (`#profile` e `#orders`) ou tabs server-side, mantendo o mesmo endpoint `/account`.
   - Reorganizar o markup para separar claramente os blocos de perfil e pedidos sem alterar a regra de autenticação já existente em `index.php`.

2. **Adicionar rota pública de contato no front controller**
   - Incluir um `case '/contact'` em `index.php` para renderizar `templates/contact.php` com método GET.
   - Incluir um endpoint POST dedicado (ex.: `/contact/send`) para processar o envio do formulário.
   - Garantir que essa rota não exija `isLoggedIn()`.

3. **Implementar template de contato com pré-preenchimento condicional**
   - Criar `templates/contact.php` com formulário completo e validações HTML básicas (`required`, tipo de e-mail).
   - Pré-carregar valores de nome/e-mail/telefone quando `isLoggedIn()` for verdadeiro e houver dados do usuário disponível.
   - Manter os campos editáveis mesmo quando pré-preenchidos.

4. **Implementar processamento e envio da mensagem**
   - Criar função de serviço em `src/` (ex.: `src/contact.php`) para validar/sanitizar payload e disparar envio por e-mail usando a infraestrutura existente em `src/mailer.php`.
   - Tratar sucesso/erro com feedback amigável no formulário (flash message via sessão).
   - Validar backend para cenários logado e deslogado.

5. **Integrar “Contact Us” na navegação pública e i18n**
   - Garantir link visível para `/contact` no menu principal em `templates/layout.php`.
   - Adicionar/ajustar chaves de tradução em `lang/pt.php` e `lang/en.php` para labels, placeholders, assuntos e mensagens de retorno.
   - Verificar convivência com páginas dinâmicas por slug para evitar conflito de rota com `contact`.

6. **Garantir consistência visual e UX**
   - Aplicar estilos seguindo o padrão existente no projeto para side menu e formulário.
   - Garantir layout responsivo para mobile no `/account` e `/contact`.

7. **Validação técnica obrigatória**
   - Executar verificação de sintaxe/lint PHP nos arquivos alterados.
   - Testar fluxo funcional manual:
     - `/account` com side menu e separação Profile/Orders.
     - `/contact` deslogado (campos vazios editáveis).
     - `/contact` logado (pré-preenchimento correto).
     - envio com sucesso e com erro.
   - Subir preview local e validar visualmente as duas páginas no navegador.

## Critérios de aceite
- `/account` exibe side menu com entradas **Profile** e **Orders**, cada uma levando ao bloco correto.
- `/contact` é acessível sem login e aparece na navegação pública.
- Formulário contém exatamente os campos solicitados e select de assunto com opções definidas.
- Usuário logado vê nome/e-mail/telefone já preenchidos; visitante não logado preenche manualmente.
- Envio de formulário funciona e apresenta feedback de sucesso/erro sem quebrar o layout.
- Traduções PT/EN permanecem consistentes e sem chaves faltantes.
