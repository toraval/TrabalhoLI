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
$user_query = "SELECT id, nome, email, salario FROM utilizadores WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Processar adição de nova receita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_receita'])) {
    $descricao = trim($_POST['descricao']);
    $valor = floatval(str_replace(',', '.', $_POST['valor']));
    $tipo = $_POST['tipo'];
    $data_recebimento = $_POST['data_recebimento'];
    
    if ($descricao && $valor > 0 && $data_recebimento) {
        // Inserir a receita
        $insert_query = "INSERT INTO receitas (descricao, valor, tipo, data_recebimento, id_utilizador) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sdssi", $descricao, $valor, $tipo, $data_recebimento, $user_id);
        
        if ($stmt->execute()) {
            $mensagem_sucesso = "Receita adicionada com sucesso! O seu saldo disponível foi atualizado.";
        } else {
            $mensagem_erro = "Erro ao adicionar receita: " . $conn->error;
        }
        $stmt->close();
    } else {
        $mensagem_erro = "Por favor, preencha todos os campos corretamente.";
    }
}

// Processar exclusão de receita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_receita'])) {
    $id_receita = intval($_POST['id_receita']);
    
    if ($id_receita > 0) {
        $delete_query = "DELETE FROM receitas WHERE id_receita = ? AND id_utilizador = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $id_receita, $user_id);
        
        if ($stmt->execute()) {
            $mensagem_sucesso = "Receita excluída com sucesso!";
        } else {
            $mensagem_erro = "Erro ao excluir receita.";
        }
        $stmt->close();
    }
}

// Buscar todas as receitas do utilizador
$receitas_query = "SELECT * FROM receitas WHERE id_utilizador = ? ORDER BY data_recebimento DESC, data_criacao DESC";
$stmt = $conn->prepare($receitas_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$receitas_result = $stmt->get_result();
$receitas = $receitas_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular total de receitas
$total_receitas = 0;
foreach ($receitas as $receita) {
    $total_receitas += $receita['valor'];
}

// Calcular saldo disponível (para mostrar na página)
$despesas_query = "SELECT SUM(valor_mensal) as total_despesas FROM despesas WHERE id_utilizador = ?";
$stmt = $conn->prepare($despesas_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$despesas_result = $stmt->get_result();
$despesas_row = $despesas_result->fetch_assoc();
$total_despesas = $despesas_row['total_despesas'] ?: 0;
$stmt->close();

$salario = $user['salario'] ?: 0;
$saldo_disponivel = ($salario + $total_receitas) - $total_despesas;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Receitas - FinancialManager</title>
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/receitas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once 'header_footer/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-coins"></i> Gestão de Receitas</h1>
                <p class="subtitle">Adicione e gerencie suas receitas extras</p>
            </div>

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

            <!-- Resumo Financeiro -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="card-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="card-content">
                        <h3>Salário Base</h3>
                        <p class="amount">€ <?php echo number_format($salario, 2, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="card-content">
                        <h3>Receitas Extras</h3>
                        <p class="amount">€ <?php echo number_format($total_receitas, 2, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div class="summary-card highlight">
                    <div class="card-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="card-content">
                        <h3>Saldo Disponível</h3>
                        <p class="amount">€ <?php echo number_format($saldo_disponivel, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Formulário para Adicionar Receita -->
            <div class="form-section">
                <h2><i class="fas fa-plus-circle"></i> Adicionar Nova Receita</h2>
                <form method="POST" action="" class="receita-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="descricao"><i class="fas fa-file-alt"></i> Descrição *</label>
                            <input type="text" id="descricao" name="descricao" required 
                                   placeholder="Ex: Freelance, Venda online, Presente...">
                        </div>
                        
                        <div class="form-group">
                            <label for="valor"><i class="fas fa-euro-sign"></i> Valor (€) *</label>
                            <input type="number" id="valor" name="valor" step="0.01" min="0.01" required 
                                   placeholder="0,00">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo"><i class="fas fa-tag"></i> Tipo *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="Extra" selected>Extra</option>
                                <option value="Salário">Salário</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="data_recebimento"><i class="fas fa-calendar-alt"></i> Data de Recebimento *</label>
                            <input type="date" id="data_recebimento" name="data_recebimento" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <button type="submit" name="adicionar_receita" class="btn-submit">
                        <i class="fas fa-plus"></i> Adicionar Receita
                    </button>
                </form>
            </div>

            <!-- Lista de Receitas -->
            <div class="receitas-list">
                <h2><i class="fas fa-list"></i> Suas Receitas</h2>
                
                <?php if (count($receitas) > 0): ?>
                    <div class="table-responsive">
                        <table class="receitas-table">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Tipo</th>
                                    <th>Data Recebimento</th>
                                    <th>Data Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($receitas as $receita): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($receita['descricao']); ?></td>
                                        <td class="valor">€ <?php echo number_format($receita['valor'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="tipo-badge tipo-<?php echo strtolower($receita['tipo']); ?>">
                                                <?php echo $receita['tipo']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($receita['data_recebimento'])); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($receita['data_criacao'])); ?></td>
                                        <td>
                                            <form method="POST" action="" class="delete-form">
                                                <input type="hidden" name="id_receita" value="<?php echo $receita['id_receita']; ?>">
                                                <button type="submit" name="excluir_receita" class="btn-delete" 
                                                        onclick="return confirm('Tem certeza que deseja excluir esta receita?')">
                                                    <i class="fas fa-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="total-row">
                                        <strong>Total de Receitas Extras:</strong> 
                                        € <?php echo number_format($total_receitas, 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-coins"></i>
                        <h3>Nenhuma receita registrada</h3>
                        <p>Adicione sua primeira receita extra usando o formulário acima.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Definir data atual como padrão para o campo de data
        document.getElementById('data_recebimento').value = '<?php echo date("Y-m-d"); ?>';
    </script>
</body>
</html>