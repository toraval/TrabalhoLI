<?php
session_start();
require_once 'connect_db.php';

// Verificar se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: main/indexv1.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Buscar informações do utilizador
$sql_user = "SELECT * FROM utilizadores WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();

// Buscar despesas do utilizador
$sql_despesas = "SELECT categoria, valor_mensal, fixa_variavel FROM despesas WHERE id_utilizador = ?";
$stmt_despesas = $conn->prepare($sql_despesas);
$stmt_despesas->bind_param("i", $user_id);
$stmt_despesas->execute();
$despesas_result = $stmt_despesas->get_result();

// Buscar orçamentos do utilizador
$sql_orcamentos = "SELECT categoria, limite_mensal FROM orcamentos WHERE id_utilizador = ?";
$stmt_orcamentos = $conn->prepare($sql_orcamentos);
$stmt_orcamentos->bind_param("i", $user_id);
$stmt_orcamentos->execute();
$orcamentos_result = $stmt_orcamentos->get_result();

// Buscar histórico financeiro
$sql_historico = "SELECT mes, total_gastos, saldo_restante FROM historico_financeiro WHERE id_utilizador = ? ORDER BY mes DESC LIMIT 6";
$stmt_historico = $conn->prepare($sql_historico);
$stmt_historico->bind_param("i", $user_id);
$stmt_historico->execute();
$historico_result = $stmt_historico->get_result();

// Calcular estatísticas
$total_despesas = 0;
$despesas_fixas = 0;
$despesas_variaveis = 0;
$categorias_despesas = [];
$orcamentos = [];
$historico_meses = [];
$historico_gastos = [];
$historico_saldos = [];

while ($despesa = $despesas_result->fetch_assoc()) {
    $total_despesas += $despesa['valor_mensal'];
    
    if ($despesa['fixa_variavel'] == 'Fixa') {
        $despesas_fixas += $despesa['valor_mensal'];
    } else {
        $despesas_variaveis += $despesa['valor_mensal'];
    }
    
    if (!isset($categorias_despesas[$despesa['categoria']])) {
        $categorias_despesas[$despesa['categoria']] = 0;
    }
    $categorias_despesas[$despesa['categoria']] += $despesa['valor_mensal'];
}

while ($orcamento = $orcamentos_result->fetch_assoc()) {
    $orcamentos[$orcamento['categoria']] = $orcamento['limite_mensal'];
}

while ($hist = $historico_result->fetch_assoc()) {
    $historico_meses[] = $hist['mes'];
    $historico_gastos[] = floatval($hist['total_gastos']);
    $historico_saldos[] = floatval($hist['saldo_restante']);
}

// Inverter a ordem para mostrar do mais antigo para o mais recente
$historico_meses = array_reverse($historico_meses);
$historico_gastos = array_reverse($historico_gastos);
$historico_saldos = array_reverse($historico_saldos);

// Calcular valores
$percentual_orcamento = ($user['salario'] > 0) ? ($total_despesas / $user['salario']) * 100 : 0;
$saldo_disponivel = $user['salario'] - $total_despesas;
$total_categorias = count($categorias_despesas);

// Ordenar categorias por valor (maior para menor)
arsort($categorias_despesas);

// Preparar dados para gráficos
$categorias_nomes = array_keys($categorias_despesas);
$categorias_valores = array_values($categorias_despesas);

// Limitar a 6 categorias para o gráfico
if (count($categorias_nomes) > 6) {
    $categorias_nomes = array_slice($categorias_nomes, 0, 6);
    $categorias_valores = array_slice($categorias_valores, 0, 6);
}

// Calcular percentuais de despesas fixas vs variáveis
$total_fixa_variavel = $despesas_fixas + $despesas_variaveis;
$percent_fixa = $total_fixa_variavel > 0 ? ($despesas_fixas / $total_fixa_variavel) * 100 : 0;
$percent_variavel = $total_fixa_variavel > 0 ? ($despesas_variaveis / $total_fixa_variavel) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Finanças - Status & Relatórios</title>
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/status.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php require_once 'header_footer/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h2><i class="fas fa-chart-bar"></i> Status & Relatórios Financeiros</h2>
                <p class="page-subtitle">Visualize gráficos e estatísticas sobre suas finanças</p>
                
            </div>

            <!-- Cards de resumo -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="card-icon income">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="card-content">
                        <h3>Rendimento Mensal</h3>
                        <p class="card-value">€ <?php echo number_format($user['salario'], 2, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon expenses">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total de Despesas</h3>
                        <p class="card-value">€ <?php echo number_format($total_despesas, 2, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon balance">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="card-content">
                        <h3>Saldo Disponível</h3>
                        <p class="card-value <?php echo $saldo_disponivel < 0 ? 'negative' : 'positive'; ?>">
                            € <?php echo number_format($saldo_disponivel, 2, ',', '.'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon percentage">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="card-content">
                        <h3>Orçamento Usado</h3>
                        <p class="card-value"><?php echo round($percentual_orcamento); ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Gráficos principais -->
            <div class="charts-section">
                <div class="chart-row">
                    <!-- Gráfico de distribuição por categoria -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie"></i> Distribuição por Categoria</h3>
                            <p class="chart-subtitle">Total: € <?php echo number_format($total_despesas, 2, ',', '.'); ?></p>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="categoriaChart" height="250"></canvas>
                        </div>
                        <?php if (empty($categorias_nomes)): ?>
                            <div class="no-data">
                                <i class="fas fa-chart-pie"></i>
                                <p>Adicione despesas para ver o gráfico por categoria</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Gráfico de despesas fixas vs variáveis -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3><i class="fas fa-balance-scale"></i> Fixas vs Variáveis</h3>
                            <p class="chart-subtitle">Comparação de tipos de despesas</p>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="fixaVariavelChart" height="250"></canvas>
                        </div>
                        <div class="chart-legends">
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #4299e1;"></span>
                                <span class="legend-text">Fixa: € <?php echo number_format($despesas_fixas, 2, ',', '.'); ?></span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #48bb78;"></span>
                                <span class="legend-text">Variável: € <?php echo number_format($despesas_variaveis, 2, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="chart-row">
                    <!-- Gráfico de histórico -->
                    <div class="chart-container large">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-line"></i> Histórico Financeiro</h3>
                            <p class="chart-subtitle">Evolução dos últimos meses</p>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="historicoChart" height="200"></canvas>
                        </div>
                        <?php if (empty($historico_meses)): ?>
                            <div class="no-data">
                                <i class="fas fa-history"></i>
                                <p>Nenhum histórico disponível</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tabela de dados detalhados -->
            <div class="data-section">
                <div class="section-header">
                    <h3><i class="fas fa-table"></i> Dados Detalhados</h3>
                    <p class="section-subtitle">Informações específicas sobre suas finanças</p>
                </div>
                
                <div class="data-grid">
                    <!-- Categorias de despesas -->
                    <div class="data-card">
                        <div class="data-header">
                            <h4><i class="fas fa-tags"></i> Categorias de Despesas</h4>
                            <span class="data-count"><?php echo $total_categorias; ?></span>
                        </div>
                        <div class="data-content">
                            <?php if (!empty($categorias_despesas)): ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Categoria</th>
                                            <th>Valor</th>
                                            <th>Percentual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $counter = 0;
                                        foreach ($categorias_despesas as $categoria => $valor): 
                                            $percent = ($total_despesas > 0) ? ($valor / $total_despesas) * 100 : 0;
                                            if ($counter++ >= 5) break;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($categoria); ?></td>
                                                <td class="value">€ <?php echo number_format($valor, 2, ',', '.'); ?></td>
                                                <td>
                                                    <div class="progress-row">
                                                        <span class="percent"><?php echo round($percent); ?>%</span>
                                                        <div class="progress-bar-small">
                                                            <div class="progress-fill" style="width: <?php echo min($percent, 100); ?>%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="no-data-small">
                                    <p>Nenhuma despesa registada</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Orçamentos definidos -->
                    <div class="data-card">
                        <div class="data-header">
                            <h4><i class="fas fa-chart-pie"></i> Orçamentos Definidos</h4>
                            <span class="data-count"><?php echo count($orcamentos); ?></span>
                        </div>
                        <div class="data-content">
                            <?php if (!empty($orcamentos)): ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Categoria</th>
                                            <th>Limite</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orcamentos as $categoria => $limite): 
                                            $gasto = isset($categorias_despesas[$categoria]) ? $categorias_despesas[$categoria] : 0;
                                            $percent = ($limite > 0) ? ($gasto / $limite) * 100 : 0;
                                            $status = $percent > 100 ? 'excedido' : ($percent > 80 ? 'alerta' : 'normal');
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($categoria); ?></td>
                                                <td class="value">€ <?php echo number_format($limite, 2, ',', '.'); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $status; ?>">
                                                        <?php echo $status == 'excedido' ? 'Excedido' : ($status == 'alerta' ? 'Alerta' : 'Normal'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="no-data-small">
                                    <p>Nenhum orçamento definido</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do utilizador -->
            <div class="user-info-section">
                <div class="section-header">
                    <h3><i class="fas fa-user-circle"></i> Informações do Perfil</h3>
                    <p class="section-subtitle">Seus dados pessoais e financeiros</p>
                </div>
                
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-header">
                            <i class="fas fa-user"></i>
                            <h4>Dados Pessoais</h4>
                        </div>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-label">Nome:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['nome']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Idade:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['idade'] ?? 'Não definida'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ocupação:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['ocupacao'] ?? 'Não definida'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-header">
                            <i class="fas fa-chart-line"></i>
                            <h4>Estatísticas Financeiras</h4>
                        </div>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-label">Total de Categorias:</span>
                                <span class="info-value"><?php echo $total_categorias; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Despesas Fixas:</span>
                                <span class="info-value">€ <?php echo number_format($despesas_fixas, 2, ',', '.'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Despesas Variáveis:</span>
                                <span class="info-value">€ <?php echo number_format($despesas_variaveis, 2, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-header">
                            <i class="fas fa-calendar-alt"></i>
                            <h4>Histórico Recente</h4>
                        </div>
                        <div class="info-content">
                            <?php if (!empty($historico_meses)): ?>
                                <?php for ($i = 0; $i < min(3, count($historico_meses)); $i++): ?>
                                    <div class="info-item">
                                        <span class="info-label"><?php echo htmlspecialchars($historico_meses[$i]); ?>:</span>
                                        <span class="info-value">€ <?php echo number_format($historico_gastos[$i], 2, ',', '.'); ?></span>
                                    </div>
                                <?php endfor; ?>
                            <?php else: ?>
                                <div class="no-data-small">
                                    <p>Sem histórico disponível</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/header_footer.js"></script>
    <script>
        // Dados para os gráficos
        const categoriaData = {
            labels: <?php echo json_encode($categorias_nomes); ?>,
            datasets: [{
                data: <?php echo json_encode($categorias_valores); ?>,
                backgroundColor: [
                    '#4299e1', '#48bb78', '#ed8936', 
                    '#9f7aea', '#f56565', '#38b2ac'
                ],
                borderWidth: 1
            }]
        };

        const fixaVariavelData = {
            labels: ['Despesas Fixas', 'Despesas Variáveis'],
            datasets: [{
                data: [<?php echo $despesas_fixas; ?>, <?php echo $despesas_variaveis; ?>],
                backgroundColor: ['#4299e1', '#48bb78'],
                borderWidth: 1
            }]
        };

        const historicoData = {
            labels: <?php echo json_encode($historico_meses); ?>,
            datasets: [
                {
                    label: 'Total Gastos',
                    data: <?php echo json_encode($historico_gastos); ?>,
                    borderColor: '#e53e3e',
                    backgroundColor: 'rgba(229, 62, 62, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Saldo Restante',
                    data: <?php echo json_encode($historico_saldos); ?>,
                    borderColor: '#38a169',
                    backgroundColor: 'rgba(56, 161, 105, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        };

        // Inicializar gráficos quando o DOM estiver carregado
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de categoria (doughnut)
            if (categoriaData.labels.length > 0) {
                const categoriaCtx = document.getElementById('categoriaChart').getContext('2d');
                new Chart(categoriaCtx, {
                    type: 'doughnut',
                    data: categoriaData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico fixa vs variável (pie)
            const fixaVariavelCtx = document.getElementById('fixaVariavelChart').getContext('2d');
            new Chart(fixaVariavelCtx, {
                type: 'pie',
                data: fixaVariavelData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Gráfico de histórico (line)
            if (historicoData.labels.length > 0) {
                const historicoCtx = document.getElementById('historicoChart').getContext('2d');
                new Chart(historicoCtx, {
                    type: 'line',
                    data: historicoData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '€' + value;
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        }
                    }
                });
            }
        });    
    </script>
</body>
</html>

<?php $conn->close(); ?>