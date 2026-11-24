<?php
// Administrador: muda o tipo de uma conta ou ativa/desativa a conta.

require_once "seguranca.php";
require_once "conexao.php";

exigir_tipo($conn, 'admin');

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header("Location: area-admin.php");
    exit();
}

$alvo_id = (int)($_POST['usuario_id'] ?? 0);
$acao = $_POST['acao'] ?? '';

// Evita que o administrador tire o próprio acesso sem querer
if ($alvo_id === (int)$_SESSION['usuario_id']) {
    definir_aviso("Você não pode alterar a sua própria conta por aqui.", true);
    header("Location: area-admin.php");
    exit();
}

$resultado = executar($conn, "SELECT nome, tipo FROM usuarios WHERE usuario_id = ?", "i", [$alvo_id]);
$alvo = $resultado ? mysqli_fetch_assoc($resultado) : null;

if (!$alvo) {
    definir_aviso("Usuário não encontrado.", true);
    header("Location: area-admin.php");
    exit();
}

$nomes_tipo = ['estudante' => 'estudante', 'profissional' => 'profissional', 'admin' => 'administrador'];

if ($acao === 'mudar_tipo') {
    $novo_tipo = $_POST['tipo'] ?? '';
    if (!isset($nomes_tipo[$novo_tipo])) {
        definir_aviso("Tipo de conta inválido.", true);
        header("Location: area-admin.php");
        exit();
    }
    $ok = executar($conn, "UPDATE usuarios SET tipo = ? WHERE usuario_id = ?", "si", [$novo_tipo, $alvo_id]);
    $mensagem = "{$alvo['nome']} agora é {$nomes_tipo[$novo_tipo]}.";
    $deixou_de_atender = $alvo['tipo'] === 'profissional' && $novo_tipo !== 'profissional';

} elseif ($acao === 'desativar' || $acao === 'ativar') {
    $ativo = $acao === 'ativar' ? 1 : 0;
    $ok = executar($conn, "UPDATE usuarios SET ativo = ? WHERE usuario_id = ?", "ii", [$ativo, $alvo_id]);
    $mensagem = $ativo ? "Conta de {$alvo['nome']} reativada." : "Conta de {$alvo['nome']} desativada. Ela não consegue mais entrar no site.";
    $deixou_de_atender = !$ativo && $alvo['tipo'] === 'profissional';

} else {
    definir_aviso("Ação inválida.", true);
    header("Location: area-admin.php");
    exit();
}

if (!$ok) {
    definir_aviso("Erro ao salvar. Tente novamente mais tarde.", true);
    header("Location: area-admin.php");
    exit();
}

// Se um profissional deixou de atender, as próximas consultas dele voltam a ser pedidos
// pendentes, para o administrador escolher outro profissional
if ($deixou_de_atender) {
    executar($conn,
        "UPDATE agendamentos SET status = 'pendente', profissional_id = NULL
          WHERE profissional_id = ? AND status = 'confirmado' AND data_consulta >= CURDATE()",
        "i", [$alvo_id]);
    $devolvidas = linhas_alteradas();
    if ($devolvidas > 0) {
        $mensagem .= " $devolvidas consulta(s) dele(a) voltaram para os pedidos pendentes.";
    }
}

definir_aviso($mensagem);
header("Location: area-admin.php");
exit();
?>
