<?php

require_once "seguranca.php";

include("conexao.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

// Só aceita exclusão enviada pelo formulário do perfil (POST + token)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header("Location: perfil.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Guarda a foto para apagar depois
$resultado = executar($conn, "SELECT foto FROM usuarios WHERE usuario_id = ?", "i", [$usuario_id]);
$usuario = $resultado ? mysqli_fetch_assoc($resultado) : null;

// Exclui o usuário do banco (agendamentos e emoções são apagados junto pelo ON DELETE CASCADE)
if (executar($conn, "DELETE FROM usuarios WHERE usuario_id = ?", "i", [$usuario_id])) {

    if (!empty($usuario['foto'])) {
        apagar_foto($usuario['foto']);
    }

    // Apaga as informações da sessão
    session_unset();
    session_destroy();

    // Volta para a página inicial
    header("Location: ../FRONT-END/index.html");
    exit();

} else {

    echo "Erro ao excluir a conta. Tente novamente mais tarde.";
}

?>
