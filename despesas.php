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
$sql_despesas = "SELECT * FROM despesas WHERE id_utilizador = ? ORDER BY categoria";
$stmt_despesas = $conn->prepare($sql_despesas);
$stmt_despesas->bind_param("i", $user_id);
$stmt_despesas->execute();
$despesas_result = $stmt_despesas->get_result();

// Calcular total de despesas
$total_despesas = 0;
$despesas_por_categoria = [];
while ($despesa = $despesas_result->fetch_assoc()) {
    $total_despesas += $despesa['valor_mensal'];
    $despesas_por_categoria[$despesa['categoria']] = $despesa;
}

// Processar formulário de adicionar/editar despesa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $categoria = trim($_POST['categoria']);
        $valor_mensal = floatval(str_replace(',', '.', $_POST['valor_mensal']));
        $fixa_variavel = $_POST['fixa_variavel'];
        
        if ($_POST['action'] == 'add') {
            // Adicionar nova despesa
            $sql_insert = "INSERT INTO despesas (categoria, valor_mensal, fixa_variavel, id_utilizador) 
                          VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sdsi", $categoria, $valor_mensal, $fixa_variavel, $user_id);
            
            if ($stmt_insert->execute()) {
                $_SESSION['message'] = 'Despesa adicionada com sucesso!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Erro ao adicionar despesa.';
                $_SESSION['message_type'] = 'error';
            }
            
        } elseif ($_POST['action'] == 'edit' && isset($_POST['id_despesas'])) {
            // Editar despesa existente
            $id_despesas = $_POST['id_despesas'];
            $sql_update = "UPDATE despesas SET categoria = ?, valor_mensal = ?, fixa_variavel = ? 
                          WHERE id_despesas = ? AND id_utilizador = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sdsii", $categoria, $valor_mensal, $fixa_variavel, $id_despesas, $user_id);
            
            if ($stmt_update->execute()) {
                $_SESSION['message'] = 'Despesa atualizada com sucesso!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Erro ao atualizar despesa.';
                $_SESSION['message_type'] = 'error';
            }
            
        } elseif ($_POST['action'] == 'delete' && isset($_POST['id_despesas'])) {
            // Eliminar despesa
            $id_despesas = $_POST['id_despesas'];
            $sql_delete = "DELETE FROM despesas WHERE id_despesas = ? AND id_utilizador = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("ii", $id_despesas, $user_id);
            
            if ($stmt_delete->execute()) {
                $_SESSION['message'] = 'Despesa eliminada com sucesso!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Erro ao eliminar despesa.';
                $_SESSION['message_type'] = 'error';
            }
        }
        
        // Redirecionar para evitar re-submissão do formulário
        header('Location: despesas.php');
        exit();
    }
}

// Rebuscar despesas após operações
$despesas_result = $stmt_despesas->execute();
$despesas_result = $stmt_despesas->get_result();

// Calcular percentual do orçamento
$percentual_orcamento = ($user['salario'] > 0) ? ($total_despesas / $user['salario']) * 100 : 0;
$saldo_disponivel = $user['salario'] - $total_despesas;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Finanças - Despesas</title>
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/despesas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once 'header_footer/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h2><i class="fas fa-money-bill-wave"></i> Gestão de Despesas</h2>
                <p class="page-subtitle">Gerencie todas as suas despesas mensais de forma organizada</p>
            </div>

            <!-- Mensagens de feedback -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php echo $_SESSION['message']; ?>
                    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                </div>
            <?php endif; ?>

            <!-- Resumo financeiro -->
            <div class="financial-summary">
                <div class="summary-box">
                    <h3><i class="fas fa-wallet"></i> Resumo Financeiro</h3>
                    <div class="summary-content">
                        <div class="summary-item">
                            <span class="label">Salário Mensal:</span>
                            <span class="value salary">€ <?php echo number_format($user['salario'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Total de Despesas:</span>
                            <span class="value expenses">€ <?php echo number_format($total_despesas, 2, ',', '.'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Saldo Disponível:</span>
                            <span class="value balance <?php echo $saldo_disponivel < 0 ? 'negative' : 'positive'; ?>">
                                € <?php echo number_format($saldo_disponivel, 2, ',', '.'); ?>
                            </span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Orçamento Usado:</span>
                            <span class="value percentage"><?php echo round($percentual_orcamento); ?>%</span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo min($percentual_orcamento, 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário de adicionar/editar despesa -->
            <div class="form-section">
                <h3><i class="fas fa-plus-circle"></i> <?php echo isset($_GET['edit']) ? 'Editar Despesa' : 'Adicionar Nova Despesa'; ?></h3>
                <form method="POST" class="despesa-form">
                    <input type="hidden" name="action" value="<?php echo isset($_GET['edit']) ? 'edit' : 'add'; ?>">
                    <?php if (isset($_GET['edit'])): ?>
                        <input type="hidden" name="id_despesas" value="<?php echo $_GET['edit']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria"><i class="fas fa-tag"></i> Categoria *</label>
                            <input type="text" id="categoria" name="categoria" required 
                                   value="<?php echo isset($_GET['edit']) && isset($despesas_por_categoria[$_GET['edit']]) 
                                           ? htmlspecialchars($despesas_por_categoria[$_GET['edit']]['categoria']) 
                                           : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_mensal"><i class="fas fa-euro-sign"></i> Valor Mensal (€) *</label>
                            <input type="number" id="valor_mensal" name="valor_mensal" step="0.01" min="0" required 
                                   value="<?php echo isset($_GET['edit']) && isset($despesas_por_categoria[$_GET['edit']]) 
                                           ? number_format($despesas_por_categoria[$_GET['edit']]['valor_mensal'], 2, '.', '') 
                                           : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fixa_variavel"><i class="fas fa-chart-line"></i> Tipo *</label>
                            <select id="fixa_variavel" name="fixa_variavel" required>
                                <option value="">Selecione...</option>
                                <option value="Fixa" <?php echo (isset($_GET['edit']) && isset($despesas_por_categoria[$_GET['edit']]) && $despesas_por_categoria[$_GET['edit']]['fixa_variavel'] == 'Fixa') ? 'selected' : ''; ?>>Fixa</option>
                                <option value="Variavel" <?php echo (isset($_GET['edit']) && isset($despesas_por_categoria[$_GET['edit']]) && $despesas_por_categoria[$_GET['edit']]['fixa_variavel'] == 'Variavel') ? 'selected' : ''; ?>>Variável</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo isset($_GET['edit']) ? 'Atualizar Despesa' : 'Adicionar Despesa'; ?>
                        </button>
                        <?php if (isset($_GET['edit'])): ?>
                            <a href="despesas.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Lista de despesas -->
            <div class="despesas-list-section">
                <h3><i class="fas fa-list"></i> Suas Despesas (<?php echo $despesas_result->num_rows; ?>)</h3>
                
                <?php if ($despesas_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="despesas-table">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Valor Mensal</th>
                                    <th>Tipo</th>
                                    <th>Percentual do Salário</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $despesas_result->data_seek(0);
                                while ($despesa = $despesas_result->fetch_assoc()): 
                                    $percentual_salario = ($user['salario'] > 0) ? ($despesa['valor_mensal'] / $user['salario']) * 100 : 0;
                                ?>
                                    <tr>
                                        <td class="categoria">
                                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($despesa['categoria']); ?>
                                        </td>
                                        <td class="valor">€ <?php echo number_format($despesa['valor_mensal'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="tipo-badge <?php echo strtolower($despesa['fixa_variavel']); ?>">
                                                <i class="fas fa-<?php echo $despesa['fixa_variavel'] == 'Fixa' ? 'lock' : 'chart-line'; ?>"></i>
                                                <?php echo $despesa['fixa_variavel']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="percent-container">
                                                <span class="percent-value"><?php echo round($percentual_salario, 1); ?>%</span>
                                                <div class="percent-bar">
                                                    <div class="percent-fill" style="width: <?php echo min($percentual_salario, 100); ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="actions">
                                            <a href="despesas.php?edit=<?php echo $despesa['id_despesas']; ?>" class="btn-action edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="delete-form" onsubmit="return confirm('Tem certeza que deseja eliminar esta despesa?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_despesas" value="<?php echo $despesa['id_despesas']; ?>">
                                                <button type="submit" class="btn-action delete" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td><strong>Total</strong></td>
                                    <td class="total-valor"><strong>€ <?php echo number_format($total_despesas, 2, ',', '.'); ?></strong></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt fa-3x"></i>
                        <h4>Nenhuma despesa registada</h4>
                        <p>Comece por adicionar suas primeiras despesas usando o formulário acima.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/header_footer.js"></script>
    
    <script>
        // Formatação automática de valores monetários
        document.addEventListener('DOMContentLoaded', function() {
            const valorInput = document.getElementById('valor_mensal');
            if (valorInput) {
                valorInput.addEventListener('blur', function() {
                    let value = parseFloat(this.value);
                    if (!isNaN(value)) {
                        this.value = value.toFixed(2);
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>