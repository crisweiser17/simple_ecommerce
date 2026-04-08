# Plano de Implementação: Correção da Visibilidade da Imagem do Banner

## 1. Resumo
O objetivo é garantir que a imagem de fundo do banner apareça totalmente visível (sem a camada azul cobrindo-a ou a deixando opaca), alterando a estrutura de camadas do banner na página inicial (`templates/archive.php`). A única exceção será quando o "overlay" estiver ativado, onde uma camada de gradiente com opacidade configurável será sobreposta à imagem.

## 2. Análise do Estado Atual
Atualmente, no arquivo `templates/archive.php`, o contêiner do banner possui um gradiente de fundo (`background: linear-gradient(...)`). A imagem do banner é renderizada **sobre** esse fundo com a classe CSS `mix-blend-overlay` e uma opacidade baseada na configuração `banner_overlay_opacity`. Isso faz com que a imagem se misture com a cor de fundo (a camada azul relatada) e pareça estar "atrás" de uma cor. 
Além disso, há uma segunda div de overlay gerada quando o `$overlayEnabled` é verdadeiro, que aplica um gradiente para o transparente sem respeitar a opacidade do painel.

## 3. Mudanças Propostas

### Arquivo: `templates/archive.php`
- **O que será feito:** 
  1. Alterar a ordem das camadas (z-index natural).
  2. Remover as classes `mix-blend-overlay` e `opacity` da div da imagem do banner, tornando-a a camada base, 100% visível e opaca.
  3. Mover a lógica de opacidade (`banner_overlay_opacity`) para a div de overlay condicional.
  4. Quando o overlay estiver ativado (`$overlayEnabled`), renderizar uma div sobre a imagem contendo o gradiente configurado (`Overlay Color 1` até `Overlay Color 2`) aplicando a opacidade do painel a esta camada.
- **Por que:** Isso alinhará o comportamento com a expectativa do usuário e com as descrições no painel Admin. A imagem sempre será protagonista e totalmente nítida, a não ser que o usuário opte explicitamente por aplicar uma camada translúcida por cima para melhorar a leitura dos textos.

## 4. Suposições e Decisões
- O gradiente base (usado como fallback) será mantido atrás da imagem caso a imagem possua partes transparentes ou demore a carregar.
- O overlay, quando ativado, usará um gradiente linear da cor 1 para a cor 2, e sua opacidade será controlada pelo valor definido no painel admin (ex: 30%), o que garantirá que a imagem seja vista por baixo do overlay.

## 5. Passos de Verificação
- Salvar as modificações no código.
- Visualizar o site e verificar se a imagem do banner aparece clara e sem o tom azul forçado.
- Acessar o Painel Admin > "Settings" > "Layout do Produto" (ou Banner) e ativar o overlay com 50% de opacidade, salvando as alterações.
- Recarregar a página inicial para confirmar se o overlay foi aplicado corretamente **sobre** a imagem.