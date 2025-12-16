<?php
session_start();
require_once 'connect_db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: main/indexv1.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Buscar informações do utilizador
$user_query = "SELECT id, nome, email, salario, ocupacao FROM utilizadores WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Processar alteração do salário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_salario'])) {
    $novo_salario = floatval(str_replace(',', '.', $_POST['novo_salario']));
    
    if ($novo_salario >= 0) {
        $update_query = "UPDATE utilizadores SET salario = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("di", $novo_salario, $user_id);
        
        if ($stmt->execute()) {
            $mensagem_sucesso = "Salário atualizado com sucesso!";
            $user['salario'] = $novo_salario;
        } else {
            $mensagem_erro = "Erro ao atualizar salário: " . $conn->error;
        }
        $stmt->close();
    } else {
        $mensagem_erro = "Por favor, insira um valor válido para o salário.";
    }
}

// Recalcular o saldo disponível após possível atualização
// Buscar despesas
$despesas_query = "SELECT SUM(valor_mensal) as total_despesas FROM despesas WHERE id_utilizador = ?";
$stmt = $conn->prepare($despesas_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$despesas_result = $stmt->get_result();
$despesas_row = $despesas_result->fetch_assoc();
$total_despesas = $despesas_row['total_despesas'] ?: 0;
$stmt->close();

// Buscar receitas
$receitas_query = "SELECT SUM(valor) as total_receitas FROM receitas WHERE id_utilizador = ?";
$stmt = $conn->prepare($receitas_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$receitas_result = $stmt->get_result();
$receitas_row = $receitas_result->fetch_assoc();
$total_receitas = $receitas_row['total_receitas'] ?: 0;
$stmt->close();

// Calcular saldo disponível
$salario = $user['salario'] ?: 0;
$saldo_disponivel = ($salario + $total_receitas) - $total_despesas;

// Calcular percentual do orçamento usado
$percentual_orcamento = $salario > 0 ? ($total_despesas / $salario) * 100 : 0;
$percentual_orcamento = min(100, max(0, $percentual_orcamento));
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Manager - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="favicon_io/favicon.ico">
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once 'header_footer/header.php'; ?>

    <!-- Conteúdo Principal Simples -->
    <main class="main-content">
        <div class="container">
            <div class="welcome-message">
                <h2>Bem-vindo ao Gestor de Finanças Pessoais</h2>
                
                <?php if (isset($mensagem_sucesso)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $mensagem_sucesso; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($mensagem_erro)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $mensagem_erro; ?>
                    </div>
                <?php endif; ?>
                
                <div class="quick-stats">
                    <div class="stat-box">
                        <h3><i class="fas fa-user"></i> Informações do Usuário</h3>
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($user['nome']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Ocupação:</strong> <?php echo htmlspecialchars($user['ocupacao']); ?></p>
                    </div>
                    
                    <div class="stat-box">
                        <h3><i class="fas fa-chart-bar"></i> Resumo Financeiro</h3>
                        <div class="salario-container">
                            <p><strong>Salário:</strong> 
                                <span class="salario-value">€ <?php echo number_format($user['salario'], 2, ',', '.'); ?></span>
                            </p>
                            <button type="button" class="btn-edit-salario" onclick="abrirModalSalario()">
                                <i class="fas fa-edit"></i> Alterar
                            </button>
                        </div>
                        <p><strong>Saldo Disponível:</strong> € <?php echo number_format($saldo_disponivel, 2, ',', '.'); ?></p>
                        <p><strong>Orçamento Usado:</strong> <?php echo round($percentual_orcamento); ?>%</p>
                    </div>
                </div>
                
                <div class="features">
                    <h3><i class="fas fa-rocket"></i> Funcionalidades Disponíveis</h3>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> <strong>Dashboard:</strong> Visão geral das suas finanças</li>
                        <li><i class="fas fa-check-circle"></i> <strong>Despesas:</strong> Gerencie suas despesas mensais</li>
                        <li><i class="fas fa-check-circle"></i> <strong>Histórico:</strong> Consulte seu histórico financeiro</li>
                        <li><i class="fas fa-check-circle"></i> <strong>Orçamento:</strong> Planeje seu orçamento mensal</li>
                        <li><i class="fas fa-check-circle"></i> <strong>Recomendações:</strong> Receba dicas personalizadas</li>
                    </ul>
                </div>
                
                <div class="next-steps">
                    <h3><i class="fas fa-arrow-right"></i> Próximos Passos</h3>
                    <p>Utilize o menu de navegação acima para acessar as diferentes funcionalidades do sistema.</p>
                    <p>Comece por adicionar suas despesas para ter um controle mais preciso das suas finanças.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para alteração do salário -->
    <div id="modalSalario" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-money-bill-wave"></i> Alterar Salário</h3>
                <button type="button" class="btn-close" onclick="fecharModalSalario()">&times;</button>
            </div>
            <form method="POST" action="" onsubmit="return validarSalario()">
                <div class="modal-body">
                    <div class="modal-form-group">
                        <label for="novo_salario"><i class="fas fa-euro-sign"></i> Novo Valor do Salário (€)</label>
                        <input type="number" 
                               id="novo_salario" 
                               name="novo_salario" 
                               step="0.01" 
                               min="0" 
                               required 
                               placeholder="0,00"
                               value="<?php echo number_format($user['salario'], 2, '.', ''); ?>">
                        <small style="color: #718096; font-size: 0.85rem; margin-top: 5px; display: block;">
                            Este valor será utilizado para calcular o seu orçamento mensal.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-cancel" onclick="fecharModalSalario()">
                        Cancelar
                    </button>
                    <button type="submit" name="atualizar_salario" class="btn-modal btn-modal-submit">
                        <i class="fas fa-save"></i> Atualizar Salário
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/header_footer.js"></script>
    <script src="js/Responsive_Scale.js"></script>
    <script src="js/index.js"></script>
</body>
</html>