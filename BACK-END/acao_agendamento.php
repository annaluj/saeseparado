<?php
// Confirmar, recusar, concluir, cancelar ou atribuir um agendamento.
// Usado pela área do profissional e pela área do administrador.

require_once "seguranca.php";
require_once "conexao.php";
require_once "agendamentos.php";

$tipo = exigir_tipo($conn, 'profissional', 'admin');
$voltar = pagina_inicial($tipo);

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header("Location: $voltar");
    exit();
}

$mensagens_sucesso = [
    'confirmar' => "Consulta confirmada! Ela agora aparece em \"Minhas próximas consultas\".",
    'atribuir'  => "Consulta confirmada com o profissional escolhido.",
    'recusar'   => "Pedido recusado.",
    'concluir'  => "Consulta marcada como concluída.",
    'cancelar'  => "Consulta cancelada.",
];

$acao = $_POST['acao'] ?? '';

$erro = mudar_agendamento(
    $conn,
    $tipo,
    $_SESSION['usuario_id'],
    (int)($_POST['agendamento_id'] ?? 0),
    $acao,
    (int)($_POST['profissional_id'] ?? 0)
);

if ($erro) {
    definir_aviso($erro, true);
} else {
    definir_aviso($mensagens_sucesso[$acao]);
}

header("Location: $voltar");
exit();
?>
