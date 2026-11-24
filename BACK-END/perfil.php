<?php
require_once "seguranca.php";
exigir_login();

require_once "conexao.php";

$resultado = executar($conn,
    "SELECT nome, email, rua, cep, numero_casa, bairro, cidade FROM usuarios WHERE usuario_id = ?",
    "i", [$_SESSION['usuario_id']]);

$dados = $resultado ? mysqli_fetch_assoc($resultado) : null;

if ($dados) {
    // "?:" também troca texto vazio por "Não informado" ("??" só trocava null)
    $nome   = $dados['nome'] ?: "Não informado";
    $email  = $dados['email'] ?: "Não informado";
    $rua    = $dados['rua'] ?: "Não informado";
    $cep    = $dados['cep'] ?: "Não informado";
    $numero = $dados['numero_casa'] ?: "Não informado";
    $bairro = $dados['bairro'] ?: "Não informado";
    $cidade = $dados['cidade'] ?: "Não informado";
} else {
    $nome = $email = $rua = $cep = $numero = $bairro = $cidade = "Erro ao carregar dados";
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../FRONT-END/base.css">
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
       <form class="excluir-conta" action="excluir_conta.php" method="POST"
             data-confirmar="Tem certeza que deseja excluir sua conta? Essa ação não pode ser desfeita.">
            <?php echo csrf_campo(); ?>
            <button type="submit">Excluir Conta</button>
       </form>

    </main>

    <script src="../FRONT-END/comum.js"></script>
</body>
</html>
