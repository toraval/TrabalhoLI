// recomendacoes.js - Funcionalidades simples para a página de recomendações

document.addEventListener('DOMContentLoaded', function() {
    // 1. Filtrar recomendações por tipo
    initFilterButtons();
    
    // 2. Animações simples
    initAnimations();
    
    // 3. Marcar recomendações como lidas
    initMarkAsRead();
    
    // 4. Atualizar contador de recomendações não lidas
    updateUnreadCount();
});

// Filtrar recomendações por tipo
function initFilterButtons() {
    // Criar botões de filtro se não existirem
    const section = document.querySelector('.recommendations-section');
    if (!section || !document.querySelector('.recommendation-card')) return;
    
    const grid = section.querySelector('.recommendations-grid');
    const header = section.querySelector('.section-header');
    
    const filterContainer = document.createElement('div');
    filterContainer.className = 'filter-container';
    filterContainer.innerHTML = `
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">Todas</button>
            <button class="filter-btn" data-filter="alerta">Alertas</button>
            <button class="filter-btn" data-filter="aviso">Avisos</button>
            <button class="filter-btn" data-filter="sucesso">Sucessos</button>
            <button class="filter-btn" data-filter="dica">Dicas</button>
        </div>
    `;
    
    header.appendChild(filterContainer);
    
    // Adicionar event listeners aos botões
    const filterButtons = filterContainer.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover active de todos os botões
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Adicionar active ao botão clicado
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            filterRecommendations(filter);
        });
    });
}

function filterRecommendations(filter) {
    const cards = document.querySelectorAll('.recommendation-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        if (filter === 'all' || card.classList.contains(filter)) {
            card.style.display = 'block';
            visibleCount++;
            // Animação de entrada
            card.style.animation = 'fadeIn 0.5s ease';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Atualizar contador no subtítulo
    updateFilterCount(visibleCount);
}

function updateFilterCount(count) {
    const subtitle = document.querySelector('.section-subtitle');
    const totalCards = document.querySelectorAll('.recommendation-card').length;
    
    if (subtitle) {
        if (count === totalCards) {
            subtitle.textContent = `${count} sugestões personalizadas`;
        } else {
            subtitle.textContent = `${count} de ${totalCards} sugestões (filtradas)`;
        }
    }
}

// Animações simples
function initAnimations() {
    // Animar entrada dos cartões
    const cards = document.querySelectorAll('.recommendation-card, .tip-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 50));
    });
    
    // Efeito hover nos cartões de dicas
    const tipCards = document.querySelectorAll('.tip-card');
    tipCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Marcar recomendações como lidas
function initMarkAsRead() {
    const cards = document.querySelectorAll('.recommendation-card');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            if (!this.classList.contains('read')) {
                this.classList.add('read');
                this.style.opacity = '0.9';
                
                // Adicionar indicador de lido
                const footer = this.querySelector('.card-footer');
                if (footer && !footer.querySelector('.read-indicator')) {
                    const indicator = document.createElement('span');
                    indicator.className = 'read-indicator';
                    indicator.innerHTML = '<i class="fas fa-check"></i> Lido';
                    indicator.style.cssText = `
                        font-size: 0.75rem;
                        color: #38a169;
                        display: flex;
                        align-items: center;
                        gap: 5px;
                    `;
                    footer.appendChild(indicator);
                }
                
                updateUnreadCount();
            }
        });
    });
}

// Atualizar contador de recomendações não lidas
function updateUnreadCount() {
    const unreadCards = document.querySelectorAll('.recommendation-card:not(.read)');
    const count = unreadCards.length;
    
    // Atualizar título se houver recomendações não lidas
    const title = document.querySelector('.section-header h3');
    if (title && count > 0) {
        const originalText = title.textContent.replace(/\(\d+\)/, '');
        title.textContent = `${originalText} (${count} novas)`;
        
        // Adicionar badge de notificação
        if (!title.querySelector('.notification-badge')) {
            const badge = document.createElement('span');
            badge.className = 'notification-badge';
            badge.textContent = count;
            badge.style.cssText = `
                background: #e53e3e;
                color: white;
                font-size: 0.7rem;
                padding: 2px 6px;
                border-radius: 10px;
                margin-left: 8px;
                font-weight: 600;
            `;
            title.appendChild(badge);
        }
    }
}

// Adicionar estilos CSS dinamicamente
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .filter-container {
        margin-top: 10px;
    }
    
    .filter-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 6px 12px;
        background: #e2e8f0;
        border: none;
        border-radius: 16px;
        color: #4a5568;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .filter-btn.active {
        background: #805ad5;
        color: white;
    }
    
    .filter-btn:hover:not(.active) {
        background: #cbd5e0;
    }
    
    .recommendation-card.read {
        opacity: 0.9;
    }
    
    .recommendation-card.read .recommendation-icon {
        opacity: 0.8;
    }
`;
document.head.appendChild(style);