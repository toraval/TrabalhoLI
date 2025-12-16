<?php
// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexão com a base de dados
require_once 'connect_db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Redirecionar para página de login/landing
    header('Location: indexv1.html');
    exit();
}

// Usar o ID do usuário da sessão
$user_id = $_SESSION['user_id'];

// Buscar informações do usuário no banco de dados
$sql_user = "SELECT id, nome, email, ocupacao, salario FROM utilizadores WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    
    // Buscar despesas do usuário para calcular saldo restante
    $sql_despesas = "SELECT SUM(valor_mensal) as total_despesas FROM despesas WHERE id_utilizador = ?";
    $stmt_despesas = $conn->prepare($sql_despesas);
    $stmt_despesas->bind_param("i", $user_id);
    $stmt_despesas->execute();
    $result_despesas = $stmt_despesas->get_result();
    
    $total_despesas = 0;
    if ($result_despesas->num_rows > 0) {
        $row_despesas = $result_despesas->fetch_assoc();
        $total_despesas = $row_despesas['total_despesas'] ?: 0;
    }
    
    // Buscar receitas do usuário
    $sql_receitas = "SELECT SUM(valor) as total_receitas FROM receitas WHERE id_utilizador = ?";
    $stmt_receitas = $conn->prepare($sql_receitas);
    $stmt_receitas->bind_param("i", $user_id);
    $stmt_receitas->execute();
    $result_receitas = $stmt_receitas->get_result();
    
    $total_receitas = 0;
    if ($result_receitas->num_rows > 0) {
        $row_receitas = $result_receitas->fetch_assoc();
        $total_receitas = $row_receitas['total_receitas'] ?: 0;
    }
    
    // Calcular saldo disponível (salário + receitas extras - despesas)
    $salario = $user['salario'] ?: 0;
    $saldo_disponivel = ($salario + $total_receitas) - $total_despesas;
    
    // Calcular percentual do orçamento usado (baseado apenas no salário)
    $percentual_orcamento = $salario > 0 ? ($total_despesas / $salario) * 100 : 0;
    $percentual_orcamento = min(100, max(0, $percentual_orcamento));
    
} else {
    // Usuário não encontrado na base de dados - fazer logout
    session_destroy();
    header('Location: indexv1.html');
    exit();
}

$stmt_user->close();
if (isset($stmt_despesas)) $stmt_despesas->close();
if (isset($stmt_receitas)) $stmt_receitas->close();
?>

<!-- Header Principal -->
<header class="header" id="mainHeader">
    <div class="container">
        <!-- Logo e Nome do Site -->
        <div class="logo-section">
            <div class="logo">
                <!--<i class="fas fa-chart-line"></i>-->
                <img src="img/logo.png" alt="FinancialManager Logo" class="logo-img">

                <div class="logo-text">
                    <h1>Financial<span>Manager</span></h1>
                    <p class="tagline">Gestão financeira pessoal</p>
                </div>
            </div>
        </div>

        <!-- Menu de Navegação -->
        <nav class="main-nav" id="mainNav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="despesas.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'despesas.php') ? 'active' : ''; ?>">
                        <i class="fas fa-wallet"></i>
                        <span>Despesas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="receitas.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'receitas.php') ? 'active' : ''; ?>">
                        <i class="fas fa-coins"></i>
                        <span>Receitas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="orcamentos.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'orcamentos.php') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Orçamento</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="recomendacoes.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'recomendacoes.php') ? 'active' : ''; ?>">
                        <i class="fas fa-lightbulb"></i>
                        <span>Recomendações</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Área do Usuário -->
        <div class="user-section">
            <!-- Indicador de Saldo -->
            <div class="balance-indicator">
                <div class="balance-info">
                    <span class="balance-label">Saldo Disponível</span>
                    <span class="balance-amount">€ <span id="currentBalance"><?php echo number_format($saldo_disponivel, 2, ',', '.'); ?></span></span>
                </div>
                <div class="balance-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="balanceProgress" style="width: <?php echo round($percentual_orcamento); ?>%"></div>
                    </div>
                    <span class="progress-text"><?php echo round($percentual_orcamento); ?>% do orçamento</span>
                </div>
            </div>

            <!-- Separador vertical -->
            <div class="vertical-separator"></div>

            <!-- Perfil do Usuário -->
            <div class="user-profile" id="userProfile">
                <div class="avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <span class="user-name" id="userName"><?php echo htmlspecialchars($user['nome']); ?></span>
                    <span class="user-role" id="userRole"><?php echo htmlspecialchars($user['ocupacao']); ?></span>
                </div>
                <button class="dropdown-btn" id="profileDropdown">
                    <i class="fas fa-chevron-down"></i>
                </button>
                
                <!-- Menu Dropdown do Usuário -->
                <div class="dropdown-menu" id="userDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div>
                            <h3 id="dropdownUserName"><?php echo htmlspecialchars($user['nome']); ?></h3>
                            <p id="dropdownUserEmail"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>

                    <a href="status.php" class="dropdown-item status">
                        <i class="fas fa-chart-line"></i> Status
                    </a>

                    <a href="importar_extrato.php" class="dropdown-item importar">
                        <i class="fas fa-file-import"></i> Importar Extrato
                    </a>

                    <a href="historico.php" class="dropdown-item importar">
                        <i class="fas fa-history"></i> Historico
                    </a>

                    <div class="dropdown-divider"></div>
                    
                    <a href="logout.php" class="dropdown-item logout">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>

            <!-- Botão Mobile Menu -->
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>