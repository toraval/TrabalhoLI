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
$sql_orcamentos = "SELECT categoria, limite_mensal, percentagem_alerta FROM orcamentos WHERE id_utilizador = ?";
$stmt_orcamentos = $conn->prepare($sql_orcamentos);
$stmt_orcamentos->bind_param("i", $user_id);
$stmt_orcamentos->execute();
$orcamentos_result = $stmt_orcamentos->get_result();

// Buscar recomendações existentes
$sql_recomendacoes = "SELECT * FROM recomendacoes WHERE id_utilizador = ? ORDER BY data_recomendacao DESC";
$stmt_recomendacoes = $conn->prepare($sql_recomendacoes);
$stmt_recomendacoes->bind_param("i", $user_id);
$stmt_recomendacoes->execute();
$recomendacoes_result = $stmt_recomendacoes->get_result();

// Calcular estatísticas para recomendações
$total_despesas = 0;
$categorias_despesas = [];
$orcamentos = [];

while ($despesa = $despesas_result->fetch_assoc()) {
    $total_despesas += $despesa['valor_mensal'];
    if (!isset($categorias_despesas[$despesa['categoria']])) {
        $categorias_despesas[$despesa['categoria']] = 0;
    }
    $categorias_despesas[$despesa['categoria']] += $despesa['valor_mensal'];
}

while ($orcamento = $orcamentos_result->fetch_assoc()) {
    $orcamentos[$orcamento['categoria']] = $orcamento;
}

// Calcular percentual do orçamento
$percentual_orcamento = ($user['salario'] > 0) ? ($total_despesas / $user['salario']) * 100 : 0;

// Saldo disponível (usado para recomendação de poupança)
$saldo_disponivel = $user['salario'] - $total_despesas;

// Gerar recomendações personalizadas
$recomendacoes_geradas = gerarRecomendacoes(
    $user,
    $total_despesas,
    $categorias_despesas,
    $orcamentos,
    $percentual_orcamento,
    $saldo_disponivel
);

// Função para gerar recomendações
function gerarRecomendacoes($user, $total_despesas, $categorias_despesas, $orcamentos, $percentual_orcamento, $saldo_disponivel) {
    $recomendacoes = [];
    
    // Recomendação baseada no salário
    if ($user['salario'] > 0) {
        if ($percentual_orcamento > 90) {
            $recomendacoes[] = [
                'titulo' => 'Orçamento Muito Alto',
                'mensagem' => 'Você está usando mais de 90% do seu salário em despesas. Considere reduzir gastos não essenciais.',
                'tipo' => 'alerta',
                'icon' => 'exclamation-triangle'
            ];
        } elseif ($percentual_orcamento > 70) {
            $recomendacoes[] = [
                'titulo' => 'Orçamento Elevado',
                'mensagem' => 'Está usando mais de 70% do seu salário. Tente economizar em algumas categorias.',
                'tipo' => 'aviso',
                'icon' => 'exclamation-circle'
            ];
        } elseif ($percentual_orcamento < 50) {
            $recomendacoes[] = [
                'titulo' => 'Bom Controlo Financeiro',
                'mensagem' => 'Excelente! Você está usando menos de 50% do seu salário em despesas.',
                'tipo' => 'sucesso',
                'icon' => 'check-circle'
            ];
        }
    }
    
    // Recomendações baseadas em orçamentos vs despesas
    foreach ($orcamentos as $categoria => $orcamento) {
        if (isset($categorias_despesas[$categoria])) {
            $gasto_atual = $categorias_despesas[$categoria];
            $limite = $orcamento['limite_mensal'];
            $percentual_usado = ($limite > 0) ? ($gasto_atual / $limite) * 100 : 0;
            
            if ($percentual_usado > $orcamento['percentagem_alerta']) {
                $recomendacoes[] = [
                    'titulo' => 'Alerta de Orçamento',
                    'mensagem' => "Na categoria '$categoria' você já gastou " . round($percentual_usado) . "% do orçamento (limite: €" . number_format($limite, 2, ',', '.') . ")",
                    'tipo' => 'alerta',
                    'icon' => 'bell'
                ];
            }
        }
    }
    
    // Recomendação baseada na idade e ocupação
    if (!empty($user['idade']) && !empty($user['ocupacao']) && $user['idade'] < 25 && $user['ocupacao'] == 'Estudante') {
        $recomendacoes[] = [
            'titulo' => 'Dica para Estudantes',
            'mensagem' => 'Como estudante, considere aproveitar descontos estudantis e controlar gastos com entretenimento.',
            'tipo' => 'dica',
            'icon' => 'graduation-cap'
        ];
    }
    
    // Recomendação geral se tiver menos de 5 despesas
    if (count($categorias_despesas) < 5) {
        $recomendacoes[] = [
            'titulo' => 'Diversificação de Despesas',
            'mensagem' => 'Considere categorizar melhor suas despesas para ter um controlo mais detalhado.',
            'tipo' => 'dica',
            'icon' => 'list-alt'
        ];
    }
    
    // Recomendação para poupança
    if ($saldo_disponivel > 100) {
        $recomendacoes[] = [
            'titulo' => 'Oportunidade de Poupança',
            'mensagem' => 'Com o saldo disponível, considere guardar parte para uma reserva de emergência.',
            'tipo' => 'oportunidade',
            'icon' => 'piggy-bank'
        ];
    }
    
    return $recomendacoes;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Finanças - Recomendações</title>
    <link rel="icon" type="image/x-icon" href="favicon_io/favicon.ico">
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/recomendacoes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once 'header_footer/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h2><i class="fas fa-lightbulb"></i> Recomendações Personalizadas</h2>
                <p class="page-subtitle">Dicas e sugestões baseadas na sua situação financeira atual</p>
            </div>

            <!-- Resumo rápido -->
            <div class="quick-summary">
                <div class="summary-card">
                    <h3><i class="fas fa-chart-pie"></i> Sua Situação Atual</h3>
                    <div class="summary-content">
                        <div class="summary-item">
                            <span class="label">Total de Despesas</span>
                            <span class="value">€ <?php echo number_format($total_despesas, 2, ',', '.'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Orçamento Usado</span>
                            <span class="value"><?php echo round($percentual_orcamento); ?>%</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo min($percentual_orcamento, 100); ?>%"></div>
                            </div>
                        </div>
                        <div class="summary-item">
                            <span class="label">Categorias</span>
                            <span class="value"><?php echo count($categorias_despesas); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Recomendações</span>
                            <span class="value"><?php echo count($recomendacoes_geradas); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recomendações geradas -->
            <div class="recommendations-section">
                <div class="section-header">
                    <h3><i class="fas fa-magic"></i> Recomendações Baseadas nos Seus Dados</h3>
                    <p class="section-subtitle"><?php echo count($recomendacoes_geradas); ?> sugestões personalizadas</p>
                </div>
                
                <?php if (count($recomendacoes_geradas) > 0): ?>
                    <div class="recommendations-grid">
                        <?php foreach ($recomendacoes_geradas as $index => $recomendacao): ?>
                            <div class="recommendation-card <?php echo $recomendacao['tipo']; ?>" data-rec-id="<?php echo $index; ?>">
                                <div class="card-header">
                                    <div class="recommendation-icon">
                                        <i class="fas fa-<?php echo $recomendacao['icon']; ?>"></i>
                                    </div>
                                    <h4><?php echo htmlspecialchars($recomendacao['titulo']); ?></h4>
                                    <span class="recommendation-number">#<?php echo $index + 1; ?></span>
                                </div>
                                <div class="card-body">
                                    <p><?php echo htmlspecialchars($recomendacao['mensagem']); ?></p>
                                </div>
                                <div class="card-footer">
                                    <span class="recommendation-type"><?php echo ucfirst($recomendacao['tipo']); ?></span>
                                    <span class="recommendation-date">Gerada hoje</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-recommendations">
                        <div class="empty-icon">
                            <i class="fas fa-chart-line fa-3x"></i>
                        </div>
                        <h4>Adicione mais dados para recomendações</h4>
                        <p>Para receber recomendações personalizadas, adicione suas despesas e orçamentos.</p>
                        <div class="empty-actions">
                            <a href="despesas.php" class="btn btn-primary">
                                <i class="fas fa-money-bill-wave"></i> Adicionar Despesas
                            </a>
                            <a href="orcamentos.php" class="btn btn-secondary">
                                <i class="fas fa-chart-pie"></i> Criar Orçamentos
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recomendações salvas -->
            <div class="saved-recommendations">
                <div class="section-header">
                    <h3><i class="fas fa-save"></i> Recomendações Salvas</h3>
                    <p class="section-subtitle">Recomendações anteriores guardadas no sistema</p>
                </div>
                
                <?php if ($recomendacoes_result->num_rows > 0): ?>
                    <div class="recommendations-list">
                        <?php while ($recomendacao = $recomendacoes_result->fetch_assoc()): ?>
                            <div class="saved-recommendation">
                                <div class="saved-header">
                                    <h4><i class="fas fa-comment"></i> Recomendação</h4>
                                    <span class="saved-date"><?php echo date('d/m/Y', strtotime($recomendacao['data_recomendacao'])); ?></span>
                                </div>
                                <div class="saved-body">
                                    <p><?php echo htmlspecialchars($recomendacao['text_recomendacao']); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-saved">
                        <i class="fas fa-inbox"></i>
                        <p>Nenhuma recomendação salva anteriormente.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Dicas gerais -->
            <div class="general-tips">
                <h3><i class="fas fa-graduation-cap"></i> Dicas Financeiras Gerais</h3>
                <div class="tips-grid">
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <div class="tip-content">
                            <h4>Regra 50-30-20</h4>
                            <p>Aloque 50% para necessidades, 30% para desejos e 20% para poupanças.</p>
                        </div>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="tip-content">
                            <h4>Revise Regularmente</h4>
                            <p>Analise suas despesas semanalmente para identificar gastos desnecessários.</p>
                        </div>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="tip-content">
                            <h4>Fundo de Emergência</h4>
                            <p>Mantenha uma reserva equivalente a 3-6 meses de despesas essenciais.</p>
                        </div>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="tip-content">
                            <h4>Metas Realistas</h4>
                            <p>Estabeleça objetivos financeiros alcançáveis e com prazos definidos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/header_footer.js"></script>
    <script src="js/recomendacoes.js"></script>
</body>
</html>

<?php $conn->close(); ?>
