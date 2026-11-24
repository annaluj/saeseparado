<?php
require_once "seguranca.php";
exigir_login();

require_once "conexao.php";
require_once "usuarios.php";

$usuario_id = $_SESSION['usuario_id'];

// 1. Busca os agendamentos do usuário logado
$resultado_agendamentos = executar($conn,
    "SELECT id, data_consulta, horario_consulta FROM agendamentos
      WHERE usuario_id = ?
      ORDER BY data_consulta ASC, horario_consulta ASC",
    "i", [$usuario_id]);

if (!$resultado_agendamentos) {
    die("Erro ao buscar agendamentos. Tente novamente mais tarde.");
}

// 2. Média das emoções do usuário
$emocoes = resumo_emocoes($conn, $usuario_id);

if (!$emocoes) {
    die("Erro ao buscar registros emocionais. Tente novamente mais tarde.");
}

// 3. Foto de perfil do usuário
$foto = foto_do_usuario($conn, $usuario_id);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Página Principal</title>
    <link rel="stylesheet" href="../FRONT-END/pagina-principal.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Federo&display=swap" rel="stylesheet">
</head>
<body>

<div class="fundo">

    <header class="inicio">
        <div class="boas-vindas">
            <h1>
                Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!
            </h1>
            <p>Seja bem-vindo(a) ao SAE (Serviço de Apoio Estudantil).</p>
        </div>


        <div class="foto-perfil">
            <form action="alterar_foto.php" method="POST" enctype="multipart/form-data">
                <?php echo csrf_campo(); ?>
                <label for="nova-foto" class="foto-clicavel" title="Clique para alterar sua foto">
                    <?php if (!empty($foto)): ?>
                        <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto do usuário">
                    <?php else: ?>
                        <div class="sem-foto">👤</div>
                    <?php endif; ?>
                </label>

                <input type="file" id="nova-foto" name="nova_foto" accept="image/*">
            </form>

            <form action="logout.php" method="POST">
                <?php echo csrf_campo(); ?>
                <button type="submit" class="sair">Sair da conta</button>
            </form>
        </div>
    </header>

    <main class="cards">

        <div class="card">
            <h2>Agendamento</h2>
            <p>Agende um atendimento com nossa equipe.</p>
            <button type="button" class="btn-color" data-href="agendar.php">Agendar atendimento</button>
        </div>

        <!-- MEUS AGENDAMENTOS -->
        <div class="card">
            <h2>Meus agendamentos</h2>

            <?php if (mysqli_num_rows($resultado_agendamentos) > 0): ?>
                <ul class="lista-agendamentos">
                    <?php while ($agendamento = mysqli_fetch_assoc($resultado_agendamentos)): ?>
                        <?php
                            $data_formatada = date('d/m/Y', strtotime($agendamento['data_consulta']));
                            $hora_formatada = date('H:i', strtotime($agendamento['horario_consulta']));
                        ?>
                        <li class="agendamento">

                            📅 <strong>Data:</strong> <?php echo $data_formatada; ?><br>

                            ⏰ <strong>Horário:</strong> <?php echo $hora_formatada; ?><br>

                            📌 <strong>Status:</strong> Aguardando confirmação

                            <form action="excluir_agendamento.php" method="POST" class="form-excluir-agendamento"
                                  data-confirmar="Tem certeza que deseja excluir este agendamento?">
                                <?php echo csrf_campo(); ?>
                                <input type="hidden" name="agendamento_id" value="<?php echo (int)$agendamento['id']; ?>">

                                <button type="submit" class="btn-excluir-agendamento">🗑️ Excluir agendamento</button>
                            </form>

                        </li>
                    <?php endwhile; ?>
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
