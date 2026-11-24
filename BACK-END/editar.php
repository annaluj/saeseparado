<?php
require_once "seguranca.php";
exigir_login();

require_once "conexao.php";

// Busca os dados cadastrados atualmente
$resultado = executar($conn,
    "SELECT nome, email, rua, cep, numero_casa, bairro, cidade FROM usuarios WHERE usuario_id = ?",
    "i", [$_SESSION['usuario_id']]);

$usuario = $resultado ? mysqli_fetch_assoc($resultado) : null;

if (!$usuario) {
    die("Erro ao carregar os dados do usuário.");
}

// Atalho para escrever o valor de um campo com segurança
function valor($campo) {
    global $usuario;
    echo htmlspecialchars($usuario[$campo] ?? '');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../FRONT-END/base.css">
    <link rel="stylesheet" href="../FRONT-END/cadastro.css">
</head>
<body>

    <form action="processar_atualizacaoperfil.php" method="post" class="cadastro">
        <?php echo csrf_campo(); ?>
        <h2>Dados do usuário</h2>

        <!-- Preenchimento automático com value -->
        <input type="text" name="nome" placeholder="Nome" maxlength="100" required value="<?php valor('nome'); ?>">
        <input type="email" name="email" placeholder="Email" maxlength="255" required value="<?php valor('email'); ?>">

        <h2>Endereço</h2>

        <input type="text" name="rua" placeholder="Rua" maxlength="150" required value="<?php valor('rua'); ?>">
        <input type="text" name="cep" placeholder="CEP" maxlength="10" required value="<?php valor('cep'); ?>">
        <input type="text" name="numero_casa" placeholder="Número" maxlength="10" required value="<?php valor('numero_casa'); ?>">
        <input type="text" name="bairro" placeholder="Bairro" maxlength="100" required value="<?php valor('bairro'); ?>">
        <input type="text" name="cidade" placeholder="Cidade" maxlength="100" required value="<?php valor('cidade'); ?>">

        <button type="submit">Salvar</button>
        <button type="button" class="btn-voltar" data-href="perfil.php">Voltar</button>
    </form>

    <script src="../FRONT-END/comum.js"></script>
</body>
</html>
