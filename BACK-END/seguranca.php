<?php
// Configurações de segurança usadas por todas as páginas do BACK-END.
// Inclua com: require_once "seguranca.php";  (no lugar de session_start())

// Não mostra erros internos para o usuário; eles vão para o log do PHP
// (C:\xampp\php\logs\php_error_log). Para depurar, troque '0' por '1' temporariamente.
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
// Depois de 5 erros seguidos com o mesmo e-mail e IP, bloqueia por 15 minutos.
// As tentativas ficam guardadas em arquivos na pasta temporária do sistema.

const LOGIN_MAX_TENTATIVAS = 5;
const LOGIN_BLOQUEIO_SEGUNDOS = 15 * 60;

function arquivo_tentativas($email) {
    $pasta = sys_get_temp_dir() . '/sae_login';
    if (!is_dir($pasta)) {
        mkdir($pasta, 0700, true);
    }
    $chave = hash('sha256', strtolower($email) . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''));
    return $pasta . '/' . $chave . '.json';
}

function ler_tentativas($email) {
    $arquivo = arquivo_tentativas($email);
    $dados = is_file($arquivo) ? json_decode(file_get_contents($arquivo), true) : null;

    // Passou o tempo de bloqueio: zera a contagem
    if (!$dados || time() - $dados['ultima'] > LOGIN_BLOQUEIO_SEGUNDOS) {
        return ['quantidade' => 0, 'ultima' => 0];
    }
    return $dados;
}

function login_bloqueado($email) {
    return ler_tentativas($email)['quantidade'] >= LOGIN_MAX_TENTATIVAS;
}

function registrar_falha_login($email) {
    $dados = ler_tentativas($email);
    $dados['quantidade']++;
    $dados['ultima'] = time();
    file_put_contents(arquivo_tentativas($email), json_encode($dados), LOCK_EX);
}

function limpar_tentativas_login($email) {
    $arquivo = arquivo_tentativas($email);
    if (is_file($arquivo)) {
        unlink($arquivo);
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
