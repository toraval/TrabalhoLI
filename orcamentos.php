<?php
// orcamentos.php - Gestão de Orçamentos Mensais
session_start();

// Verificar se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: indexv1.html');
    exit();
}

include 'header_footer/header.php';

// Incluir header
if (!isset($conn)) {
    require_once 'connect_db.php';  // Ajuste este caminho!
}


$user_id = $_SESSION['user_id'];

// Processar requisições POST
$mensagem = '';
$tipo_mensagem = '';

// 1. ADICIONAR NOVO ORÇAMENTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'adicionar') {
    $categoria = trim($_POST['categoria'] ?? '');
    $limite_mensal = floatval($_POST['limite_mensal'] ?? 0);
    $percentagem_alerta = floatval($_POST['percentagem_alerta'] ?? 80);
    
            // em vez do INSERT simples, usar INSERT ... ON DUPLICATE KEY UPDATE
        $sql = "INSERT INTO orcamentos (id_utilizador, categoria, limite_mensal, percentagem_alerta, data_criacao)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    limite_mensal = VALUES(limite_mensal),
                    percentagem_alerta = VALUES(percentagem_alerta)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("isdd", $user_id, $categoria, $limite_mensal, $percentagem_alerta);

            if ($stmt->execute()) {
                $mensagem = "✓ Orçamento adicionado/atualizado com sucesso!";
                $tipo_mensagem = "success";
            } else {
                $mensagem = "✗ Erro ao guardar orçamento.";
                $tipo_mensagem = "error";
            }

            $stmt->close();
        } else {
            $mensagem = "✗ Erro na preparação da consulta.";
            $tipo_mensagem = "error";
        }

}

// 2. ATUALIZAR ORÇAMENTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'editar') {
    $id_orcamento = intval($_POST['id_orcamento'] ?? 0);
    $limite_mensal = floatval($_POST['limite_mensal'] ?? 0);
    $percentagem_alerta = floatval($_POST['percentagem_alerta'] ?? 80);
    
    $sql = "UPDATE orcamentos SET limite_mensal = ?, percentagem_alerta = ? 
            WHERE id = ? AND id_utilizador = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddii", $limite_mensal, $percentagem_alerta, $id_orcamento, $user_id);
    
    if ($stmt->execute()) {
        $mensagem = "Orçamento atualizado com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao atualizar orçamento.";
        $tipo_mensagem = "error";
    }
    $stmt->close();
}

// 3. ELIMINAR ORÇAMENTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'eliminar') {
    $id_orcamento = intval($_POST['id_orcamento'] ?? 0);
    
    $sql = "DELETE FROM orcamentos WHERE id = ? AND id_utilizador = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_orcamento, $user_id);
    
    if ($stmt->execute()) {
        $mensagem = "Orçamento eliminado com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao eliminar orçamento.";
        $tipo_mensagem = "error";
    }
    $stmt->close();
}

// BUSCAR ORÇAMENTOS DO UTILIZADOR
$sql_orcamentos = "SELECT * FROM orcamentos WHERE id_utilizador = ? ORDER BY categoria ASC";
$stmt_orcamentos = $conn->prepare($sql_orcamentos);
$stmt_orcamentos->bind_param("i", $user_id);
$stmt_orcamentos->execute();
$result_orcamentos = $stmt_orcamentos->get_result();

// Calcular despesas por categoria para este mês
$despesas_por_categoria = array();
$mes_atual = date('m');
$ano_atual = date('Y');

$sql_despesas = "SELECT categoria, SUM(valor_mensal) as total 
                 FROM despesas 
                 WHERE id_utilizador = ? AND MONTH(data_criacao) = ? AND YEAR(data_criacao) = ?
                 GROUP BY categoria";
$stmt_despesas = $conn->prepare($sql_despesas);
$stmt_despesas->bind_param("iss", $user_id, $mes_atual, $ano_atual);
$stmt_despesas->execute();
$result_despesas = $stmt_despesas->get_result();

while ($row = $result_despesas->fetch_assoc()) {
    $despesas_por_categoria[$row['categoria']] = $row['total'];
}
$stmt_despesas->close();

// Calcular resumo geral dos orçamentos
$total_orcamentado = 0;
$total_gasto = 0;
$orcamentos_array = array();

while ($row = $result_orcamentos->fetch_assoc()) {
    $categoria = $row['categoria'];
    $limite = $row['limite_mensal'];
    $gasto = isset($despesas_por_categoria[$categoria]) ? $despesas_por_categoria[$categoria] : 0;
    $percentagem = ($limite > 0) ? ($gasto / $limite) * 100 : 0;
    $percentagem = min(100, max(0, $percentagem));
    
    $total_orcamentado += $limite;
    $total_gasto += $gasto;
    
    $orcamentos_array[] = array(
        'id' => $row['id'],
        'categoria' => $categoria,
        'limite' => $limite,
        'gasto' => $gasto,
        'percentagem' => $percentagem,
        'percentagem_alerta' => $row['percentagem_alerta'],
        'esta_acima' => $percentagem > $row['percentagem_alerta']
    );
}
$stmt_orcamentos->close();

// Calcular saldo disponível total
$orcamento_disponivel = $total_orcamentado - $total_gasto;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamentos - FinanceControl</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/orcamentos.css">
</head>
<body>
    
    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-chart-pie"></i> Gestão de Orçamentos</h2>
            <p class="page-subtitle">Defina limites de gastos por categoria e acompanhe o seu progresso mensal</p>
        </div>

        <!-- MENSAGENS DE FEEDBACK -->
        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                <i class="fas fa-<?php echo $tipo_mensagem == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- RESUMO DOS ORÇAMENTOS -->
        <div class="financial-summary">
            <div class="summary-box">
                <h3><i class="fas fa-wallet"></i> Resumo Geral dos Orçamentos</h3>
                <div class="summary-content">
                    <div class="summary-item">
                        <span class="label">Total Orçamentado</span>
                        <span class="value salary"><?php echo number_format($total_orcamentado, 2, ',', '.'); ?> €</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Total Gasto Este Mês</span>
                        <span class="value expenses"><?php echo number_format($total_gasto, 2, ',', '.'); ?> €</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Disponível</span>
                        <span class="value balance <?php echo $orcamento_disponivel >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo number_format($orcamento_disponivel, 2, ',', '.'); ?> €
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Utilização Média</span>
                        <span class="value percentage"><?php echo number_format(($total_orcamentado > 0 ? ($total_gasto / $total_orcamentado) * 100 : 0), 1, ',', '.'); ?>%</span>
                    </div>
                </div>
                <div class="progress-bar" style="margin-top: 20px;">
                    <div class="progress-fill" style="width: <?php echo number_format(min(100, ($total_orcamentado > 0 ? ($total_gasto / $total_orcamentado) * 100 : 0)), 1); ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- FORMULÁRIO PARA ADICIONAR NOVO ORÇAMENTO -->
        <div class="form-section">
            <h3><i class="fas fa-plus-circle"></i> Adicionar Novo Orçamento</h3>
            <form method="POST" class="orcamento-form">
                <input type="hidden" name="acao" value="adicionar">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="categoria"><i class="fas fa-tag"></i> Categoria</label>
                        <select name="categoria" id="categoria" required>
                            <option value="">Selecione uma categoria</option>
                            <option value="Alimentação">Alimentação</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Habitação">Habitação</option>
                            <option value="Saúde">Saúde</option>
                            <option value="Educação">Educação</option>
                            <option value="Lazer">Lazer</option>
                            <option value="Utilities">Utilities (Água, Luz, Gás)</option>
                            <option value="Seguros">Seguros</option>
                            <option value="Poupança">Poupança</option>
                            <option value="Investimentos">Investimentos</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="limite_mensal"><i class="fas fa-euro-sign"></i> Limite Mensal (€)</label>
                        <input type="number" name="limite_mensal" id="limite_mensal" step="0.01" min="0" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="percentagem_alerta"><i class="fas fa-bell"></i> Alerta em %</label>
                        <input type="number" name="percentagem_alerta" id="percentagem_alerta" step="5" min="0" max="100" value="80">
                        <small>Você será alertado quando atingir este % do limite</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Adicionar Orçamento
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpar
                    </button>
                </div>
            </form>
        </div>

        <!-- LISTA DE ORÇAMENTOS -->
        <div class="orcamentos-list-section">
            <h3><i class="fas fa-list"></i> Seus Orçamentos</h3>
            
            <?php if (count($orcamentos_array) > 0): ?>
                <div class="orcamentos-grid">
                    <?php foreach ($orcamentos_array as $orcamento): ?>
                        <div class="orcamento-card <?php echo $orcamento['esta_acima'] ? 'alerta-ativo' : ''; ?>">
                            <div class="card-header">
                                <h4><i class="fas fa-tag"></i> <?php echo htmlspecialchars($orcamento['categoria']); ?></h4>
                                <?php if ($orcamento['esta_acima']): ?>
                                    <span class="badge-alerta"><i class="fas fa-exclamation-triangle"></i> Alerta!</span>
                                <?php else: ?>
                                    <span class="badge-ok"><i class="fas fa-check-circle"></i> OK</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <div class="orcamento-info">
                                    <div class="info-item">
                                        <span class="label">Limite</span>
                                        <span class="valor"><?php echo number_format($orcamento['limite'], 2, ',', '.'); ?> €</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Gasto</span>
                                        <span class="valor gasto"><?php echo number_format($orcamento['gasto'], 2, ',', '.'); ?> €</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Disponível</span>
                                        <span class="valor disponivel"><?php echo number_format($orcamento['limite'] - $orcamento['gasto'], 2, ',', '.'); ?> €</span>
                                    </div>
                                </div>

                                <div class="orcamento-progress">
                                    <div class="progress-label">
                                        <span>Utilização: <strong><?php echo number_format($orcamento['percentagem'], 1, ',', '.'); ?>%</strong></span>
                                        <span class="alerta-info">Alerta em: <?php echo intval($orcamento['percentagem_alerta']); ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill <?php echo ($orcamento['percentagem'] > $orcamento['percentagem_alerta']) ? 'danger' : (($orcamento['percentagem'] > 50) ? 'warning' : 'success'); ?>" 
                                             style="width: <?php echo min(100, number_format($orcamento['percentagem'], 1)); ?>%;"></div>
                                    </div>
                                </div>

                                <div class="card-message">
                                    <?php
                                    $restante = $orcamento['limite'] - $orcamento['gasto'];
                                    if ($orcamento['percentagem'] > $orcamento['percentagem_alerta']) {
                                        echo "<i class='fas fa-exclamation-triangle'></i> Ultrapassou o alerta!";
                                    } else if ($orcamento['percentagem'] > 80) {
                                        echo "<i class='fas fa-exclamation-circle'></i> Próximo do limite!";
                                    } else if ($restante > 0) {
                                        echo "<i class='fas fa-smile'></i> " . number_format($restante, 2, ',', '.') . " € disponível";
                                    } else {
                                        echo "<i class='fas fa-ban'></i> Limite ultrapassado em " . number_format(abs($restante), 2, ',', '.') . " €";
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="card-actions">
                                <button class="btn-action edit" onclick="editarOrcamento(<?php echo $orcamento['id']; ?>, '<?php echo htmlspecialchars($orcamento['categoria']); ?>', <?php echo $orcamento['limite']; ?>, <?php echo $orcamento['percentagem_alerta']; ?>)" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja eliminar este orçamento?');">
                                    <input type="hidden" name="acao" value="eliminar">
                                    <input type="hidden" name="id_orcamento" value="<?php echo $orcamento['id']; ?>">
                                    <button type="submit" class="btn-action delete" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>Nenhum orçamento definido</h4>
                    <p>Comece por adicionar seus primeiros orçamentos usando o formulário acima.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- DICAS E RECOMENDAÇÕES -->
        <div class="tips-section">
            <h3><i class="fas fa-lightbulb"></i> Dicas para Gerir Bem Seu Orçamento</h3>
            <div class="tips-grid">
                <div class="tip-card">
                    <i class="fas fa-calculator"></i>
                    <h4>Regra 50/30/20</h4>
                    <p>Dedique 50% da renda para necessidades, 30% para desejos e 20% para poupanças e investimentos.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-chart-line"></i>
                    <h4>Revise Mensalmente</h4>
                    <p>Analise seus gastos mensais e ajuste os orçamentos conforme necessário.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Reserve para Emergências</h4>
                    <p>Mantenha um fundo de emergência equivalente a 3-6 meses de despesas.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-target"></i>
                    <h4>Defina Metas Realistas</h4>
                    <p>Use os alertas para evitar gastos desnecessários e atingir suas metas.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA EDITAR ORÇAMENTO -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="fecharModal()">&times;</span>
            <h3><i class="fas fa-edit"></i> Editar Orçamento</h3>
            <form method="POST" id="formEditar">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id_orcamento" id="id_orcamento">
                
                <div class="form-group">
                    <label>Categoria</label>
                    <input type="text" id="categoria_edit" disabled style="background-color: #f0f0f0;">
                </div>

                <div class="form-group">
                    <label for="limite_mensal_edit"><i class="fas fa-euro-sign"></i> Limite Mensal (€)</label>
                    <input type="number" name="limite_mensal" id="limite_mensal_edit" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="percentagem_alerta_edit"><i class="fas fa-bell"></i> Alerta em %</label>
                    <input type="number" name="percentagem_alerta" id="percentagem_alerta_edit" step="5" min="0" max="100" required>
                </div>

                <div class="form-actions" style="justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/header_footer.js"></script>
    <script src="js/orcamentos.js"></script>
</body>
</html>
