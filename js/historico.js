// historico.js - Funcionalidades simples para a página de histórico

document.addEventListener('DOMContentLoaded', function() {
    // 1. Filtro de busca
    const searchInput = document.getElementById('searchHistory');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.history-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const month = row.querySelector('.month-cell').textContent.toLowerCase();
                if (month.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Atualizar contador no footer
            updateRowCount(visibleCount);
        });
    }
    
    // 2. Filtro por status
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover active de todos os botões
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Adicionar active ao botão clicado
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            filterRows(filter);
        });
    });
    
    // 3. Ordenação simples da tabela
    initTableSorting();
    
    // 4. Animações simples
    initAnimations();
});

// Função para filtrar linhas por status
function filterRows(filter) {
    const rows = document.querySelectorAll('.history-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const status = row.dataset.status;
        
        if (filter === 'all' || status === filter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updateRowCount(visibleCount);
}

// Atualizar contador de linhas visíveis
function updateRowCount(count) {
    const totalRows = document.querySelectorAll('.history-row').length;
    const footer = document.querySelector('.table-footer p:first-child');
    
    if (footer && count !== totalRows) {
        footer.innerHTML = `Mostrando <strong>${count}</strong> de <strong>${totalRows}</strong> registos`;
    } else if (footer) {
        footer.innerHTML = `Total de registos: <strong>${count}</strong> meses`;
    }
}

// Ordenação simples da tabela
function initTableSorting() {
    const headers = document.querySelectorAll('.history-table th');
    headers.forEach((header, index) => {
        header.style.cursor = 'pointer';
        
        header.addEventListener('click', function() {
            const table = document.getElementById('historyTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('.history-row'));
            
            // Determinar direção da ordenação
            const isAscending = !this.classList.contains('asc');
            
            // Remover classes de ordenação de todos os cabeçalhos
            headers.forEach(h => {
                h.classList.remove('asc', 'desc');
            });
            
            // Adicionar classe ao cabeçalho atual
            this.classList.add(isAscending ? 'asc' : 'desc');
            
            // Ordenar linhas
            rows.sort((a, b) => {
                let aValue, bValue;
                
                switch(index) {
                    case 0: // Mês
                        aValue = a.querySelector('.month-cell').textContent;
                        bValue = b.querySelector('.month-cell').textContent;
                        break;
                    case 1: // Gastos
                        aValue = parseFloat(a.querySelector('.expense-cell').textContent
                            .replace(/[^\d.,]/g, '').replace(',', '.'));
                        bValue = parseFloat(b.querySelector('.expense-cell').textContent
                            .replace(/[^\d.,]/g, '').replace(',', '.'));
                        break;
                    case 2: // Saldo
                        aValue = parseFloat(a.querySelector('.balance-cell').textContent
                            .replace(/[^\d.,]/g, '').replace(',', '.'));
                        bValue = parseFloat(b.querySelector('.balance-cell').textContent
                            .replace(/[^\d.,]/g, '').replace(',', '.'));
                        break;
                    case 3: // Percentual
                        aValue = parseFloat(a.querySelector('.percent-value').textContent);
                        bValue = parseFloat(b.querySelector('.percent-value').textContent);
                        break;
                    default:
                        return 0;
                }
                
                if (isAscending) {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });
            
            // Reordenar na tabela
            rows.forEach(row => tbody.appendChild(row));
            
            // Feedback visual
            showSortFeedback(this.textContent.trim(), isAscending);
        });
    });
}

// Feedback visual para ordenação
function showSortFeedback(column, isAscending) {
    // Criar mensagem temporária
    const message = document.createElement('div');
    message.className = 'sort-feedback';
    message.innerHTML = `
        <i class="fas fa-sort-${isAscending ? 'amount-up' : 'amount-down'}"></i>
        Ordenado por ${column} (${isAscending ? 'A-Z' : 'Z-A'})
    `;
    message.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #4299e1;
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        z-index: 100;
        animation: fadeInOut 2.5s ease;
    `;
    
    // Estilo para animação
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(20px); }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(message);
    
    // Remover após animação
    setTimeout(() => {
        if (message.parentNode) {
            document.body.removeChild(message);
        }
    }, 2500);
}

// Animações simples
function initAnimations() {
    // Animar entrada das linhas
    const rows = document.querySelectorAll('.history-row');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, 100 + (index * 50));
    });
    
    // Efeito hover nos cartões de estatísticas
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}