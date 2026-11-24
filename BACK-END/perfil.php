<?php
require_once "seguranca.php"; // Ativa a sessão com as configurações de segurança

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

include("conexao.php"); 

$id_usuario = $_SESSION['usuario_id']; 

$resultado = executar($conn, "SELECT * FROM usuarios WHERE usuario_id = ?", "i", [$id_usuario]);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $dados = mysqli_fetch_assoc($resultado);
    
    $nome   = $dados['nome'] ?? "Não informado";
    $email  = $dados['email'] ?? "Não informado";
    $rua    = $dados['rua'] ?? "Não informado";
    $cep    = $dados['cep'] ?? "Não informado";
    $numero = $dados['numero_casa'] ?? "Não informado";
    $bairro = $dados['bairro'] ?? "Não informado";
    $cidade = $dados['cidade'] ?? "Não informado";
} else {
    $nome = $email = $rua = $cep = $numero = $bairro = $cidade = "Erro ao carregar dados";
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="../FRONT-END/perfil.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Federo&family=Neuton:wght@300;400;700&display=swap" rel="stylesheet">
    
    <title>Perfil do Usuário</title>
</head>
<body>

    <main class="fundo">
        
        <section class="usuario">
                <span class="rotulo">Nome:</span>
                <p class="valor" id="nome"><?php echo htmlspecialchars($nome); ?></p>
                <span class="rotulo">E-mail:</span>
                <p class="valor"><?php echo htmlspecialchars($email); ?></p>
        </section>

        <section class="endereco">
            <h2>Endereço</h2>
            <p><strong>Rua:</strong> <?php echo htmlspecialchars($rua); ?></p>
            <p><strong>Número:</strong> <?php echo htmlspecialchars($numero); ?></p>
            <p><strong>Bairro:</strong> <?php echo htmlspecialchars($bairro); ?></p>
            <p><strong>Cidade:</strong> <?php echo htmlspecialchars($cidade); ?></p>
            <p><strong>CEP:</strong> <?php echo htmlspecialchars($cep); ?></p>
        </section>

        <div class="acoes">
        <a href="pagina-principal.php">Voltar</a><br>
        <a href="editar.php" class="btn-editar">Editar Informações</a>
        </div>

       <!-- A exclusão é enviada por POST com token (um link simples poderia ser usado por outro site para apagar a conta) -->
       <form id="form-excluir-conta" action="excluir_conta.php" method="POST" style="display: none;">
            <?php echo csrf_campo(); ?>
       </form>
       <div class="excluir-conta"> <a href="excluir_conta.php" onclick="if (confirm('Tem certeza que deseja excluir sua conta? Essa ação não pode ser desfeita.')) { document.getElementById('form-excluir-conta').submit(); } return false;"> Excluir Conta </a> </div>

    </main>

</body>
</html>