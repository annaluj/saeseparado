<?php
require_once "seguranca.php";
exigir_login(true);

header('Content-Type: application/json; charset=UTF-8');

if (!csrf_valido()) {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida. Recarregue a página e tente novamente.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);

// Só aceita números inteiros de 1 a 5 ("5abc" ou 3.9 são recusados)
$emocao = filter_var($dados['emocao'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 5]
]);

if ($emocao === false) {
    echo json_encode(['success' => false, 'message' => 'Valor de emoção inválido (deve ser de 1 a 5).']);
    exit();
}

require_once "conexao.php";
exigir_tipo($conn, 'estudante'); // só estudantes agendam e registram emoções
require_once "usuarios.php";

$usuario_id = $_SESSION['usuario_id'];
$limite_diario = 5;

// "data_registro >= CURDATE()" permite ao banco usar índice (DATE(...) não permite)
$res_limite = executar($conn,
    "SELECT COUNT(*) AS total_hoje
       FROM registro_emocoes
      WHERE usuario_id = ?
        AND data_registro >= CURDATE()",
    "i", [$usuario_id]);

if (!$res_limite) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor. Tente novamente mais tarde.']);
    exit();
}

$total_hoje = (int)mysqli_fetch_assoc($res_limite)['total_hoje'];

if ($total_hoje >= $limite_diario) {
    echo json_encode([
        'success' => false,
        'message' => "Você já atingiu o limite máximo de $limite_diario registros de hoje! Volte amanhã."
    ]);
    exit();
}

if (!executar($conn, "INSERT INTO registro_emocoes (usuario_id, emocao) VALUES (?, ?)", "ii", [$usuario_id, $emocao])) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar. Tente novamente mais tarde.']);
    exit();
}

$restantes = $limite_diario - ($total_hoje + 1);

if ($restantes > 0) {
    $mensagem = "Emoção registrada com sucesso! Você ainda pode registrar mais $restantes vez(es) hoje.";
} else {
    $mensagem = "Emoção registrada! Você atingiu o limite de registros de hoje.";
}

// Devolve o resumo atualizado para a página mostrar sem recarregar
echo json_encode([
    'success' => true,
    'message' => $mensagem,
    'resumo'  => resumo_emocoes($conn, $usuario_id)
]);
?>
