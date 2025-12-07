// Função para inicializar o header
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do DOM
    const profileDropdown = document.getElementById('profileDropdown');
    const userDropdown = document.getElementById('userDropdown');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Alternar dropdown do perfil
    if (profileDropdown) {
        profileDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
    }
    
    // Fechar dropdown ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-profile')) {
            if (userDropdown) userDropdown.classList.remove('show');
        }
    });
    
    // Alternar menu móvel
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            
            // Alterar ícone do botão
            const icon = mobileMenuBtn.querySelector('i');
            if (mainNav.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Ativar link de navegação ao clicar
    if (navLinks.length > 0) {
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Se for mobile, fechar menu após clicar
                if (window.innerWidth <= 768) {
                    mainNav.classList.remove('active');
                    if (mobileMenuBtn) {
                        mobileMenuBtn.querySelector('i').classList.remove('fa-times');
                        mobileMenuBtn.querySelector('i').classList.add('fa-bars');
                    }
                }
            });
        });
    }
    
    // Logout com confirmação
    const logoutBtn = document.querySelector('.logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const logoutUrl = this.getAttribute('href');
            
            if (confirm('Tem certeza que deseja sair?')) {
                window.location.href = logoutUrl;
            }
        });
    }
    
    // Função para atualizar o saldo periodicamente (simulação)
    function updateBalancePeriodically() {
        // Esta função pode ser usada para fazer requisições AJAX
        // para atualizar o saldo sem recarregar a página
        console.log('Atualização periódica do saldo pode ser implementada aqui com AJAX');
    }
    
    // Atualizar a cada 60 segundos (opcional)
    // setInterval(updateBalancePeriodically, 60000);
    
});
// Toggle do Dropdown do Usuário
document.getElementById('profileDropdown').addEventListener('click', function(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('active');
});

// Fechar dropdown ao clicar fora
document.addEventListener('click', function(e) {
    const userProfile = document.getElementById('userProfile');
    const dropdown = document.getElementById('userDropdown');
    
    if (!userProfile.contains(e.target)) {
        dropdown.classList.remove('active');
    }
});

// Fechar ao clicar em um item
document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function() {
        document.getElementById('userDropdown').classList.remove('active');
    });
});
