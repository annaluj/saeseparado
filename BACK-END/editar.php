<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

include("conexao.php");

$usuario_id = $_SESSION['usuario_id'];

// Busca os dados cadastrados atualmente
$sql = "SELECT nome, email, rua, cep, numero_casa, bairro, cidade FROM usuarios WHERE usuario_id = '$usuario_id'";
$resultado = mysqli_query($conn, $sql);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario = mysqli_fetch_assoc($resultado);
} else {
    die("Erro ao carregar os dados do usuário.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../FRONT-END/cadastro.css">
</head>
<body>
    
    <!-- Envia para o processar_atualizacao.php -->
    <form action="processar_atualizacaoperfil.php" method="post" enctype="multipart/form-data" class="cadastro">
        <h2>Dados do usuário</h2>
        
        <div class="foto-usuario">
            <img id="imagem-preview" width="200px" src="#" alt="Prévia da foto" style="display: none;">
        </div>

        <!-- Preenchimento automático com value -->
        <input type="text" name="nome" placeholder="Nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>">
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($usuario['email']); ?>">

        <h2>Endereço</h2>

        <input type="text" name="rua" placeholder="Rua" value="<?php echo htmlspecialchars($usuario['rua']); ?>">
        <input type="text" name="cep" placeholder="CEP" value="<?php echo htmlspecialchars($usuario['cep']); ?>">
        <!-- name corrigido para numero_casa -->
        <input type="text" name="numero_casa" placeholder="Número" value="<?php echo htmlspecialchars($usuario['numero_casa']); ?>">
        <input type="text" name="bairro" placeholder="Bairro" value="<?php echo htmlspecialchars($usuario['bairro']); ?>">
        <input type="text" name="cidade" placeholder="Cidade" value="<?php echo htmlspecialchars($usuario['cidade']); ?>">

        <button type="submit">Salvar</button>
        <a href="perfil.php" class="link-voltar">
            <button type="button" class="btn-voltar">Voltar</button>
        </a>
    </form>

    <script src="../FRONT-END/pagina01.js"></script>
</body>
</html>