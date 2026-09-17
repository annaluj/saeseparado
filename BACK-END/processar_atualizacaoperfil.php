<?php
session_start();
include("conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id'];

    // 1. Busca os dados atuais do banco como backup
    $sql_busca = "SELECT * FROM usuarios WHERE usuario_id = '$usuario_id'";
    $res_busca = mysqli_query($conn, $sql_busca);
    $atual = mysqli_fetch_assoc($res_busca);

    // 2. Se o campo enviado estiver vazio, mantém o valor atual do banco de dados
    $nome        = !empty($_POST['nome'])        ? mysqli_real_escape_string($conn, $_POST['nome'])        : $atual['nome'];
    $email       = !empty($_POST['email'])       ? mysqli_real_escape_string($conn, $_POST['email'])       : $atual['email'];
    $rua         = !empty($_POST['rua'])         ? mysqli_real_escape_string($conn, $_POST['rua'])         : $atual['rua'];
    $cep         = !empty($_POST['cep'])         ? mysqli_real_escape_string($conn, $_POST['cep'])         : $atual['cep'];
    $numero_casa = !empty($_POST['numero_casa']) ? mysqli_real_escape_string($conn, $_POST['numero_casa']) : $atual['numero_casa'];
    $bairro      = !empty($_POST['bairro'])      ? mysqli_real_escape_string($conn, $_POST['bairro'])      : $atual['bairro'];
    $cidade      = !empty($_POST['cidade'])      ? mysqli_real_escape_string($conn, $_POST['cidade'])      : $atual['cidade'];

    // 3. Executa a atualização
    $sql_update = "UPDATE usuarios SET 
                    nome = '$nome', 
                    email = '$email', 
                    rua = '$rua', 
                    cep = '$cep', 
                    numero_casa = '$numero_casa', 
                    bairro = '$bairro', 
                    cidade = '$cidade' 
                   WHERE usuario_id = '$usuario_id'";

    if (mysqli_query($conn, $sql_update)) {
        $_SESSION['usuario_nome'] = $nome; // Atualiza o nome na sessão
        header("Location: perfil.php");
        exit();
    } else {
        echo "Erro ao atualizar banco de dados: " . mysqli_error($conn);
    }
} else {
    header("Location: perfil.php");
    exit();
}
?>