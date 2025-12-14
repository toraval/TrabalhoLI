// importar_extrato.js

document.addEventListener('DOMContentLoaded', () => {
  // DRAG & DROP
  const uploadArea = document.getElementById('uploadArea');
  const fileInput  = document.getElementById('fileInput');
  const formUpload = document.getElementById('formUpload');
  const btnCancelar = document.getElementById('btnCancelar');
  const btnImportar = document.getElementById('btnImportar');

  if (uploadArea && fileInput && formUpload) {
    uploadArea.addEventListener('click', () => fileInput.click());

    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.style.background = '#e3f2fd';
    });

    uploadArea.addEventListener('dragleave', () => {
      uploadArea.style.background = '';
    });

    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      fileInput.files = e.dataTransfer.files;
      formUpload.submit();
    });

    // Submete automaticamente ao selecionar ficheiro pelo diálogo
    fileInput.addEventListener('change', () => {
      if (fileInput.files && fileInput.files.length > 0) {
        formUpload.submit();
      }
    });
  }

  // PREPARAR DADOS PARA IMPORTAÇÃO
  function prepararImportacao() {
    const dados  = [];
    const tabela = document.getElementById('tabelaPreview');

    if (!tabela) return;

    const linhas = tabela.querySelectorAll('tr');

    linhas.forEach((linha) => {
      const cells = linha.querySelectorAll('td');
      if (cells.length > 0) {
        const select = linha.querySelector('.categoria-select');

        dados.push({
          data: cells[0].textContent.trim(),
          descricao: cells[1].textContent.trim(),
          valor: parseFloat(
            cells[2].textContent.replace(/\./g, '').replace(',', '.')
          ),
          categoria: select ? select.value : cells[3].textContent.trim(),
          tipo: 'Saída'
        });
      }
    });

    const hidden = document.getElementById('dadosJson');
    if (hidden) {
      hidden.value = JSON.stringify(dados);
    }
  }

  function prepararEEnviarImportacao() {
    prepararImportacao();
    const formImportar = document.getElementById('formImportar');
    if (formImportar) {
      formImportar.submit();
    }
  }

  // Botão Importar
  if (btnImportar) {
    btnImportar.addEventListener('click', prepararEEnviarImportacao);
  }

  // Botão Cancelar
  if (btnCancelar) {
    btnCancelar.addEventListener('click', () => {
      window.location.href = 'importar_extrato.php';
    });
  }
});
