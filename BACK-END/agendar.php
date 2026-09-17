<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <input type="time" id="hora" required> <br>

    <a href="pagina-principal.php" class="btn-voltar">← Voltar</a>

<button type="submit">Agendar Consulta</button>
    

    <p id="mensagemConsulta" style="margin-top: 15px; font-weight: bold; color: green;"></p>
    <p id="aviso" style="font-size: 0.85rem; color: #555;"></p>
</form>

    <script src="../FRONT-END/agendar.js"></script>
</body>
</html>
