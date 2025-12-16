<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: main/indexv1.html');
    exit();
}

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
    <?php
    // Incluir o header dinâmico
    require_once 'header_footer/header.php';
    ?>

    <!-- Conteúdo Principal Simples -->
    <main class="main-content">
        <div class="container">
            <div class="welcome-message">
                <h2>Bem-vindo ao Gestor de Finanças Pessoais</h2>
                
                <div class="quick-stats">
                    <div class="stat-box">
                        <h3><i class="fas fa-user"></i> Informações do Usuário</h3>
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($user['nome']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Ocupação:</strong> <?php echo htmlspecialchars($user['ocupacao']); ?></p>
                    </div>
                    
                    <div class="stat-box">
                        <h3><i class="fas fa-chart-bar"></i> Resumo Financeiro</h3>
                        <p><strong>Salário:</strong> € <?php echo number_format($user['salario'], 2, ',', '.'); ?></p>
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

    <script src="js/header_footer.js"></script>
    <script src="js/Responsive_Scale.js"></script>
</body>
</html>