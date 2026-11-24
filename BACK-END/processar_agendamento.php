<?php
require_once "seguranca.php";
exigir_login(true);

header("Content-Type: application/json; charset=UTF-8");

if (!csrf_valido()) {
    echo json_encode(["success" => false, "message" => "Requisição inválida. Recarregue a página e tente novamente."]);
    exit();
}

$dados = json_decode(file_get_contents("php://input"), true);

$data = isset($dados['data']) && is_string($dados['data']) ? $dados['data'] : '';
$hora = isset($dados['hora']) && is_string($dados['hora']) ? $dados['hora'] : '';

if ($data === '' || $hora === '') {
    echo json_encode(["success" => false, "message" => "Por favor, preencha a data e o horário."]);
    exit();
}

// Confere se data e hora estão no formato certo (ex.: 2026-11-24 e 14:30)
$data_hora = DateTime::createFromFormat('Y-m-d H:i', $data . ' ' . $hora);
if (!$data_hora || $data_hora->format('Y-m-d H:i') !== $data . ' ' . $hora) {
    echo json_encode(["success" => false, "message" => "Data ou horário inválido."]);
    exit();
}

if ($data_hora < new DateTime()) {
    echo json_encode(["success" => false, "message" => "Não é possível agendar uma consulta em uma data ou horário que já passou."]);
    exit();
}

require_once "conexao.php";
exigir_tipo($conn, 'estudante'); // só estudantes agendam e registram emoções

$sql = "INSERT INTO agendamentos (usuario_id, data_consulta, horario_consulta) VALUES (?, ?, ?)";

if (executar($conn, $sql, "iss", [$_SESSION['usuario_id'], $data, $hora])) {
    echo json_encode([
        "success" => true,
        "message" => "Agendamento realizado com sucesso! Aguarde nossa confirmação."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao salvar o agendamento. Tente novamente mais tarde."
    ]);
}
?>
