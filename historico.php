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

// Buscar histórico financeiro do utilizador
$sql_historico = "SELECT * FROM historico_financeiro WHERE id_utilizador = ? ORDER BY mes DESC";
$stmt_historico = $conn->prepare($sql_historico);
$stmt_historico->bind_param("i", $user_id);
$stmt_historico->execute();
$historico_result = $stmt_historico->get_result();

// Buscar despesas atuais para cálculo
$sql_despesas = "SELECT SUM(valor_mensal) as total_despesas FROM despesas WHERE id_utilizador = ?";
$stmt_despesas = $conn->prepare($sql_despesas);
$stmt_despesas->bind_param("i", $user_id);
$stmt_despesas->execute();
$despesas_result = $stmt_despesas->get_result();
$despesas_data = $despesas_result->fetch_assoc();
$total_despesas = $despesas_data['total_despesas'] ?? 0;

// Calcular valores atuais
$percentual_orcamento = ($user['salario'] > 0) ? ($total_despesas / $user['salario']) * 100 : 0;
$saldo_disponivel = $user['salario'] - $total_despesas;

// Calcular estatísticas do histórico
$sql_stats = "SELECT 
    COUNT(*) as total_meses,
    AVG(total_gastos) as media_gastos,
    AVG(saldo_restante) as media_saldo,
    MIN(saldo_restante) as menor_saldo,
    MAX(saldo_restante) as maior_saldo
    FROM historico_financeiro 
    WHERE id_utilizador = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $user_id);
$stmt_stats->execute();
$stats_result = $stmt_stats->get_result();
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Finanças - Histórico</title>
    <link rel="icon" type="image/x-icon" href="favicon_io/favicon.ico">
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/historico.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once 'header_footer/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h2><i class="fas fa-history"></i> Histórico Financeiro</h2>
                <p class="page-subtitle">Consulte o histórico dos seus meses anteriores</p>
            </div>

            <!-- Resumo atual -->
            <div class="current-summary">
                <div class="summary-card">
                    <h3><i class="fas fa-calendar-check"></i> Situação Atual</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <span class="label">Salário</span>
                            <span class="value">€ <?php echo number_format($user['salario'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Despesas Atuais</span>
                            <span class="value">€ <?php echo number_format($total_despesas, 2, ',', '.'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Saldo Disponível</span>
                            <span class="value <?php echo $saldo_disponivel < 0 ? 'negative' : 'positive'; ?>">
                                € <?php echo number_format($saldo_disponivel, 2, ',', '.'); ?>
                            </span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Orçamento Usado</span>
                            <span class="value"><?php echo round($percentual_orcamento); ?>%</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo min($percentual_orcamento, 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas do histórico -->
            <?php if ($historico_result->num_rows > 0): ?>
            <div class="stats-section">
                <h3><i class="fas fa-chart-bar"></i> Estatísticas do Histórico</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value"><?php echo $stats['total_meses'] ?? 0; ?></span>
                            <span class="stat-label">Meses Registados</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">€ <?php echo number_format($stats['media_gastos'] ?? 0, 2, ',', '.'); ?></span>
                            <span class="stat-label">Média de Gastos</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">€ <?php echo number_format($stats['media_saldo'] ?? 0, 2, ',', '.'); ?></span>
                            <span class="stat-label">Média de Saldo</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">€ <?php echo number_format($stats['maior_saldo'] ?? 0, 2, ',', '.'); ?></span>
                            <span class="stat-label">Melhor Saldo</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabela de histórico -->
            <div class="history-section">
                <h3><i class="fas fa-table"></i> Histórico por Mês</h3>
                
                <?php if ($historico_result->num_rows > 0): ?>
                    <div class="history-controls">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchHistory" placeholder="Pesquisar por mês...">
                        </div>
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-filter="all">Todos</button>
                            <button class="filter-btn" data-filter="positive">Saldo Positivo</button>
                            <button class="filter-btn" data-filter="negative">Saldo Negativo</button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="history-table" id="historyTable">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th>Total Gastos</th>
                                    <th>Saldo Restante</th>
                                    <th>Percentual Usado</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $historico_result->data_seek(0);
                                while ($registro = $historico_result->fetch_assoc()): 
                                    $percentual_usado = ($user['salario'] > 0) ? ($registro['total_gastos'] / $user['salario']) * 100 : 0;
                                    $status_class = $registro['saldo_restante'] >= 0 ? 'positive' : 'negative';
                                ?>
                                    <tr class="history-row" data-status="<?php echo $status_class; ?>">
                                        <td class="month-cell">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo htmlspecialchars($registro['mes']); ?>
                                        </td>
                                        <td class="expense-cell">
                                            € <?php echo number_format($registro['total_gastos'], 2, ',', '.'); ?>
                                        </td>
                                        <td class="balance-cell <?php echo $status_class; ?>">
                                            € <?php echo number_format($registro['saldo_restante'], 2, ',', '.'); ?>
                                        </td>
                                        <td>
                                            <div class="progress-container">
                                                <span class="percent-value"><?php echo round($percentual_usado); ?>%</span>
                                                <div class="percent-bar">
                                                    <div class="percent-fill" style="width: <?php echo min($percentual_usado, 100); ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <i class="fas fa-<?php echo $status_class == 'positive' ? 'check' : 'exclamation'; ?>"></i>
                                                <?php echo $status_class == 'positive' ? 'Positivo' : 'Negativo'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="table-footer">
                                        <p>Total de registos: <strong><?php echo $historico_result->num_rows; ?></strong> meses</p>
                                        <?php if ($historico_result->num_rows == 0): ?>
                                            <p class="no-data">Adicione meses ao histórico para ver os dados aqui.</p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-history">
                        <div class="empty-icon">
                            <i class="fas fa-history fa-3x"></i>
                        </div>
                        <h4>Nenhum histórico encontrado</h4>
                        <p>Você ainda não tem registos de meses anteriores no seu histórico.</p>
                        <p>O histórico é automaticamente gerado quando você fecha um mês no sistema.</p>
                        <div class="empty-actions">
                            <a href="despesas.php" class="btn btn-primary">
                                <i class="fas fa-money-bill-wave"></i> Gerir Despesas Atuais
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Dicas e informações -->
            <div class="info-section">
                <h3><i class="fas fa-lightbulb"></i> Como Funciona o Histórico?</h3>
                <div class="info-content">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="info-text">
                            <h4>Atualização Automática</h4>
                            <p>O histórico é atualizado automaticamente quando você fecha um mês no sistema.</p>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="info-text">
                            <h4>Acompanhe sua Evolução</h4>
                            <p>Use o histórico para ver sua evolução financeira ao longo do tempo.</p>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="info-text">
                            <h4>Planeje Melhor</h4>
                            <p>Analise meses anteriores para planejar melhor seus próximos orçamentos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/header_footer.js"></script>
    <script src="js/historico.js"></script>
</body>
</html>

<?php $conn->close(); ?>