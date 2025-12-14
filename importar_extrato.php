<?php
// importar_extrato.php - Ferramenta de Importação de Extrato Bancário
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: indexv1.html');
    exit();
}

require_once __DIR__ . '/header_footer/header.php';


$user_id = $_SESSION['user_id'];
$mensagem = '';
$tipo_mensagem = '';
$dados_processados = array();

// MAPEAMENTO AUTOMÁTICO DE CATEGORIAS
$mapeamento_categorias = array(
    'supermercado' => 'Alimentação',
    'mercearia' => 'Alimentação',
    'pao' => 'Alimentação',
    'padaria' => 'Alimentação',
    'restaurante' => 'Alimentação',
    'cafe' => 'Alimentação',
    'pizza' => 'Alimentação',
    'burger' => 'Alimentação',
    'gasolina' => 'Transporte',
    'combustivel' => 'Transporte',
    'autocarro' => 'Transporte',
    'metro' => 'Transporte',
    'uber' => 'Transporte',
    'taxi' => 'Transporte',
    'comboio' => 'Transporte',
    'renda' => 'Habitação',
    'condominio' => 'Habitação',
    'imovel' => 'Habitação',
    'farmacia' => 'Saúde',
    'hospital' => 'Saúde',
    'medico' => 'Saúde',
    'clinica' => 'Saúde',
    'dentista' => 'Saúde',
    'medicamento' => 'Saúde',
    'escola' => 'Educação',
    'universidade' => 'Educação',
    'curso' => 'Educação',
    'livro' => 'Educação',
    'cinema' => 'Lazer',
    'teatro' => 'Lazer',
    'musica' => 'Lazer',
    'concerto' => 'Lazer',
    'jogo' => 'Lazer',
    'spotify' => 'Lazer',
    'netflix' => 'Lazer',
    'playstation' => 'Lazer',
    'agua' => 'Utilities',
    'luz' => 'Utilities',
    'electricidade' => 'Utilities',
    'eletricidade' => 'Utilities',
    'gas' => 'Utilities',
    'internet' => 'Utilities',
    'telefone' => 'Utilities',
    'tv' => 'Utilities',
    'seguro' => 'Seguros',
    'automovel' => 'Seguros',
    'saude' => 'Seguros',
    'vida' => 'Seguros',
    'banco' => 'Outro',
    'juros' => 'Outro',
    'comissao' => 'Outro',
    'taxa' => 'Outro'
);

// FUNÇÃO PARA DETECTAR CATEGORIA
function detectarCategoria($descricao)
{
    global $mapeamento_categorias;

    $descricao_lower = strtolower($descricao);

    foreach ($mapeamento_categorias as $palavra_chave => $categoria) {
        if (strpos($descricao_lower, $palavra_chave) !== false) {
            return $categoria;
        }
    }

    // Se não detectar, retorna Outro
    return 'Outro';
}

// PROCESSAR UPLOAD DO ARQUIVO CSV
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivo_csv'])) {
    $arquivo = $_FILES['arquivo_csv']['tmp_name'];
    $nome_arquivo = $_FILES['arquivo_csv']['name'];

    // Validar se é CSV
    if (!preg_match('/\.csv$/i', $nome_arquivo)) {
        $mensagem = '✗ Por favor, envie um ficheiro CSV.';
        $tipo_mensagem = 'error';
    } else if (!is_uploaded_file($arquivo)) {
        $mensagem = '✗ Erro no upload do ficheiro.';
        $tipo_mensagem = 'error';
    } else {
        // Abrir e processar CSV
        $handle = fopen($arquivo, 'r');

        if ($handle) {
            $linha_num = 0;

            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $linha_num++;

                // Ignorar primeira linha (cabeçalho)
                if ($linha_num == 1)
                    continue;

                // Validar formato (esperado: Data, Descrição, Valor)
                if (count($row) >= 3) {
                    $data = trim($row[0]);
                    $descricao = trim($row[1]);
                    $valor_str = trim($row[2]);

                    // Converter valor (remover símbolos de moeda)
                    $valor = floatval(preg_replace('/[^0-9.,]/', '', $valor_str));
                    $valor = abs($valor); // Garantir valor positivo

                    // Detectar categoria automaticamente
                    $categoria = detectarCategoria($descricao);

                    // Armazenar dados processados
                    if (!empty($descricao) && $valor > 0) {
                        $dados_processados[] = array(
                            'data' => $data,
                            'descricao' => $descricao,
                            'valor' => round($valor, 2),
                            'categoria' => $categoria,
                            'tipo' => 'Saída'
                        );
                    }
                }
            }

            fclose($handle);

            if (count($dados_processados) > 0) {
                $mensagem = '✓ Ficheiro carregado com sucesso! ' . count($dados_processados) . ' movimentos encontrados.';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = '✗ Nenhum movimento válido encontrado no ficheiro.';
                $tipo_mensagem = 'error';
            }
        } else {
            $mensagem = '✗ Erro ao ler o ficheiro.';
            $tipo_mensagem = 'error';
        }
    }
}

// IMPORTAR DADOS PROCESSADOS PARA A BD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'importar') {
    // Recuperar dados do formulário (enviados via JSON)
    $dados_json = $_POST['dados_json'] ?? '[]';
    $dados_importar = json_decode($dados_json, true);

    $total_importado = 0;
    $erros = 0;

    if (is_array($dados_importar)) {
        foreach ($dados_importar as $despesa) {
            if (!isset($despesa['categoria']) || !isset($despesa['valor']) || $despesa['valor'] <= 0) {
                $erros++;
                continue;
            }

            $categoria = trim($despesa['categoria']);
            $valor = floatval($despesa['valor']);
            $categoria = trim($despesa['categoria']);
            $valor_mensal = floatval($despesa['valor']);   // usa a mesma variável que vai pro bind
            $fixa_variavel = 'variavel';                   // ou 'fixa' conforme tua regra

            $sql = "INSERT INTO despesas (id_utilizador, categoria, valor_mensal, fixa_variavel)
            VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("isds", $user_id, $categoria, $valor_mensal, $fixa_variavel);
                //            i     s          d             s

                if ($stmt->execute())
                    $total_importado++;
                else
                    $erros++;

                $stmt->close();
            } else {
                $erros++;
            }

        }
    }

    if ($total_importado > 0) {
        $mensagem = "✓ Importação concluída! $total_importado despesas foram adicionadas com sucesso.";
        $tipo_mensagem = 'success';
        $dados_processados = array(); // Limpar dados
    } else {
        $mensagem = '✗ Erro ao importar despesas. Tente novamente.';
        $tipo_mensagem = 'error';
    }
}

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Extrato Bancário - FinanceControl</title>
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/importar_extrato.css">
</head>

<body>
    <?php require_once __DIR__ . '/header_footer/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-file-csv"></i> Importar Extrato Bancário</h2>
            <p class="page-subtitle">Carregue o extrato bancário em formato CSV e ele será automaticamente categorizado
            </p>
        </div>

        <!-- MENSAGENS DE FEEDBACK -->
        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                <i class="fas fa-<?php echo $tipo_mensagem == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- INFORMAÇÕES ÚTEIS -->
        <div class="info-box">
            <h4><i class="fas fa-info-circle"></i> Formato Esperado: <strong>CSV</h4>
            <p><strong>O sistema irá:</strong> Detectar automaticamente a categoria de cada movimento e mostrar uma
                previsão antes de importar.</p>
        </div>

        <!-- SECÇÃO DE UPLOAD -->
        <div class="upload-section">
            <h3><i class="fas fa-upload"></i> Carregar Ficheiro CSV</h3>

            <form method="POST" enctype="multipart/form-data" id="formUpload">
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Clique aqui ou arraste o ficheiro</strong></p>
                    <p style="font-size: 12px; color: #999;">Formatos aceitos: .csv</p>
                    <input type="file" id="fileInput" name="arquivo_csv" accept=".csv" style="display: none;">
                    <button type="button" class="btn btn-primary"
                        onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-browse"></i> Selecionar Ficheiro
                    </button>
                </div>
            </form>
        </div>

        <!-- PREVISÃO DOS DADOS -->
        <?php if (count($dados_processados) > 0): ?>
            <div class="preview-section">
                <h3><i class="fas fa-eye"></i> Previsão de Importação (<?php echo count($dados_processados); ?> movimentos)
                </h3>
                <p>Revise os dados abaixo. Pode alterar a categoria se necessário:</p>

                <form method="POST" id="formImportar">
                    <input type="hidden" name="acao" value="importar">
                    <input type="hidden" name="dados_json" id="dadosJson">

                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Valor (€)</th>
                                <th>Categoria Detectada</th>
                                <th>Alterar Categoria</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaPreview">
                            <?php foreach ($dados_processados as $idx => $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['data']); ?></td>
                                    <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                                    <td><?php echo number_format($item['valor'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="categoria-badge">
                                            <?php echo htmlspecialchars($item['categoria']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select class="categoria-select" data-index="<?php echo $idx; ?>">
                                            <option value="Alimentação" <?php echo $item['categoria'] == 'Alimentação' ? 'selected' : ''; ?>>Alimentação</option>
                                            <option value="Transporte" <?php echo $item['categoria'] == 'Transporte' ? 'selected' : ''; ?>>Transporte</option>
                                            <option value="Habitação" <?php echo $item['categoria'] == 'Habitação' ? 'selected' : ''; ?>>Habitação</option>
                                            <option value="Saúde" <?php echo $item['categoria'] == 'Saúde' ? 'selected' : ''; ?>>
                                                Saúde</option>
                                            <option value="Educação" <?php echo $item['categoria'] == 'Educação' ? 'selected' : ''; ?>>Educação</option>
                                            <option value="Lazer" <?php echo $item['categoria'] == 'Lazer' ? 'selected' : ''; ?>>
                                                Lazer</option>
                                            <option value="Utilities" <?php echo $item['categoria'] == 'Utilities' ? 'selected' : ''; ?>>Utilities</option>
                                            <option value="Seguros" <?php echo $item['categoria'] == 'Seguros' ? 'selected' : ''; ?>>Seguros</option>
                                            <option value="Poupança" <?php echo $item['categoria'] == 'Poupança' ? 'selected' : ''; ?>>Poupança</option>
                                            <option value="Investimentos" <?php echo $item['categoria'] == 'Investimentos' ? 'selected' : ''; ?>>Investimentos</option>
                                            <option value="Outro" <?php echo $item['categoria'] == 'Outro' ? 'selected' : ''; ?>>
                                                Outro</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <div class="form-actions" style="margin-top: 25px;">
                            <button type="button" class="btn btn-primary" id="btnImportar">
                                <i class="fas fa-check"></i> Importar Despesas
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnCancelar">
                                <i class="fas fa-redo"></i> Cancelar
                            </button>
                        </div>

                    </table>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <script src="js/importar_extrato.js"></script>
    <script src="js/header_footer.js"></script>
    <?php require_once __DIR__ . '/header_footer/footer.php'; ?>
</body>

</html>