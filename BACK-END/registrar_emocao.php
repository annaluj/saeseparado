<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include("conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit();
}

$raw_input = file_get_contents('php://input');
$dados = json_decode($raw_input, true);

$emocao = null;
if (isset($dados['emocao'])) {
    $emocao = $dados['emocao'];
} elseif (isset($_POST['emocao'])) {
    $emocao = $_POST['emocao'];
}

if ($emocao !== null && $emocao !== '') {
    $usuario_id = $_SESSION['usuario_id'];
    $emocao_valor = (int)$emocao;

    if ($emocao_valor >= 1 && $emocao_valor <= 5) {

        $sql_limite = "SELECT COUNT(*) AS total_hoje 
                       FROM registro_emocoes 
                       WHERE usuario_id = '$usuario_id' 
                         AND DATE(data_registro) = CURDATE()";
        
        $res_limite = mysqli_query($conn, $sql_limite);
        $dados_limite = mysqli_fetch_assoc($res_limite);
        $total_hoje = (int)$dados_limite['total_hoje'];

        if ($total_hoje >= 5) {
            echo json_encode([
                'success' => false, 
                'message' => 'Você já atingiu o limite máximo de 5 registros de hoje! Volte amanhã.'
            ]);
            exit();
        }

        $sql = "INSERT INTO registro_emocoes (usuario_id, emocao) VALUES ('$usuario_id', '$emocao_valor')";

        if (mysqli_query($conn, $sql)) {
            $restantes = 5 - ($total_hoje + 1);
            
            if ($restantes > 0) {
                $mensagem_sucesso = "Emoção registrada com sucesso! Você ainda pode registrar mais $restantes vez(es) hoje.";
            } else {
                $mensagem_sucesso = "Emoção registrada! Você atingiu o limite de registros de hoje.";
            }

            echo json_encode([
                'success' => true, 
                'message' => $mensagem_sucesso
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco: ' . mysqli_error($conn)]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Valor de emoção inválido (deve ser de 1 a 5).']);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Nenhum dado enviado.',
        'debug' => $raw_input
    ]);
}
?>