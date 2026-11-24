<?php

require_once "seguranca.php";
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome   = trim($_POST['nome'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $senha  = $_POST['senha'] ?? '';

    $rua    = trim($_POST['rua'] ?? '');
    $cep    = trim($_POST['cep'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');

    if (empty($nome) || empty($email) || empty($senha)) {
        die("Por favor, preencha todos os campos obrigatórios.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Digite um e-mail válido.");
    }

    if (strlen($senha) < 8) {
        die("A senha deve ter pelo menos 8 caracteres.");
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Verifica se o e-mail já existe
    $resultado_verificar = executar($conn, "SELECT usuario_id FROM usuarios WHERE email = ?", "s", [$email]);

    if (!$resultado_verificar) {
        die("Erro no servidor. Tente novamente mais tarde.");
    }

    if (mysqli_num_rows($resultado_verificar) > 0) {
        die("Este e-mail já está cadastrado! Tente outro.");
    }

    // FOTO
    $caminho_foto = NULL;

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

    $inserido = executar($conn, $sql_inserir, "sssssssss",
        [$nome, $email, $senha_hash, $rua, $cep, $numero, $bairro, $cidade, $caminho_foto]);

    if ($inserido) {

        echo "<h3>Cadastro realizado com sucesso!</h3>";
        echo "<a href='../FRONT-END/login.html'>Clique aqui para fazer login</a>";

    } else {

        // Se não salvou o usuário, apaga a foto que já tinha sido enviada
        apagar_foto($caminho_foto);
        echo "Erro ao realizar o cadastro. Tente novamente mais tarde.";
    }

} else {

    header("Location: ../FRONT-END/cadastro.html");
    exit();
}

?>
