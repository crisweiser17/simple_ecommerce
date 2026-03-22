# Plano de Ação: Correção da Persistência de Idioma

## 1. Análise do Problema
Atualmente, o idioma selecionado (Inglês ou Português) é salvo apenas na sessão do PHP (`$_SESSION['lang']`). Como a sessão expira após 24 horas ou é destruída quando o usuário faz logout, o idioma volta ao padrão (`en`), causando a impressão de que a linguagem "fica mudando sozinha".

## 2. Solução Proposta
Para garantir que a escolha de idioma seja lembrada de forma definitiva, vamos implementar uma abordagem em camadas utilizando **Cookies de longa duração** aliados à sessão existente.

A ordem de prioridade para definir o idioma será:
1. **Parâmetro na URL (`?lang=`)**: Ação explícita do usuário. Atualiza a sessão e salva no cookie.
2. **Sessão (`$_SESSION['lang']`)**: Mantém a fluidez durante a navegação atual.
3. **Cookie Persistente (`$_COOKIE['lang']`)**: Recupera a escolha do usuário mesmo se a sessão expirar ou o usuário deslogar (validade de 1 ano).
4. **Idioma do Navegador (`HTTP_ACCEPT_LANGUAGE`)**: Caso o usuário nunca tenha escolhido, tentamos identificar o idioma padrão do navegador.
5. **Padrão (`en`)**: Fallback final.

## 3. Passos da Implementação
1. **Editar o arquivo `src/i18n.php`**:
   - Substituir o bloco atual que gerencia o `$isMultilangEnabled` para incorporar a lógica de Cookies descrita acima.
   - Adicionar a função `setcookie('lang', $selectedLang, time() + (86400 * 365), '/')` para salvar o idioma no navegador por 1 ano.
   - Adicionar a detecção do idioma do navegador como melhoria de UX (experiência do usuário).
2. **Testar as Mudanças**:
   - Alternar o idioma via link e verificar a criação do Cookie.
   - Simular a expiração da sessão (ou fazer logout) e garantir que o idioma salvo no Cookie seja restaurado com sucesso na próxima visita.