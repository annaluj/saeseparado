<?php
require_once "seguranca.php";

// Só sai da conta pelo botão da página (POST + token), para outro site não conseguir deslogar o usuário
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header("Location: pagina-principal.php");
    exit();
}

session_unset();
session_destroy();
header("Location: ../FRONT-END/index.html");
exit();
?>
