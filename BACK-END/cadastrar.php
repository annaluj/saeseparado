
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = $_POST['senha'];

    $rua = mysqli_real_escape_string($conn, $_POST['rua']);
    $cep = mysqli_real_escape_string($conn, $_POST['cep']);
    $numero = mysqli_real_escape_string($conn, $_POST['numero']);
    $bairro = mysqli_real_escape_string($conn, $_POST['bairro']);
    $cidade = mysqli_real_escape_string($conn, $_POST['cidade']);

    if (empty($nome) || empty($email) || empty($senha)) {
        die("Por favor, preencha todos os campos obrigatórios.");
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Verifica se o e-mail já existe
    $sql_verificar = "SELECT usuario_id FROM usuarios WHERE email = '$email'";
    $resultado_verificar = mysqli_query($conn, $sql_verificar);

    if (mysqli_num_rows($resultado_verificar) > 0) {
        die("Este e-mail já está cadastrado! Tente outro.");
    }

    // FOTO
    $caminho_foto = NULL;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {

        $nome_foto = $_FILES['foto']['name'];
        $foto_temporaria = $_FILES['foto']['tmp_name'];

        $extensao = strtolower(pathinfo($nome_foto, PATHINFO_EXTENSION));

        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extensao, $extensoes_permitidas)) {
            die("Formato de imagem não permitido.");
        }

        // Cria a pasta uploads se ela não existir
        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }

        // Nome único para a foto
        $novo_nome = uniqid() . "." . $extensao;

        $caminho_foto = "uploads/" . $novo_nome;

        // Move a foto para a pasta uploads
        if (!move_uploaded_file($foto_temporaria, $caminho_foto)) {
            die("Erro ao salvar a foto.");
        }
    }

    // Cadastra o usuário
    $sql_inserir = "INSERT INTO usuarios 
    (nome, email, senha_hash, rua, cep, numero_casa, bairro, cidade, foto) 
    VALUES 
    ('$nome', '$email', '$senha_hash', '$rua', '$cep', '$numero', '$bairro', '$cidade', '$caminho_foto')";

    if (mysqli_query($conn, $sql_inserir)) {

        echo "<h3>Cadastro realizado com sucesso!</h3>";
        echo "<a href='../FRONT-END/login.html'>Clique aqui para fazer login</a>";

    } else {

        echo "Erro ao cadastrar no banco de dados: " . mysqli_error($conn);
    }

} else {

    header("Location: ../FRONT-END/cadastro.html");
    exit();
}

?>

