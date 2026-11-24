// Funções usadas em várias páginas. Carregue este arquivo antes dos outros scripts.

// Token de segurança (CSRF) que o PHP coloca no <head> da página
function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : "";
}

// Botões que levam para outra página: <button type="button" data-href="pagina.php">
document.querySelectorAll("[data-href]").forEach(function(botao) {
    botao.addEventListener("click", function() {
        window.location.href = botao.dataset.href;
    });
});

// Formulários que pedem confirmação antes de enviar: <form data-confirmar="Tem certeza?">
document.querySelectorAll("form[data-confirmar]").forEach(function(form) {
    form.addEventListener("submit", function(event) {
        if (!confirm(form.dataset.confirmar)) {
            event.preventDefault();
        }
    });
});
