<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

include("conexao.php");

$usuario_id = $_SESSION['usuario_id'];

if (!isset($_POST['agendamento_id'])) {
    header("Location: pagina-principal.php");
    exit();
}

$agendamento_id = $_POST['agendamento_id'];

$sql = "DELETE FROM agendamentos 
        WHERE id = '$agendamento_id' 
        AND usuario_id = '$usuario_id'";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro ao excluir agendamento: " . mysqli_error($conn));
}

header("Location: pagina-principal.php");
exit();

?>