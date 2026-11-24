<?php
// Configurações de segurança usadas por todas as páginas do BACK-END.
// Inclua com: require_once "seguranca.php";  (no lugar de session_start())

// Não mostra erros internos para o usuário; eles vão para o log do PHP
// (no XAMPP: C:\xampp\apache\logs\error.log). Para depurar, troque '0' por '1' temporariamente.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Horário do Brasil (o XAMPP vem configurado com o horário da Alemanha)
date_default_timezone_set('America/Sao_Paulo');

// Sessão com cookie protegido
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,                  // JavaScript não consegue ler o cookie
        'samesite' => 'Lax',                 // cookie não vai em requisições de outros sites
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

// ---------- Login obrigatório ----------

// Coloque no começo das páginas que só podem ser vistas por quem está logado.
// Use exigir_login(true) nos arquivos que respondem JSON para o JavaScript.
function exigir_login($resposta_json = false) {
    if (isset($_SESSION['usuario_id'])) {
        return;
    }

    if ($resposta_json) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(["success" => false, "message" => "Sessão expirada. Faça login novamente."]);
    } else {
        header("Location: ../FRONT-END/login.html");
    }
    exit();
}

// ---------- Tipos de conta (estudante, profissional, admin) ----------

// Página para onde cada tipo de conta vai depois do login
function pagina_inicial($tipo) {
    if ($tipo === 'admin') {
        return 'area-admin.php';
    }
    if ($tipo === 'profissional') {
        return 'area-profissional.php';
    }
    return 'pagina-principal.php';
}

// Só deixa continuar quem é de um dos tipos informados. Ex.: exigir_tipo($conn, 'admin');
// Confere no banco (e não só na sessão), então uma conta desativada ou que mudou
// de tipo perde o acesso na hora. Retorna o tipo do usuário.
function exigir_tipo($conn, ...$tipos_permitidos) {
    exigir_login();

    $resultado = executar($conn, "SELECT tipo, ativo FROM usuarios WHERE usuario_id = ?", "i", [$_SESSION['usuario_id']]);
    $usuario = $resultado ? mysqli_fetch_assoc($resultado) : null;

    // Conta apagada ou desativada: encerra a sessão
    if (!$usuario || !$usuario['ativo']) {
        session_unset();
        session_destroy();
        header("Location: ../FRONT-END/login.html");
        exit();
    }

    $_SESSION['usuario_tipo'] = $usuario['tipo'];

    if (!in_array($usuario['tipo'], $tipos_permitidos, true)) {
        header("Location: " . pagina_inicial($usuario['tipo']));
        exit();
    }

    return $usuario['tipo'];
}

// ---------- Avisos para mostrar na próxima página ----------
// Ex.: definir_aviso("Consulta confirmada!"); header("Location: area-profissional.php");

function definir_aviso($mensagem, $erro = false) {
    $_SESSION['aviso'] = ['mensagem' => $mensagem, 'erro' => $erro];
}

// Mostra o aviso guardado (se houver) e apaga para não repetir
function mostrar_aviso() {
    if (empty($_SESSION['aviso'])) {
        return;
    }
    $aviso = $_SESSION['aviso'];
    unset($_SESSION['aviso']);

    $classe = $aviso['erro'] ? 'aviso aviso-erro' : 'aviso';
    echo '<p class="' . $classe . '">' . htmlspecialchars($aviso['mensagem']) . '</p>';
}

// ---------- Proteção CSRF ----------

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Campo escondido para colocar dentro dos <form>
function csrf_campo() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

// Confere o token vindo do formulário (POST) ou do fetch (cabeçalho X-CSRF-Token)
function csrf_valido() {
    $enviado = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return !empty($_SESSION['csrf_token']) && is_string($enviado)
        && hash_equals($_SESSION['csrf_token'], $enviado);
}

// ---------- Limite de tentativas de login ----------
// Bloqueia por 15 minutos depois de:
//  - 5 erros com o mesmo e-mail vindo do mesmo IP, ou
//  - 15 erros com o mesmo e-mail vindo de IPs diferentes.
// As tentativas ficam em arquivos na pasta temporária do sistema.

const LOGIN_BLOQUEIO_SEGUNDOS = 15 * 60;

function contadores_login($email) {
    $email = strtolower($email);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return [
        hash('sha256', 'email-ip|' . $email . '|' . $ip) => 5,
        hash('sha256', 'email|' . $email) => 15,
    ];
}

// Abre o arquivo do contador com trava, para que requisições ao mesmo tempo
// não consigam furar o limite. Retorna [arquivo aberto, dados].
function abrir_contador($chave) {
    $pasta = sys_get_temp_dir() . '/sae_login';
    if (!is_dir($pasta)) {
        mkdir($pasta, 0700, true);
    }

    $arquivo = fopen($pasta . '/' . $chave . '.json', 'c+');
    flock($arquivo, LOCK_EX);

    $dados = json_decode(stream_get_contents($arquivo), true);

    // Passou o tempo de bloqueio: zera a contagem
    if (!$dados || time() - $dados['ultima'] > LOGIN_BLOQUEIO_SEGUNDOS) {
        $dados = ['quantidade' => 0, 'ultima' => 0];
    }
    return [$arquivo, $dados];
}

function fechar_contador($arquivo, $dados = null) {
    if ($dados !== null) {
        ftruncate($arquivo, 0);
        rewind($arquivo);
        fwrite($arquivo, json_encode($dados));
    }
    flock($arquivo, LOCK_UN);
    fclose($arquivo);
}

function login_bloqueado($email) {
    foreach (contadores_login($email) as $chave => $limite) {
        [$arquivo, $dados] = abrir_contador($chave);
        fechar_contador($arquivo);
        if ($dados['quantidade'] >= $limite) {
            return true;
        }
    }
    return false;
}

function registrar_falha_login($email) {
    foreach (array_keys(contadores_login($email)) as $chave) {
        [$arquivo, $dados] = abrir_contador($chave);
        $dados['quantidade']++;
        $dados['ultima'] = time();
        fechar_contador($arquivo, $dados);
    }
}

function limpar_tentativas_login($email) {
    foreach (array_keys(contadores_login($email)) as $chave) {
        $caminho = sys_get_temp_dir() . '/sae_login/' . $chave . '.json';
        if (is_file($caminho)) {
            unlink($caminho);
        }
    }
}

// ---------- Upload de foto ----------

// Confere se o arquivo é mesmo uma imagem e salva em BACK-END/uploads.
// Retorna ['caminho' => 'uploads/arquivo.ext'] ou ['erro' => 'mensagem'].
function salvar_foto($arquivo) {
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['erro' => 'Erro ao enviar a foto.'];
    }

    if ($arquivo['size'] > 5 * 1024 * 1024) {
        return ['erro' => 'A foto deve ter no máximo 5 MB.'];
    }

    // Descobre o tipo real pelo conteúdo do arquivo (não pelo nome)
    $tipos_permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipo = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);

    if (!isset($tipos_permitidos[$tipo]) || @getimagesize($arquivo['tmp_name']) === false) {
        return ['erro' => 'Formato de imagem não permitido.'];
    }

    $pasta = __DIR__ . '/uploads';
    if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
    }

    $novo_nome = bin2hex(random_bytes(16)) . '.' . $tipos_permitidos[$tipo];

    if (!move_uploaded_file($arquivo['tmp_name'], $pasta . '/' . $novo_nome)) {
        return ['erro' => 'Erro ao salvar a foto.'];
    }

    return ['caminho' => 'uploads/' . $novo_nome];
}

// Apaga uma foto salva (só aceita arquivos dentro de BACK-END/uploads)
function apagar_foto($caminho) {
    if (empty($caminho)) {
        return;
    }
    $arquivo = __DIR__ . '/uploads/' . basename($caminho);
    if (is_file($arquivo)) {
        unlink($arquivo);
    }
}
