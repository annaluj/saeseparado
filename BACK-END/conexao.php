<?php
    $servidor = "localhost";
    $usuario = "root";
    $senha = "";
    $banco = "sae";

    // Erros do banco são tratados pelo código (sem mostrar detalhes ao usuário)
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = mysqli_connect($servidor, $usuario, $senha, $banco);

    if (!$conn) {
        error_log("Erro na conexão com o banco: " . mysqli_connect_error());
        die("Não foi possível conectar ao servidor. Tente novamente mais tarde.");
    }

    // Garante acentos e emojis corretos
    mysqli_set_charset($conn, "utf8mb4");

    // Executa uma consulta com prepared statement (protege contra SQL Injection).
    // Exemplo: executar($conn, "SELECT * FROM usuarios WHERE email = ?", "s", [$email]);
    // Tipos: "s" = texto, "i" = número inteiro.
    // Retorna o resultado (SELECT), true (INSERT/UPDATE/DELETE) ou false em caso de erro.
    function executar($conn, $sql, $tipos = "", $valores = []) {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            error_log("Erro no SQL: " . mysqli_error($conn));
            return false;
        }

        if ($tipos !== "") {
            mysqli_stmt_bind_param($stmt, $tipos, ...$valores);
        }

        if (!mysqli_stmt_execute($stmt)) {
            error_log("Erro ao executar SQL: " . mysqli_stmt_error($stmt));
            return false;
        }

        $resultado = mysqli_stmt_get_result($stmt);
        return $resultado !== false ? $resultado : true;
    }
?>
