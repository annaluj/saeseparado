<?php
header("Content-Type: application/json; charset=UTF-8");

session_start();
include("conexao.php");


if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "erro", "mensagem" => "Sessão expirada. Faça login novamente."]);
    exit();
}

$json_recebido = file_get_contents("php://input");
$dados = json_decode($json_recebido, true);

$usuario_id = $_SESSION['usuario_id'];
$data = isset($dados['data']) ? mysqli_real_escape_string($conn, $dados['data']) : '';
$hora = isset($dados['hora']) ? mysqli_real_escape_string($conn, $dados['hora']) : '';

if (empty($data) || empty($hora)) {
    echo json_encode(["status" => "erro", "mensagem" => "Por favor, preencha a data e o horário."]);
    exit();
}

$hoje = date('Y-m-d');
if ($data < $hoje) {
    echo json_encode(["status" => "erro", "mensagem" => "Não é possível agendar uma consulta em uma data passada."]);
    exit();
}

$sql = "INSERT INTO agendamentos (usuario_id, data_consulta, horario_consulta) VALUES ('$usuario_id', '$data', '$hora')";

if (mysqli_query($conn, $sql)) {
    echo json_encode([
        "status" => "sucesso", 
        "mensagem" => "Agendamento realizado com sucesso! Aguarde nossa confirmação."
    ]);
} else {
    echo json_encode([
        "status" => "erro", 
        "mensagem" => "Erro ao salvar no banco de dados: " . mysqli_error($conn)
    ]);
}
?>
