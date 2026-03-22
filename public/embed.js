(function() {
    // Determine base URL from the script src
    const scripts = document.getElementsByTagName('script');
    let baseUrl = '';
    for (let i = 0; i < scripts.length; i++) {
        if (scripts[i].src.includes('/embed.js')) {
            try {
                const url = new URL(scripts[i].src);
                baseUrl = url.origin;
            } catch (e) {
                // Ignore invalid URL
            }
            break;
        }
    }

    function initEmbeds() {
        const containers = document.querySelectorAll('[data-checkout]');
        
        containers.forEach(container => {
            if (container.hasAttribute('data-initialized')) return;
            
            const slug = container.getAttribute('data-checkout');
            const color = container.getAttribute('data-color') || '#2563eb';
            const text = container.getAttribute('data-text') || 'Comprar agora';
            
            if (!slug) return;
            
            const btn = document.createElement('button');
            btn.textContent = text;
            btn.style.backgroundColor = color;
            btn.style.color = '#ffffff';
            btn.style.border = 'none';
            btn.style.padding = '12px 24px';
            btn.style.borderRadius = '6px';
            btn.style.cursor = 'pointer';
            btn.style.fontWeight = '600';
            btn.style.fontSize = '16px';
            btn.style.fontFamily = 'system-ui, -apple-system, sans-serif';
            btn.style.transition = 'all 0.2s ease';
            btn.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
            
            btn.onmouseover = () => {
                btn.style.opacity = '0.9';
                btn.style.transform = 'translateY(-1px)';
            };
            btn.onmouseout = () => {
                btn.style.opacity = '1';
                btn.style.transform = 'translateY(0)';
            };
            btn.onmousedown = () => {
                btn.style.transform = 'translateY(1px)';
            };
            
            btn.onclick = () => {
                window.location.href = `${baseUrl}/checkout/express/${encodeURIComponent(slug)}`;
            };
            
            container.appendChild(btn);
            container.setAttribute('data-initialized', 'true');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEmbeds);
    } else {
        initEmbeds();
    }
})();
