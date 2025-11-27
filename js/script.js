
        // Estado global
        let financialData = null;
        let expenses = [];
        let goals = [];
        let charts = {};

        // Navegação entre tabs
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');

            // Atualizar projeções quando abrir a tab
            if (tabName === 'projection' && financialData) {
                updateProjections();
            }
        }

        // Definir data actual no input de despesas
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('expense-date').value = today;
        });

        // Função principal de cálculo
        function calculateFinances() {
            const salary = parseFloat(document.getElementById('salary').value) || 0;
            
            if (salary === 0) {
                alert('Por favor, insira o seu ordenado mensal.');
                return;
            }

            // Despesas fixas
            const rent = parseFloat(document.getElementById('rent').value) || 0;
            const utilities = parseFloat(document.getElementById('utilities').value) || 0;
            const internet = parseFloat(document.getElementById('internet').value) || 0;
            const transport = parseFloat(document.getElementById('transport').value) || 0;
            const insurance = parseFloat(document.getElementById('insurance').value) || 0;
            const loans = parseFloat(document.getElementById('loans').value) || 0;

            // Despesas variáveis
            const food = parseFloat(document.getElementById('food').value) || 0;
            const health = parseFloat(document.getElementById('health').value) || 0;
            const education = parseFloat(document.getElementById('education').value) || 0;
            const clothing = parseFloat(document.getElementById('clothing').value) || 0;

            // Gastos supérfluos
            const entertainment = parseFloat(document.getElementById('entertainment').value) || 0;
            const restaurants = parseFloat(document.getElementById('restaurants').value) || 0;
            const hobbies = parseFloat(document.getElementById('hobbies').value) || 0;
            const other = parseFloat(document.getElementById('other').value) || 0;

            const essentials = rent + utilities + internet + transport + insurance + loans + food + health + education + clothing;
            const nonEssentials = entertainment + restaurants + hobbies + other;
            const totalExpenses = essentials + nonEssentials;
            const remaining = salary - totalExpenses;

            financialData = {
                salary,
                essentials,
                nonEssentials,
                totalExpenses,
                remaining,
                essentialsPercent: (essentials / salary) * 100,
                nonEssentialsPercent: (nonEssentials / salary) * 100,
                savingsPercent: (remaining / salary) * 100
            };

            displayResults();
            showTab('analysis');
        }

        function displayResults() {
            document.getElementById('no-data').classList.add('hidden');
            document.getElementById('results').classList.remove('hidden');

            // Estatísticas
            document.getElementById('stat-salary').textContent = `€${financialData.salary.toFixed(2)}`;
            document.getElementById('stat-expenses').textContent = `€${financialData.totalExpenses.toFixed(2)}`;
            document.getElementById('stat-remaining').textContent = `€${financialData.remaining.toFixed(2)}`;
            document.getElementById('stat-savings-rate').textContent = `${financialData.savingsPercent.toFixed(1)}%`;

            // Tabela comparativa
            const comparisonBody = document.getElementById('comparison-body');
            comparisonBody.innerHTML = `
                <tr>
                    <td>Despesas Essenciais</td>
                    <td>${financialData.essentialsPercent.toFixed(1)}%</td>
                    <td>70%</td>
                    <td class="${financialData.essentialsPercent <= 70 ? 'positive' : 'negative'}">
                        ${(financialData.essentialsPercent - 70).toFixed(1)}%
                    </td>
                </tr>
                <tr>
                    <td>Poupança/Investimento</td>
                    <td>${financialData.savingsPercent.toFixed(1)}%</td>
                    <td>20%</td>
                    <td class="${financialData.savingsPercent >= 20 ? 'positive' : 'negative'}">
                        ${(financialData.savingsPercent - 20).toFixed(1)}%
                    </td>
                </tr>
                <tr>
                    <td>Gastos Supérfluos</td>
                    <td>${financialData.nonEssentialsPercent.toFixed(1)}%</td>
                    <td>10%</td>
                    <td class="${financialData.nonEssentialsPercent <= 10 ? 'positive' : 'negative'}">
                        ${(financialData.nonEssentialsPercent - 10).toFixed(1)}%
                    </td>
                </tr>
            `;

            // Gráficos
            createCharts();

            // Conselhos
            generateAdvice();
        }

        function createCharts() {
            // Gráfico da distribuição actual
            const currentCtx = document.getElementById('currentChart');
            if (charts.current) charts.current.destroy();
            
            charts.current = new Chart(currentCtx, {
                type: 'pie',
                data: {
                    labels: ['Despesas Essenciais', 'Gastos Supérfluos', 'Poupança/Disponível'],
                    datasets: [{
                        data: [
                            financialData.essentials,
                            financialData.nonEssentials,
                            financialData.remaining
                        ],
                        backgroundColor: ['#33808d', '#e68161', '#32b8c6']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Gráfico de comparação
            const comparisonCtx = document.getElementById('comparisonChart');
            if (charts.comparison) charts.comparison.destroy();

            charts.comparison = new Chart(comparisonCtx, {
                type: 'bar',
                data: {
                    labels: ['Essenciais', 'Poupança', 'Supérfluos'],
                    datasets: [
                        {
                            label: 'Actual (%)',
                            data: [
                                financialData.essentialsPercent,
                                financialData.savingsPercent,
                                financialData.nonEssentialsPercent
                            ],
                            backgroundColor: '#33808d'
                        },
                        {
                            label: 'Recomendado (%)',
                            data: [70, 20, 10],
                            backgroundColor: '#32b8c6'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        function generateAdvice() {
            const adviceList = document.getElementById('advice-list');
            const advice = [];

            if (financialData.remaining < 0) {
                advice.push({
                    type: 'warning',
                    text: `⚠️ ATENÇÃO: Está a gastar mais do que ganha! Défice de €${Math.abs(financialData.remaining).toFixed(2)} por mês.`
                });
            } else if (financialData.savingsPercent < 10) {
                advice.push({
                    type: 'warning',
                    text: `💡 A sua taxa de poupança é muito baixa (${financialData.savingsPercent.toFixed(1)}%). Tente poupar pelo menos 20% do seu ordenado.`
                });
            } else if (financialData.savingsPercent >= 20) {
                advice.push({
                    type: 'success',
                    text: `✅ Excelente! Está a poupar ${financialData.savingsPercent.toFixed(1)}% do seu ordenado. Continue assim!`
                });
            }

            if (financialData.nonEssentialsPercent > 15) {
                advice.push({
                    type: 'warning',
                    text: `📉 Está a gastar ${financialData.nonEssentialsPercent.toFixed(1)}% em gastos supérfluos. Tente reduzir para 10% para aumentar as poupanças.`
                });
                const savings = (financialData.nonEssentialsPercent - 10) / 100 * financialData.salary;
                advice.push({
                    type: 'normal',
                    text: `💰 Se reduzir os gastos supérfluos para 10%, pode poupar mais €${savings.toFixed(2)} por mês (€${(savings * 12).toFixed(2)} por ano).`
                });
            }

            if (financialData.essentialsPercent > 70) {
                advice.push({
                    type: 'warning',
                    text: `🏠 As suas despesas essenciais representam ${financialData.essentialsPercent.toFixed(1)}% do ordenado. Considere formas de reduzir custos fixos.`
                });
            }

            if (financialData.remaining > 0) {
                const monthlyInvestment = financialData.remaining * 0.5;
                const annualInvestment = monthlyInvestment * 12;
                advice.push({
                    type: 'success',
                    text: `📊 Sugestão: Invista €${monthlyInvestment.toFixed(2)} por mês (50% da sobra). Em um ano terá investido €${annualInvestment.toFixed(2)}.`
                });

                const monthlySavings = financialData.remaining * 0.3;
                const annualSavings = monthlySavings * 12;
                advice.push({
                    type: 'success',
                    text: `🏦 Reserve €${monthlySavings.toFixed(2)} para poupanças de emergência. Em um ano terá €${annualSavings.toFixed(2)} guardados.`
                });
            }

            adviceList.innerHTML = advice.map(item => `
                <li class="advice-item ${item.type}">${item.text}</li>
            `).join('');
        }

        // Gestão de Despesas
        function addExpense() {
            const description = document.getElementById('expense-description').value;
            const amount = parseFloat(document.getElementById('expense-amount').value);
            const category = document.getElementById('expense-category').value;
            const date = document.getElementById('expense-date').value;

            if (!description || !amount || !date) {
                alert('Por favor, preencha todos os campos.');
                return;
            }

            const expense = {
                id: Date.now(),
                description,
                amount,
                category,
                date
            };

            expenses.push(expense);
            updateExpenseList();
            updateExpenseChart();

            // Limpar formulário
            document.getElementById('expense-description').value = '';
            document.getElementById('expense-amount').value = '';
        }

        function deleteExpense(id) {
            expenses = expenses.filter(e => e.id !== id);
            updateExpenseList();
            updateExpenseChart();
        }

        function updateExpenseList() {
            const expenseList = document.getElementById('expense-list');
            const total = expenses.reduce((sum, e) => sum + e.amount, 0);

            if (expenses.length === 0) {
                expenseList.innerHTML = '<p style="text-align: center; color: var(--color-text-secondary);">Nenhuma despesa registada ainda.</p>';
            } else {
                expenseList.innerHTML = expenses
                    .sort((a, b) => new Date(b.date) - new Date(a.date))
                    .map(expense => `
                        <div class="expense-item">
                            <div class="expense-info">
                                <div class="expense-category">${expense.description}</div>
                                <div class="expense-date">${expense.category} • ${new Date(expense.date).toLocaleDateString('pt-PT')}</div>
                            </div>
                            <div class="expense-amount">€${expense.amount.toFixed(2)}</div>
                            <button class="btn btn-danger btn-small" onclick="deleteExpense(${expense.id})">✕</button>
                        </div>
                    `).join('');
            }

            document.getElementById('total-expenses').textContent = `€${total.toFixed(2)}`;
        }

        function updateExpenseChart() {
            const ctx = document.getElementById('expenseChart');
            
            const categoryTotals = {};
            expenses.forEach(expense => {
                categoryTotals[expense.category] = (categoryTotals[expense.category] || 0) + expense.amount;
            });

            if (charts.expenses) charts.expenses.destroy();

            if (Object.keys(categoryTotals).length === 0) {
                return;
            }

            charts.expenses = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(categoryTotals),
                    datasets: [{
                        data: Object.values(categoryTotals),
                        backgroundColor: [
                            '#33808d', '#e68161', '#32b8c6', '#c0152f', 
                            '#626c71', '#1d7480', '#a8522f'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Gestão de Objetivos
        function addGoal() {
            const name = document.getElementById('goal-name').value;
            const amount = parseFloat(document.getElementById('goal-amount').value);
            const saved = parseFloat(document.getElementById('goal-saved').value) || 0;
            const monthly = parseFloat(document.getElementById('goal-monthly').value);
            const deadline = document.getElementById('goal-deadline').value;

            if (!name || !amount || !monthly || !deadline) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }

            const remaining = amount - saved;
            const monthsNeeded = Math.ceil(remaining / monthly);
            const deadlineDate = new Date(deadline);
            const today = new Date();
            const monthsAvailable = Math.round((deadlineDate - today) / (1000 * 60 * 60 * 24 * 30));

            const goal = {
                id: Date.now(),
                name,
                amount,
                saved,
                monthly,
                deadline,
                remaining,
                monthsNeeded,
                monthsAvailable,
                achievable: monthsNeeded <= monthsAvailable
            };

            goals.push(goal);
            updateGoalsList();

            // Limpar formulário
            document.getElementById('goal-name').value = '';
            document.getElementById('goal-amount').value = '';
            document.getElementById('goal-saved').value = '';
            document.getElementById('goal-monthly').value = '';
            document.getElementById('goal-deadline').value = '';
        }

        function deleteGoal(id) {
            goals = goals.filter(g => g.id !== id);
            updateGoalsList();
        }

        function updateGoalsList() {
            const goalsList = document.getElementById('goals-list');

            if (goals.length === 0) {
                goalsList.innerHTML = '<p style="text-align: center; color: var(--color-text-secondary);">Nenhum objetivo definido ainda.</p>';
                return;
            }

            goalsList.innerHTML = goals.map(goal => {
                const progress = (goal.saved / goal.amount) * 100;
                return `
                    <div class="goal-card">
                        <div class="goal-header">
                            <div class="goal-name">${goal.name}</div>
                            <button class="btn btn-danger btn-small" onclick="deleteGoal(${goal.id})">✕</button>
                        </div>
                        <div class="goal-progress">
                            €${goal.saved.toFixed(2)} de €${goal.amount.toFixed(2)} (${progress.toFixed(1)}%)
                        </div>
                        <div class="percentage-bar">
                            <div class="percentage-fill" style="width: ${progress}%"></div>
                        </div>
                        <div style="margin-top: 10px; font-size: 0.9em; color: var(--color-text-secondary);">
                            <div>💰 Falta: €${goal.remaining.toFixed(2)}</div>
                            <div>📅 Prazo: ${new Date(goal.deadline).toLocaleDateString('pt-PT')}</div>
                            <div>📊 Com €${goal.monthly.toFixed(2)}/mês → ${goal.monthsNeeded} meses</div>
                            ${goal.achievable 
                                ? '<div class="positive">✅ Objetivo alcançável no prazo!</div>'
                                : '<div class="negative">⚠️ Precisa poupar mais por mês para alcançar no prazo</div>'
                            }
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Projeções
        function updateProjections() {
            if (!financialData) {
                return;
            }

            const annualIncome = financialData.salary * 12;
            const annualExpenses = financialData.totalExpenses * 12;
            const annualSavings = financialData.remaining * 12 * 0.7; // 70% para poupança
            const annualInvestment = financialData.remaining * 12 * 0.3; // 30% para investimento

            document.getElementById('proj-income').textContent = `€${annualIncome.toFixed(2)}`;
            document.getElementById('proj-expenses').textContent = `€${annualExpenses.toFixed(2)}`;
            document.getElementById('proj-savings').textContent = `€${annualSavings.toFixed(2)}`;
            document.getElementById('proj-investment').textContent = `€${annualInvestment.toFixed(2)}`;

            // Tabela mensal
            const tbody = document.getElementById('projection-table-body');
            const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            let accumulated = 0;

            tbody.innerHTML = months.map((month, i) => {
                accumulated += financialData.remaining;
                return `
                    <tr>
                        <td>${month}</td>
                        <td class="positive">€${financialData.salary.toFixed(2)}</td>
                        <td class="negative">€${financialData.totalExpenses.toFixed(2)}</td>
                        <td class="positive">€${financialData.remaining.toFixed(2)}</td>
                        <td class="positive"><strong>€${accumulated.toFixed(2)}</strong></td>
                    </tr>
                `;
            }).join('');

            // Gráfico de projeção
            createProjectionChart(months, accumulated);
        }

        function createProjectionChart(months, finalAmount) {
            const ctx = document.getElementById('projectionChart');
            if (charts.projection) charts.projection.destroy();

            const data = [];
            let acc = 0;
            for (let i = 0; i < 12; i++) {
                acc += financialData.remaining;
                data.push(acc);
            }

            charts.projection = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Saldo Acumulado (€)',
                        data: data,
                        borderColor: '#33808d',
                        backgroundColor: 'rgba(51, 128, 141, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Simulador
        function runSimulation() {
            if (!financialData) {
                alert('Por favor, preencha os dados iniciais primeiro.');
                showTab('input');
                return;
            }

            const salaryChange = parseFloat(document.getElementById('sim-salary-change').value) || 0;
            const expenseChange = parseFloat(document.getElementById('sim-expense-change').value) || 0;
            const newExpense = parseFloat(document.getElementById('sim-new-expense').value) || 0;
            const months = parseInt(document.getElementById('sim-months').value) || 12;

            const newSalary = financialData.salary * (1 + salaryChange / 100);
            const newExpenses = financialData.totalExpenses * (1 + expenseChange / 100) + newExpense;
            const newRemaining = newSalary - newExpenses;

            const currentTotal = financialData.remaining * months;
            const newTotal = newRemaining * months;
            const difference = newTotal - currentTotal;

            document.getElementById('sim-current').textContent = `€${currentTotal.toFixed(2)}`;
            document.getElementById('sim-new').textContent = `€${newTotal.toFixed(2)}`;
            document.getElementById('sim-diff').textContent = `€${difference.toFixed(2)}`;
            
            const diffElement = document.getElementById('sim-diff');
            diffElement.className = `stat-value ${difference >= 0 ? 'positive' : 'negative'}`;

            document.getElementById('simulation-results').classList.remove('hidden');

            // Gráfico de simulação
            createSimulationChart(months, currentTotal, newTotal);

            // Análise
            generateSimulationAdvice(salaryChange, expenseChange, newExpense, difference, months);
        }

        function createSimulationChart(months, currentTotal, newTotal) {
            const ctx = document.getElementById('simulationChart');
            if (charts.simulation) charts.simulation.destroy();

            const labels = [];
            const currentData = [];
            const newData = [];

            let currentAcc = 0;
            let newAcc = 0;

            for (let i = 1; i <= months; i++) {
                labels.push(`Mês ${i}`);
                currentAcc += financialData.remaining;
                currentData.push(currentAcc);
                
                const newRemaining = (financialData.salary * (1 + parseFloat(document.getElementById('sim-salary-change').value || 0) / 100)) -
                                    (financialData.totalExpenses * (1 + parseFloat(document.getElementById('sim-expense-change').value || 0) / 100) +
                                     parseFloat(document.getElementById('sim-new-expense').value || 0));
                newAcc += newRemaining;
                newData.push(newAcc);
            }

            charts.simulation = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Cenário Actual',
                            data: currentData,
                            borderColor: '#626c71',
                            backgroundColor: 'rgba(98, 108, 113, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Novo Cenário',
                            data: newData,
                            borderColor: '#33808d',
                            backgroundColor: 'rgba(51, 128, 141, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function generateSimulationAdvice(salaryChange, expenseChange, newExpense, difference, months) {
            const adviceDiv = document.getElementById('simulation-advice');
            const advice = [];

            if (salaryChange !== 0) {
                advice.push(`📊 Com um ${salaryChange > 0 ? 'aumento' : 'redução'} de ${Math.abs(salaryChange)}% no ordenado...`);
            }

            if (expenseChange !== 0) {
                advice.push(`💰 Com uma ${expenseChange > 0 ? 'aumento' : 'redução'} de ${Math.abs(expenseChange)}% nas despesas...`);
            }

            if (newExpense > 0) {
                advice.push(`➕ Adicionando uma nova despesa de €${newExpense.toFixed(2)}/mês...`);
            }

            if (difference > 0) {
                advice.push(`✅ Este cenário resulta em mais €${difference.toFixed(2)} poupados em ${months} meses!`);
                advice.push(`💡 Isso representa €${(difference / months).toFixed(2)} extra por mês.`);
            } else if (difference < 0) {
                advice.push(`⚠️ Este cenário resulta em menos €${Math.abs(difference).toFixed(2)} poupados em ${months} meses.`);
                advice.push(`📉 Você perderá €${(Math.abs(difference) / months).toFixed(2)} por mês com estas mudanças.`);
            } else {
                advice.push(`➡️ Este cenário não altera significativamente as suas finanças.`);
            }

            adviceDiv.innerHTML = advice.map(text => `<p style="margin-bottom: 10px;">${text}</p>`).join('');
        }

function saveModalData() {
  const map = [
    ['modal-salary', 'salary'],
    ['modal-rent', 'rent'],
    ['modal-utilities', 'utilities'],
    ['modal-internet', 'internet'],
    ['modal-transport', 'transport'],
    ['modal-insurance', 'insurance'],
    ['modal-loans', 'loans'],
    ['modal-food', 'food'],
    ['modal-health', 'health'],
    ['modal-education', 'education'],
    ['modal-clothing', 'clothing'],
    ['modal-entertainment', 'entertainment'],
    ['modal-restaurants', 'restaurants'],
    ['modal-hobbies', 'hobbies'],
    ['modal-other', 'other'],
  ];

  // validação simples: exige salário e renda
  const salary = document.getElementById('modal-salary').value;
  const rent = document.getElementById('modal-rent').value;
  if (!salary || !rent) {
    alert('Preencha pelo menos o ordenado e a renda.');
    return;
  }

  // copia os valores do modal para os campos principais
  map.forEach(([from, to]) => {
    const fromEl = document.getElementById(from);
    const toEl = document.getElementById(to);
    if (fromEl && toEl) {
      toEl.value = fromEl.value;
    }
  });

  // fecha o modal
  const modal = document.getElementById('input-modal');
  if (modal) {
    modal.classList.add('hidden');
  }

  // chama o cálculo
  if (typeof calculateFinances === 'function') {
    calculateFinances();
  }
}
