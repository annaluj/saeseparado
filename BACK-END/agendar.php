<?php
require_once "seguranca.php";
require_once "conexao.php";
exigir_tipo($conn, 'estudante');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Agendar Consulta - SAE</title>
    <link rel="stylesheet" href="../FRONT-END/agendar.css">
</head>
<body>

    <form id="formConsulta" class="consulta">
    <h1>Seja Bem-vindo à Central de Agendamento</h1>

    <input type="text" id="nome" class="inputs" value="<?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>" required readonly><br>

    <label for="data">Escolha a melhor data</label>
    <input type="date" id="data" class="inputs" min="<?php echo date('Y-m-d'); ?>" required><br>

    <label for="hora">Escolha a hora que deseja a sua consulta:</label>
    <input type="time" id="hora" class="inputs" required> <br>

    <a href="pagina-principal.php" class="btn-voltar">← Voltar</a>

<button type="submit">Agendar Consulta</button>
</form>

    <script src="../FRONT-END/comum.js"></script>
    <script src="../FRONT-END/agendar.js"></script>
</body>
</html>
