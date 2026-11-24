<?php

require_once "seguranca.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../FRONT-END/cadastro.html");
    exit();
}

require_once "conexao.php";
require_once "usuarios.php";

$dados = campos_do_formulario();
$senha = $_POST['senha'] ?? '';

$erro = validar_dados_usuario($dados);
if ($erro) {
    die($erro);
}

// O bcrypt só usa os primeiros 72 bytes da senha
if (strlen($senha) < 8 || strlen($senha) > 72) {
    die("A senha deve ter entre 8 e 72 caracteres.");
}

// Esta mensagem revela que o e-mail já tem conta. Sem confirmação por e-mail
// não há outro jeito de avisar a pessoa, então aceitamos esse risco.
if (email_em_uso($conn, $dados['email'])) {
    die("Este e-mail já está cadastrado! Tente outro.");
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// FOTO
$caminho_foto = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $foto = salvar_foto($_FILES['foto']);

    if (isset($foto['erro'])) {
        die($foto['erro']);
    }

    $caminho_foto = $foto['caminho'];
}

// Cadastra o usuário
$sql_inserir = "INSERT INTO usuarios
(nome, email, senha_hash, rua, cep, numero_casa, bairro, cidade, foto)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$inserido = executar($conn, $sql_inserir, "sssssssss", [
    $dados['nome'], $dados['email'], $senha_hash, $dados['rua'], $dados['cep'],
    $dados['numero_casa'], $dados['bairro'], $dados['cidade'], $caminho_foto
]);

if ($inserido) {

    echo "<h3>Cadastro realizado com sucesso!</h3>";
    echo "<a href='../FRONT-END/login.html'>Clique aqui para fazer login</a>";

} else {

    // Se não salvou o usuário, apaga a foto que já tinha sido enviada
    apagar_foto($caminho_foto);
    echo "Erro ao realizar o cadastro. Tente novamente mais tarde.";
}

?>
