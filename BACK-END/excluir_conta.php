<?php

session_start();

include("conexao.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Exclui o usuário do banco
$sql = "DELETE FROM usuarios WHERE usuario_id = '$usuario_id'";

if (mysqli_query($conn, $sql)) {

    // Apaga as informações da sessão
    session_unset();
    session_destroy();

    // Volta para a página inicial
    header("Location: ../FRONT-END/index.html");
    exit();

} else {

    echo "Erro ao excluir a conta: " . mysqli_error($conn);
}

?>