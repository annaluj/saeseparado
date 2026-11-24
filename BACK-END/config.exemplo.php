<?php
// MODELO de configuração do banco de dados.
// Copie este arquivo para "config.php" (na mesma pasta) e coloque os dados do seu banco.
// O config.php NÃO vai para o GitHub (está no .gitignore), então cada pessoa tem o seu.
//
// No XAMPP local o padrão é usuário "root" sem senha.
// Antes de publicar o site, crie um usuário só para o SAE no MySQL, por exemplo:
//   CREATE USER 'sae_app'@'localhost' IDENTIFIED BY 'uma-senha-forte';
//   GRANT SELECT, INSERT, UPDATE, DELETE ON sae.* TO 'sae_app'@'localhost';

return [
    'servidor' => 'localhost',
    'usuario'  => 'root',
    'senha'    => '',
    'banco'    => 'sae',
];
