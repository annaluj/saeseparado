const loginErro = document.querySelector("#erro");
const login = document.querySelector(".login");
const inputEmail = document.querySelector('input[name="email"]');
const inputSenha = document.querySelector('input[name="senha"]');

if (login) {
    login.addEventListener("submit", function (event) {
        event.preventDefault();

        const valorEmail = inputEmail.value.trim();
        const valorSenha = inputSenha.value;

        loginErro.classList.remove("mostrar");

        fetch("../BACK-END/login.php", {
            method: 'POST',
            body: new URLSearchParams({
                email: valorEmail,
                senha: valorSenha
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "../BACK-END/pagina-principal.php";
            } else {
                loginErro.textContent = data.message;
                loginErro.classList.add("mostrar");
                inputEmail.classList.add("input-erro");
            }
        })
        .catch(error => {
            console.error("Erro na requisição:", error);
            loginErro.textContent = "Erro ao conectar ao servidor.";
            loginErro.classList.add("mostrar");
        });
    });
}