// orcamentos.js - Funcionalidades JavaScript para página de orçamentos

document.addEventListener('DOMContentLoaded', function() {
    // Inicializações
    console.log('Módulo de Orçamentos carregado com sucesso!');
    
    // Fechar modal ao clicar fora dele
    const modal = document.getElementById('modalEditar');
    if (modal) {
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                fecharModal();
            }
        });
    }
});

/**
 * EDITAR ORÇAMENTO
 * Abre o modal com os dados do orçamento para edição
 */
function editarOrcamento(id, categoria, limite, percentagem_alerta) {
    const modal = document.getElementById('modalEditar');
    
    // Preencher os campos do formulário
    document.getElementById('id_orcamento').value = id;
    document.getElementById('categoria_edit').value = categoria;
    document.getElementById('limite_mensal_edit').value = limite.toFixed(2);
    document.getElementById('percentagem_alerta_edit').value = percentagem_alerta;
    
    // Mostrar modal
    modal.classList.add('show');
}

/**
 * FECHAR MODAL
 */
function fecharModal() {
    const modal = document.getElementById('modalEditar');
    modal.classList.remove('show');
    document.getElementById('formEditar').reset();
}

/**
 * VALIDAÇÃO DE FORMULÁRIO
 */
function validarFormulario(limite, percentagem) {
    if (isNaN(limite) || limite <= 0) {
        alert('Por favor, insira um limite válido e maior que 0.');
        return false;
    }
    
    if (isNaN(percentagem) || percentagem < 0 || percentagem > 100) {
        alert('A percentagem de alerta deve estar entre 0 e 100.');
        return false;
    }
    
    return true;
}

/**
 * FORMATAR MOEDA
 */
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    }).format(valor);
}

/**
 * CALCULADORA DE RECOMENDAÇÕES
 * Exibe recomendações baseadas na regra 50/30/20
 */
function calcularRecomendacoes() {
    const salario = parseFloat(document.getElementById('salario')?.value || 0);
    
    if (salario <= 0) return;
    
    const necessidades = salario * 0.50;
    const desejos = salario * 0.30;
    const poupanca = salario * 0.20;
    
    console.log('Recomendações para €' + salario.toFixed(2));
    console.log('Necessidades (50%): €' + necessidades.toFixed(2));
    console.log('Desejos (30%): €' + desejos.toFixed(2));
    console.log('Poupança (20%): €' + poupanca.toFixed(2));
}

/**
 * DESTACAR ALERTA
 * Anima cards que estão acima do limite
 */
function destacarAlertas() {
    const cartesAlerta = document.querySelectorAll('.orcamento-card.alerta-ativo');
    
    cartesAlerta.forEach(card => {
        card.style.animation = 'pulse 2s infinite';
    });
}

// Adicionar animação pulse ao CSS dinamicamente
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.02);
        }
    }
`;
document.head.appendChild(style);

/**
 * EXPORTAR DADOS
 * Exporta orçamentos para arquivo CSV
 */
function exportarDadosCSV() {
    let csv = 'Categoria,Limite Mensal,Gasto,Percentagem,Status\n';
    
    const cards = document.querySelectorAll('.orcamento-card');
    
    cards.forEach(card => {
        const categoria = card.querySelector('.card-header h4').textContent.trim();
        const valores = card.querySelectorAll('.valor');
        
        if (valores.length >= 2) {
            const limite = valores[0].textContent.trim();
            const gasto = valores[1].textContent.trim();
            const percentagem = card.querySelector('.progress-label strong').textContent.trim();
            const status = card.classList.contains('alerta-ativo') ? 'Alerta' : 'OK';
            
            csv += `"${categoria}",${limite},${gasto},${percentagem},"${status}"\n`;
        }
    });
    
    // Criar e fazer download do arquivo
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `orcamentos_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * IMPRIMIR RELATÓRIO
 */
function imprimirRelatorio() {
    window.print();
}

/**
 * CALCULAR ESTATÍSTICAS
 */
function calcularEstatisticas() {
    const cards = document.querySelectorAll('.orcamento-card');
    let totalOrcamentos = cards.length;
    let alertasAtivos = document.querySelectorAll('.orcamento-card.alerta-ativo').length;
    let percentagemAlertaMedia = 0;
    
    let soma = 0;
    cards.forEach(card => {
        const percentagem = parseFloat(
            card.querySelector('.progress-label strong').textContent.replace('%', '').replace(',', '.')
        );
        soma += percentagem;
    });
    
    if (totalOrcamentos > 0) {
        percentagemAlertaMedia = (soma / totalOrcamentos).toFixed(1);
    }
    
    console.log('===== ESTATÍSTICAS MENSAIS =====');
    console.log('Total de Orçamentos: ' + totalOrcamentos);
    console.log('Alertas Ativos: ' + alertasAtivos);
    console.log('Percentagem Média de Utilização: ' + percentagemAlertaMedia + '%');
    
    return {
        total: totalOrcamentos,
        alertas: alertasAtivos,
        mediaPercentagem: percentagemAlertaMedia
    };
}

/**
 * SUGESTÕES DE CATEGORIA
 */
const categoriasComuns = [
    'Alimentação',
    'Transporte',
    'Habitação',
    'Saúde',
    'Educação',
    'Lazer',
    'Utilities',
    'Seguros',
    'Poupança',
    'Investimentos'
];

function sugerirCategorias(valor) {
    return categoriasComuns.filter(cat => 
        cat.toLowerCase().includes(valor.toLowerCase())
    );
}

/**
 * TOOLTIP COM INFORMAÇÕES
 */
function mostrarTooltip(evento, texto) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = texto;
    tooltip.style.cssText = `
        position: absolute;
        background: #2d3748;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        z-index: 9999;
        max-width: 200px;
        white-space: normal;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = evento.target.getBoundingClientRect();
    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    
    setTimeout(() => tooltip.remove(), 3000);
}

/**
 * ANÁLISE COMPARATIVA
 */
function analisarProgresso() {
    const cards = document.querySelectorAll('.orcamento-card');
    let melhorControle = null;
    let menorPercentagem = 100;
    
    cards.forEach(card => {
        const percentagem = parseFloat(
            card.querySelector('.progress-label strong').textContent.replace('%', '').replace(',', '.')
        );
        
        if (percentagem < menorPercentagem) {
            menorPercentagem = percentagem;
            melhorControle = card.querySelector('.card-header h4').textContent.trim();
        }
    });
    
    if (melhorControle) {
        console.log(`Melhor controle: ${melhorControle} (${menorPercentagem.toFixed(1)}%)`);
    }
}

/**
 * NOTIFICAÇÕES EM TEMPO REAL
 */
function verificarAlertas() {
    const cartesAlerta = document.querySelectorAll('.orcamento-card.alerta-ativo');
    
    if (cartesAlerta.length > 0) {
        console.warn(`⚠️ ${cartesAlerta.length} categoria(s) acima do alerta!`);
        
        cartesAlerta.forEach(card => {
            const categoria = card.querySelector('.card-header h4').textContent.trim();
            const percentagem = card.querySelector('.progress-label strong').textContent.trim();
            console.warn(`  - ${categoria}: ${percentagem}`);
        });
    } else {
        console.log('✅ Todos os orçamentos sob controle!');
    }
}

// Executar verificação de alertas ao carregar
verificarAlertas();

// Recalcular estatísticas periodicamente (a cada 5 minutos)
setInterval(calcularEstatisticas, 5 * 60 * 1000);
