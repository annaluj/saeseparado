<?php
require_once "seguranca.php";
require_once "conexao.php";
require_once "usuarios.php";
require_once "agendamentos.php";

exigir_tipo($conn, 'profissional');

$usuario_id = $_SESSION['usuario_id'];

// Pedidos que ainda ninguém confirmou
$pendentes = buscar_agendamentos($conn,
    "a.status = 'pendente' AND a.data_consulta >= CURDATE()");

// Consultas que este profissional vai atender
$proximas = buscar_agendamentos($conn,
    "a.status = 'confirmado' AND a.profissional_id = ? AND a.data_consulta >= CURDATE()",
    "i", [$usuario_id]);

// Últimas consultas que já passaram ou foram encerradas
$historico = buscar_agendamentos($conn,
    "a.profissional_id = ? AND (a.status IN ('concluido', 'cancelado', 'recusado') OR a.data_consulta < CURDATE())",
    "i", [$usuario_id], true, 20);

$foto = foto_do_usuario($conn, $usuario_id);
$subtitulo = "Área do profissional: confirme os pedidos e acompanhe suas consultas.";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Profissional - SAE</title>
    <link rel="stylesheet" href="../FRONT-END/pagina-principal.css">
    <link rel="stylesheet" href="../FRONT-END/areas.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Federo&display=swap" rel="stylesheet">
</head>
<body class="area-trabalho">

<div class="fundo">

    <?php include "cabecalho.php"; ?>

    <main class="cards">

        <!-- PEDIDOS PENDENTES -->
        <div class="card">
            <h2>Pedidos de agendamento (<?php echo count($pendentes); ?>)</h2>
            <p>Ao confirmar, a consulta passa a ser sua.</p>

            <?php if ($pendentes): ?>
                <ul class="lista-agendamentos">
                    <?php foreach ($pendentes as $a): ?>
                        <li class="agendamento">
                            👤 <strong>Estudante:</strong> <?php echo htmlspecialchars($a['estudante_nome']); ?><br>
                            📅 <strong>Data:</strong> <?php echo formatar_data($a['data_consulta']); ?>
                            às <?php echo formatar_hora($a['horario_consulta']); ?><br>
                            💙 <strong>Bem-estar:</strong> <?php echo bem_estar($a); ?>

                            <div class="acoes-agendamento">
                                <?php botao_acao($a['id'], 'confirmar', '✅ Confirmar', 'btn-color'); ?>
                                <?php botao_acao($a['id'], 'recusar', 'Recusar', 'btn-perigo', 'Recusar este pedido de agendamento?'); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="vazio">Nenhum pedido aguardando confirmação. 🎉</p>
            <?php endif; ?>
        </div>

        <!-- MINHAS PRÓXIMAS CONSULTAS -->
        <div class="card">
            <h2>Minhas próximas consultas (<?php echo count($proximas); ?>)</h2>

            <?php if ($proximas): ?>
                <ul class="lista-agendamentos">
                    <?php foreach ($proximas as $a): ?>
                        <li class="agendamento">
                            👤 <strong>Estudante:</strong> <?php echo htmlspecialchars($a['estudante_nome']); ?><br>
                            ✉️ <strong>Contato:</strong> <?php echo htmlspecialchars($a['estudante_email']); ?><br>
                            📅 <strong>Data:</strong> <?php echo formatar_data($a['data_consulta']); ?>
                            às <?php echo formatar_hora($a['horario_consulta']); ?><br>
                            💙 <strong>Bem-estar:</strong> <?php echo bem_estar($a); ?>

                            <div class="acoes-agendamento">
                                <?php botao_acao($a['id'], 'concluir', '✔️ Marcar como concluída', 'btn-color'); ?>
                                <?php botao_acao($a['id'], 'cancelar', 'Cancelar consulta', 'btn-perigo', 'Cancelar esta consulta? O estudante verá que ela foi cancelada.'); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="vazio">Você não tem consultas marcadas.</p>
            <?php endif; ?>
        </div>

        <!-- HISTÓRICO -->
        <div class="card">
            <h2>Histórico</h2>
            <p>Suas últimas 20 consultas encerradas.</p>

            <?php if ($historico): ?>
                <ul class="lista-agendamentos">
                    <?php foreach ($historico as $a): ?>
                        <li class="agendamento agendamento-encerrado">
                            <?php echo formatar_data($a['data_consulta']); ?> às <?php echo formatar_hora($a['horario_consulta']); ?>
                            · <?php echo htmlspecialchars($a['estudante_nome']); ?>
                            <?php echo etiqueta_status($a['status']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="vazio">Nenhuma consulta no histórico ainda.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Meu perfil</h2>
            <p>Consulte e altere seus dados pessoais.</p>
            <button type="button" class="btn-color" data-href="perfil.php">Ver meu perfil</button>
        </div>

    </main>

</div>

<script src="../FRONT-END/comum.js"></script>
<script src="../FRONT-END/pagina01.js"></script>

</body>
</html>
