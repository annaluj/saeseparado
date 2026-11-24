<?php
// Cabeçalho das áreas logadas (estudante, profissional e administrador):
// boas-vindas, foto clicável para trocar e botão de sair.
// Antes de incluir, defina: $foto (caminho ou null) e $subtitulo (texto abaixo do "Olá").
?>
    <header class="inicio">
        <div class="boas-vindas">
            <h1>
                Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!
            </h1>
            <p><?php echo htmlspecialchars($subtitulo); ?></p>
        </div>

        <div class="foto-perfil">
            <form action="alterar_foto.php" method="POST" enctype="multipart/form-data">
                <?php echo csrf_campo(); ?>
                <label for="nova-foto" class="foto-clicavel" title="Clique para alterar sua foto">
                    <?php if (!empty($foto)): ?>
                        <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto do usuário">
                    <?php else: ?>
                        <div class="sem-foto">👤</div>
                    <?php endif; ?>
                </label>

                <input type="file" id="nova-foto" name="nova_foto" accept="image/*">
            </form>

            <form action="logout.php" method="POST">
                <?php echo csrf_campo(); ?>
                <button type="submit" class="sair">Sair da conta</button>
            </form>
        </div>
    </header>

    <?php mostrar_aviso(); ?>
