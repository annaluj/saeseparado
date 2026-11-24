<?php
require_once "seguranca.php";
session_unset();
session_destroy();
header("Location: ../FRONT-END/index.html");
exit();
?>
