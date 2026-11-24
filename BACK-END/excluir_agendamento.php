<?php

require_once "seguranca.php";
exigir_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['agendamento_id'])) {
    header("Location: pagina-principal.php");
    exit();
}

if (!csrf_valido()) {
    die("Requisição inválida. Recarregue a página e tente novamente.");
}

require_once "conexao.php";

$resultado = executar($conn,
    "DELETE FROM agendamentos WHERE id = ? AND usuario_id = ?",
    "ii", [(int)$_POST['agendamento_id'], $_SESSION['usuario_id']]);

if (!$resultado) {
    die("Erro ao excluir agendamento. Tente novamente mais tarde.");
}

header("Location: pagina-principal.php");
exit();

?>
