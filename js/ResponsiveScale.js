
    // Função para ajustar escala da página automaticamente
    function ajustarEscalaPagina() {
        const viewport = document.querySelector('meta[name="viewport"]');
        const larguraTela = window.innerWidth;
        
        // Remover zoom anterior
        viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, viewport-fit=cover');
        
        // Aplicar ajustes baseado no tamanho da tela
        if (larguraTela < 320) {
            // Telas muito pequenas
            document.documentElement.style.fontSize = '12px';
        } else if (larguraTela < 480) {
            // Mobile pequeno
            document.documentElement.style.fontSize = '13px';
        } else if (larguraTela < 768) {
            // Mobile grande
            document.documentElement.style.fontSize = '14px';
        } else if (larguraTela < 1024) {
            // Tablet
            document.documentElement.style.fontSize = '15px';
        } else if (larguraTela < 1440) {
            // Laptop
            document.documentElement.style.fontSize = '16px';
        } else {
            // Desktop grande
            document.documentElement.style.fontSize = '17px';
        }
    }
    
    // Executar ao carregar a página
    window.addEventListener('load', ajustarEscalaPagina);
    
    // Executar quando a janela é redimensionada
    window.addEventListener('resize', ajustarEscalaPagina);
    
    // Executar imediatamente
    ajustarEscalaPagina();