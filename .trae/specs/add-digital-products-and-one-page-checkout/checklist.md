# Checklist de Verificação

- [x] Banco de dados possui as novas colunas em `products` e as novas tabelas `order_digital_deliveries` e `embed_sessions`.
- [x] Admin permite criar/editar produtos marcados como digitais, definir limite de downloads e dias de expiração.
- [x] Admin permite upload de arquivos para produtos digitais (ou inserção de URL externa), validando o tipo e tamanho (até 25MB).
- [x] Confirmação de pagamento gera tokens únicos e registros na tabela `order_digital_deliveries`.
- [x] Cliente recebe e-mail automático com link de download após o pagamento de produto digital.
- [x] Acessar `/download/{token}` faz as validações corretas (token válido, expirado, limite de downloads excedido).
- [x] Arquivo é baixado corretamente pela rota `/download/{token}` sem expor o caminho real, ou redirecionado para a URL externa.
- [x] Área "Meus Pedidos" do cliente exibe o botão de download para produtos digitais pagos, mostrando downloads restantes e validade.
- [x] Acessar `/produto/{slug}/single` exibe uma landing page focada, sem header/footer.
- [x] Compra via Single Page funciona com One-Step Checkout (nome + e-mail -> PIX -> confirmação na tela -> download imediato).
- [x] Snippet `embed.js` injeta um botão na página de teste.
- [x] Clique no botão do widget redireciona ou abre `/checkout/express/{slug}` corretamente.
- [x] Compra via Checkout Express processa o pagamento e libera o acesso adequadamente.