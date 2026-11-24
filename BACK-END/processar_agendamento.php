<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "seguranca.php";
include("conexao.php");


if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "erro", "mensagem" => "Sessão expirada. Faça login novamente."]);
    exit();
}

if (!csrf_valido()) {
    echo json_encode(["status" => "erro", "mensagem" => "Requisição inválida. Recarregue a página e tente novamente."]);
    exit();
}

$json_recebido = file_get_contents("php://input");
$dados = json_decode($json_recebido, true);

$usuario_id = $_SESSION['usuario_id'];
$data = isset($dados['data']) && is_string($dados['data']) ? $dados['data'] : '';
$hora = isset($dados['hora']) && is_string($dados['hora']) ? $dados['hora'] : '';

if (empty($data) || empty($hora)) {
    echo json_encode(["status" => "erro", "mensagem" => "Por favor, preencha a data e o horário."]);
    exit();
}

// Confere se data e hora estão no formato certo (ex.: 2026-11-24 e 14:30)
$data_hora = DateTime::createFromFormat('Y-m-d H:i', $data . ' ' . $hora);
if (!$data_hora || $data_hora->format('Y-m-d H:i') !== $data . ' ' . $hora) {
    echo json_encode(["status" => "erro", "mensagem" => "Data ou horário inválido."]);
    exit();
}

if ($data_hora < new DateTime()) {
    echo json_encode(["status" => "erro", "mensagem" => "Não é possível agendar uma consulta em uma data ou horário que já passou."]);
    exit();
}

$sql = "INSERT INTO agendamentos (usuario_id, data_consulta, horario_consulta) VALUES (?, ?, ?)";

if (executar($conn, $sql, "iss", [$usuario_id, $data, $hora])) {
    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "Agendamento realizado com sucesso! Aguarde nossa confirmação."
    ]);
} else {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro ao salvar o agendamento. Tente novamente mais tarde."
    ]);
}
?>
