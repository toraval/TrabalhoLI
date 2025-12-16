function abrirModalSalario() {
            document.getElementById('modalSalario').style.display = 'flex';
        }
        
        function fecharModalSalario() {
            document.getElementById('modalSalario').style.display = 'none';
        }
        
        function validarSalario() {
            const salarioInput = document.getElementById('novo_salario');
            const salario = parseFloat(salarioInput.value);
            
            if (salario < 0) {
                alert('Por favor, insira um valor positivo para o salário.');
                salarioInput.focus();
                return false;
            }
            
            if (salario > 1000000) { // Limite razoável
                if (!confirm('O valor inserido é muito alto. Tem certeza que deseja continuar?')) {
                    salarioInput.focus();
                    return false;
                }
            }
            
            return true;
        }
        
        // Fechar modal ao clicar fora dele
        document.getElementById('modalSalario').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalSalario();
            }
        });
        
        // Fechar modal com tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fecharModalSalario();
            }
        });
        
        // Focar no campo de input ao abrir o modal
        document.querySelector('.btn-edit-salario').addEventListener('click', function() {
            setTimeout(function() {
                document.getElementById('novo_salario').focus();
                document.getElementById('novo_salario').select();
            }, 100);
        });