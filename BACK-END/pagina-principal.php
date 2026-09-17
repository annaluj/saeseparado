<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

include("conexao.php");

$usuario_id = $_SESSION['usuario_id'];

// 1. Busca os agendamentos do usuário logado
$sql_agendamentos = "SELECT * FROM agendamentos 
                     WHERE usuario_id = '$usuario_id' 
                     ORDER BY data_consulta ASC, horario_consulta ASC";

$resultado_agendamentos = mysqli_query($conn, $sql_agendamentos);

if (!$resultado_agendamentos) {
    die("Erro ao buscar agendamentos: " . mysqli_error($conn));
}

// 2. Busca e calcula a média das emoções do usuário
$sql_emocoes = "SELECT AVG(emocao) AS media, COUNT(*) AS quantidade 
                FROM registro_emocoes 
                WHERE usuario_id = '$usuario_id'";

$resultado_emocoes = mysqli_query($conn, $sql_emocoes);

if (!$resultado_emocoes) {
    die("Erro ao buscar registros emocionais: " . mysqli_error($conn));
}

$dados_emocoes = mysqli_fetch_assoc($resultado_emocoes);

$media = $dados_emocoes['media'];
$quantidade = (int)$dados_emocoes['quantidade'];

if ($quantidade > 0) {
    $media_formatada = number_format($media, 1, ',', '.');

    if ($media >= 4.0) {
        $status = "Excelente! 😁";
    } elseif ($media >= 3.0) {
        $status = "Estável 🙂";
    } elseif ($media >= 2.0) {
        $status = "Atenção 😐";
    } else {
        $status = "Precisa de apoio 😔";
    }
} else {
    $media_formatada = "N/A";
    $status = "Nenhum registro ainda";
}

// 3. Busca a foto de perfil do usuário
$sql_foto = "SELECT foto FROM usuarios WHERE usuario_id = '$usuario_id'";
$resultado_foto = mysqli_query($conn, $sql_foto);

if (!$resultado_foto) {
    die("Erro ao buscar a foto: " . mysqli_error($conn));
}

$dados_usuario = mysqli_fetch_assoc($resultado_foto);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link rel="stylesheet" href="../FRONT-END/pagina-principal.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Federo&family=Neuton:wght@300;400;700&display=swap" rel="stylesheet">
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
                <label for="nova-foto" class="foto-clicavel" title="Clique para alterar sua foto">
                    <?php if (!empty($dados_usuario['foto'])): ?>
                        <img src="<?php echo htmlspecialchars($dados_usuario['foto']); ?>" alt="Foto do usuário">
                    <?php else: ?>
                        <div class="sem-foto">👤</div>
                    <?php endif; ?>
                </label>

                <input type="file" id="nova-foto" name="nova_foto" accept="image/*" onchange="this.form.submit()" style="display: none;">
            </form>

            <a href="logout.php">
                <button type="button" class="sair">Sair da conta</button>
            </a>
    </header>

    <main class="cards">
 
        <div class="card">
            <h2>Agendamento</h2>
            <p>Agende um atendimento com nossa equipe.</p>
            <a href="agendar.php">
                <button type="button" class="btn-color">Agendar atendimento</button>
            </a>
        </div>

        <!-- MEUS AGENDAMENTOS -->
        <div class="card">
            <h2>Meus agendamentos</h2>

            <?php if (mysqli_num_rows($resultado_agendamentos) > 0): ?>
                <ul style="list-style: none; padding: 0; text-align: left;">
                    <?php while ($agendamento = mysqli_fetch_assoc($resultado_agendamentos)): ?>
                        <?php 
                            $data_formatada = date('d/m/Y', strtotime($agendamento['data_consulta']));
                            $hora_formatada = date('H:i', strtotime($agendamento['horario_consulta']));
                        ?>
                       <li style="margin-bottom: 10px; padding: 8px; background-color: #f0f4f8; border-radius: 6px; border-left: 4px solid #0056b3;">

    📅 <strong>Data:</strong> <?php echo $data_formatada; ?><br>

    ⏰ <strong>Horário:</strong> <?php echo $hora_formatada; ?><br>

    📌 <strong>Status:</strong> Aguardando confirmação

    <form action="excluir_agendamento.php" method="POST"
          onsubmit="return confirm('Tem certeza que deseja excluir este agendamento?');"
          style="margin-top: 8px;">

        <input type="hidden" name="agendamento_id"
               value="<?php echo $agendamento['id']; ?>">

        <button type="submit"
                style="background-color: #dc3545; color: white; border: none;
                       padding: 6px 10px; border-radius: 5px; cursor: pointer;">
            🗑️ Excluir agendamento
        </button>

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
            
            <p style="margin-bottom: 5px;">Média geral: <strong><?php echo $media_formatada; ?> / 5.0</strong></p>
            <p>Estado: <strong><?php echo $status; ?></strong></p>
            <small style="color: #666;">Total de registros: <?php echo $quantidade; ?></small>

            <div class="emojis">
                <button type="button" data-valor="5" class="emoji">😁</button>
                <button type="button" data-valor="4" class="emoji">🙂</button>
                <button type="button" data-valor="3" class="emoji">😐</button>
                <button type="button" data-valor="2" class="emoji">😔</button>
                <button type="button" data-valor="1" class="emoji">😢</button>
            </div>

            <p id="mensagem" style="margin-top: 10px; font-weight: bold;"></p>
        </div>

        <div class="card">
    <h2>Meu perfil</h2>

    <p>Consulte e altere seus dados pessoais.</p>

    <a href="perfil.php">
        <button type="button" class="btn-color">Ver meu perfil</button>
    </a>

   
</div>

    </main>

</div>

<script src="../FRONT-END/pagina01.js"></script>

</body>
</html>