<?php

require_once "seguranca.php";
exigir_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_FILES['nova_foto'])) {
    header("Location: pagina-principal.php");
    exit();
}

if (!csrf_valido()) {
    die("Requisição inválida. Recarregue a página e tente novamente.");
}

require_once "conexao.php";
require_once "usuarios.php";

$usuario_id = $_SESSION['usuario_id'];

$foto = salvar_foto($_FILES['nova_foto']);

if (isset($foto['erro'])) {
    die($foto['erro']);
}

$foto_antiga = foto_do_usuario($conn, $usuario_id);

// Salva o caminho da nova foto
if (executar($conn, "UPDATE usuarios SET foto = ? WHERE usuario_id = ?", "si", [$foto['caminho'], $usuario_id])) {

    apagar_foto($foto_antiga);

    header("Location: pagina-principal.php");
    exit();
}

apagar_foto($foto['caminho']);
echo "Erro ao atualizar a foto. Tente novamente mais tarde.";

?>
