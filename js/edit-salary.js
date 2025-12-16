// Funcionalidade para editar salário
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do DOM
    const editSalaryBtn = document.getElementById('editSalaryBtn');
    const editSalaryModal = document.getElementById('editSalaryModal');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const editSalaryForm = document.getElementById('editSalaryForm');
    const newSalaryInput = document.getElementById('newSalary');
    const modalAlert = document.getElementById('modalAlert');
    
    // Valores atuais (preenchidos via PHP)
    const currentSalary = parseFloat(document.getElementById('currentSalaryValue').textContent.replace(/[^\d.-]/g, ''));
    const userId = document.getElementById('userId').value;
    
    // Abrir modal
    if (editSalaryBtn) {
        editSalaryBtn.addEventListener('click', function() {
            editSalaryModal.classList.add('active');
            newSalaryInput.value = currentSalary.toFixed(2);
            newSalaryInput.focus();
            newSalaryInput.select();
        });
    }
    
    // Fechar modal
    const closeModal = function() {
        editSalaryModal.classList.remove('active');
        modalAlert.className = 'modal-alert';
        modalAlert.style.display = 'none';
    };
    
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    
    // Fechar modal clicando fora
    editSalaryModal.addEventListener('click', function(e) {
        if (e.target === editSalaryModal) {
            closeModal();
        }
    });
    
    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && editSalaryModal.classList.contains('active')) {
            closeModal();
        }
    });
    
    // Submeter formulário
    if (editSalaryForm) {
        editSalaryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newSalary = parseFloat(newSalaryInput.value);
            
            // Validação
            if (isNaN(newSalary) || newSalary <= 0) {
                showAlert('Por favor, insira um valor válido para o salário.', 'error');
                return;
            }
            
            // Enviar via AJAX
            updateSalary(newSalary);
        });
    }
    
    // Função para mostrar alerta
    function showAlert(message, type) {
        modalAlert.textContent = message;
        modalAlert.className = `modal-alert ${type}`;
        modalAlert.style.display = 'block';
        
        // Rolar para o alerta
        modalAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    // Função para atualizar salário via AJAX
    function updateSalary(newSalary) {
        // Mostrar loading no botão
        const saveBtn = document.querySelector('.btn-save');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A atualizar...';
        saveBtn.disabled = true;
        
        // Criar FormData
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('new_salary', newSalary.toFixed(2));
        
        // Enviar requisição
        fetch('update_salary.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar valores na página
                document.getElementById('currentSalaryValue').textContent = formatCurrency(newSalary);
                
                // Atualizar saldo disponível se o elemento existir
                const currentBalanceElement = document.getElementById('currentBalance');
                if (currentBalanceElement) {
                    const currentBalance = parseFloat(currentBalanceElement.textContent.replace(/[^\d.-]/g, ''));
                    const balanceDiff = newSalary - currentSalary;
                    const newBalance = currentBalance + balanceDiff;
                    currentBalanceElement.textContent = formatCurrency(newBalance);
                }
                
                // Mostrar mensagem de sucesso
                showAlert('Salário atualizado com sucesso!', 'success');
                
                // Fechar modal após 2 segundos
                setTimeout(() => {
                    closeModal();
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }, 2000);
                
            } else {
                showAlert(data.message || 'Erro ao atualizar salário.', 'error');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erro de conexão. Por favor, tente novamente.', 'error');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
    // Função para formatar moeda
    function formatCurrency(value) {
        return '€ ' + value.toFixed(2).replace('.', ',');
    }
});