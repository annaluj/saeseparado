<?php
// Estudante cancela um agendamento seu que ainda está pendente ou confirmado.
// O agendamento não é apagado: fica como "cancelado" no histórico do profissional.

require_once "seguranca.php";
require_once "conexao.php";

exigir_tipo($conn, 'estudante');

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['agendamento_id'])) {
    header("Location: pagina-principal.php");
    exit();
}

if (!csrf_valido()) {
    die("Requisição inválida. Recarregue a página e tente novamente.");
}

$ok = executar($conn,
    "UPDATE agendamentos SET status = 'cancelado'
      WHERE id = ? AND usuario_id = ? AND status IN ('pendente', 'confirmado')",
    "ii", [(int)$_POST['agendamento_id'], $_SESSION['usuario_id']]);

if ($ok) {
    definir_aviso("Agendamento cancelado.");
} else {
    definir_aviso("Erro ao cancelar o agendamento. Tente novamente mais tarde.", true);
}

header("Location: pagina-principal.php");
exit();

?>
