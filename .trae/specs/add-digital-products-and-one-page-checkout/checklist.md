# Checklist de Verificação

- [ ] Banco de dados possui as novas colunas em `products` e as novas tabelas `order_digital_deliveries` e `embed_sessions`.
- [ ] Admin permite criar/editar produtos marcados como digitais, definir limite de downloads e dias de expiração.
- [ ] Admin permite upload de arquivos para produtos digitais (ou inserção de URL externa), validando o tipo e tamanho (até 25MB).
- [ ] Confirmação de pagamento gera tokens únicos e registros na tabela `order_digital_deliveries`.
- [ ] Cliente recebe e-mail automático com link de download após o pagamento de produto digital.
- [ ] Acessar `/download/{token}` faz as validações corretas (token válido, expirado, limite de downloads excedido).
- [ ] Arquivo é baixado corretamente pela rota `/download/{token}` sem expor o caminho real, ou redirecionado para a URL externa.
- [ ] Área "Meus Pedidos" do cliente exibe o botão de download para produtos digitais pagos, mostrando downloads restantes e validade.
- [ ] Acessar `/produto/{slug}/single` exibe uma landing page focada, sem header/footer.
- [ ] Compra via Single Page funciona com One-Step Checkout (nome + e-mail -> PIX -> confirmação na tela -> download imediato).
- [ ] Snippet `embed.js` injeta um botão na página de teste.
- [ ] Clique no botão do widget redireciona ou abre `/checkout/express/{slug}` corretamente.
- [ ] Compra via Checkout Express processa o pagamento e libera o acesso adequadamente.