<?php
require_once "seguranca.php";
require_once "conexao.php";
require_once "usuarios.php";
require_once "agendamentos.php";

// Profissionais e administradores são levados para a área deles
exigir_tipo($conn, 'estudante');

$usuario_id = $_SESSION['usuario_id'];

// 1. Próximos agendamentos do estudante (os cancelados por ele não aparecem)
$agendamentos = buscar_agendamentos($conn,
    "a.usuario_id = ? AND a.status IN ('pendente', 'confirmado', 'recusado') AND a.data_consulta >= CURDATE()",
    "i", [$usuario_id]);

// 2. Média das emoções do usuário
$emocoes = resumo_emocoes($conn, $usuario_id);

if (!$emocoes) {
    die("Erro ao buscar registros emocionais. Tente novamente mais tarde.");
}

// 3. Dados do cabeçalho
$foto = foto_do_usuario($conn, $usuario_id);
$subtitulo = "Seja bem-vindo(a) ao SAE (Serviço de Apoio Estudantil).";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Página Principal</title>
    <link rel="stylesheet" href="../FRONT-END/pagina-principal.css">
    <link rel="stylesheet" href="../FRONT-END/areas.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Federo&display=swap" rel="stylesheet">
</head>
<body>

<div class="fundo">

    <?php include "cabecalho.php"; ?>

    <main class="cards">

        <div class="card">
            <h2>Agendamento</h2>
            <p>Agende um atendimento com nossa equipe.</p>
            <button type="button" class="btn-color" data-href="agendar.php">Agendar atendimento</button>
        </div>

        <!-- MEUS AGENDAMENTOS -->
        <div class="card">
            <h2>Meus agendamentos</h2>

            <?php if ($agendamentos): ?>
                <ul class="lista-agendamentos">
                    <?php foreach ($agendamentos as $agendamento): ?>
                        <li class="agendamento">

                            📅 <strong>Data:</strong> <?php echo formatar_data($agendamento['data_consulta']); ?><br>

                            ⏰ <strong>Horário:</strong> <?php echo formatar_hora($agendamento['horario_consulta']); ?><br>

                            📌 <strong>Status:</strong> <?php echo etiqueta_status($agendamento['status']); ?>

                            <?php if ($agendamento['status'] === 'confirmado' && $agendamento['profissional_nome']): ?>
                                <br>🧑‍⚕️ <strong>Profissional:</strong> <?php echo htmlspecialchars($agendamento['profissional_nome']); ?>
                            <?php endif; ?>

                            <?php if (in_array($agendamento['status'], ['pendente', 'confirmado'], true)): ?>
                                <form action="cancelar_agendamento.php" method="POST" class="form-excluir-agendamento"
                                      data-confirmar="Tem certeza que deseja cancelar este agendamento?">
                                    <?php echo csrf_campo(); ?>
                                    <input type="hidden" name="agendamento_id" value="<?php echo (int)$agendamento['id']; ?>">

                                    <button type="submit" class="btn-perigo">🗑️ Cancelar agendamento</button>
                                </form>
                            <?php endif; ?>

                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Você ainda não possui agendamentos marcados.</p>
            <?php endif; ?>
        </div>

        <!-- RESUMO EMOCIONAL -->
        <div class="card">
            <h2>Como você está se sentindo?</h2>

            <p class="media-emocoes">Média geral: <strong><span id="media-emocoes"><?php echo $emocoes['media']; ?></span> / 5.0</strong></p>
            <p>Estado: <strong id="status-emocoes"><?php echo $emocoes['status']; ?></strong></p>
            <small class="total-registros">Total de registros: <span id="total-emocoes"><?php echo $emocoes['quantidade']; ?></span></small>

            <div class="emojis">
                <button type="button" data-valor="5" class="emoji">😁</button>
                <button type="button" data-valor="4" class="emoji">🙂</button>
                <button type="button" data-valor="3" class="emoji">😐</button>
                <button type="button" data-valor="2" class="emoji">😔</button>
                <button type="button" data-valor="1" class="emoji">😢</button>
            </div>

            <p id="mensagem"></p>
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
