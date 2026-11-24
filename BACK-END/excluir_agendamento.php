<?php

require_once "seguranca.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

include("conexao.php");

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['agendamento_id'])) {
    header("Location: pagina-principal.php");
    exit();
}

if (!csrf_valido()) {
    die("Requisição inválida. Recarregue a página e tente novamente.");
}

$agendamento_id = (int)$_POST['agendamento_id'];

$resultado = executar($conn,
    "DELETE FROM agendamentos WHERE id = ? AND usuario_id = ?",
    "ii", [$agendamento_id, $usuario_id]);

if (!$resultado) {
    die("Erro ao excluir agendamento. Tente novamente mais tarde.");
}

header("Location: pagina-principal.php");
exit();

?>
