<?php

require_once "seguranca.php";
include("conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['nova_foto'])) {

    if (!csrf_valido()) {
        die("Requisição inválida. Recarregue a página e tente novamente.");
    }

    $foto = salvar_foto($_FILES['nova_foto']);

    if (isset($foto['erro'])) {
        die($foto['erro']);
    }

    // Pega a foto antiga
    $resultado = executar($conn, "SELECT foto FROM usuarios WHERE usuario_id = ?", "i", [$usuario_id]);
    $usuario = $resultado ? mysqli_fetch_assoc($resultado) : null;

    // Salva o caminho da nova foto
    if (executar($conn, "UPDATE usuarios SET foto = ? WHERE usuario_id = ?", "si", [$foto['caminho'], $usuario_id])) {

        // Apaga a foto antiga
        if (!empty($usuario['foto'])) {
            apagar_foto($usuario['foto']);
        }

        header("Location: pagina-principal.php");
        exit();

    } else {

        apagar_foto($foto['caminho']);
        echo "Erro ao atualizar a foto. Tente novamente mais tarde.";

    }

} else {

    header("Location: pagina-principal.php");
    exit();

}

?>
