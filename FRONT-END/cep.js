// Preenche rua, bairro e cidade automaticamente a partir do CEP, usando o ViaCEP (https://viacep.com.br).
// Também coloca o hífen sozinho enquanto a pessoa digita (00000-000).
// Usado no cadastro (cadastro.html) e na edição do perfil (editar.php).

const campoCep = document.querySelector('input[name="cep"]');
const campoRua = document.querySelector('input[name="rua"]');
const campoBairro = document.querySelector('input[name="bairro"]');
const campoCidade = document.querySelector('input[name="cidade"]');
const campoNumero = document.querySelector('input[name="numero_casa"]');

let ultimoCepBuscado = "";

// Deixa só os números e coloca o hífen depois do 5º dígito: 01001000 -> 01001-000
function formatarCep(valor) {
    const numeros = valor.replace(/\D/g, "").slice(0, 8);
    return numeros.length > 5 ? numeros.slice(0, 5) + "-" + numeros.slice(5) : numeros;
}

// Mostra (ou limpa) um aviso no próprio campo de CEP, usando o balão de erro do navegador
function avisoCep(mensagem) {
    campoCep.setCustomValidity(mensagem);
    if (mensagem) {
        campoCep.reportValidity();
    }
}

function buscarCep() {
    const cep = campoCep.value.replace(/\D/g, "");

    if (cep.length !== 8 || cep === ultimoCepBuscado) {
        return;
    }
    ultimoCepBuscado = cep;
    avisoCep("");

    const campos = [campoRua, campoBairro, campoCidade];
    const placeholdersOriginais = campos.map(c => c.placeholder);
    campos.forEach(c => c.placeholder = "Buscando...");

    fetch("https://viacep.com.br/ws/" + cep + "/json/")
        .then(resposta => {
            if (!resposta.ok) {
                throw new Error("Resposta inválida do ViaCEP");
            }
            return resposta.json();
        })
        .then(endereco => {
            if (endereco.erro) {
                avisoCep("CEP não encontrado. Confira o número ou preencha o endereço manualmente.");
                return;
            }

            // Só substitui quando o ViaCEP devolve o dado (CEPs de cidade inteira vêm sem rua e bairro)
            if (endereco.logradouro) campoRua.value = endereco.logradouro;
            if (endereco.bairro) campoBairro.value = endereco.bairro;
            if (endereco.localidade) campoCidade.value = endereco.localidade;

            // Leva a pessoa para o próximo campo que ela precisa preencher
            if (!campoRua.value) {
                campoRua.focus();
            } else if (campoNumero) {
                campoNumero.focus();
            }
        })
        .catch(erro => {
            console.error("Erro ao buscar o CEP:", erro);
            avisoCep("Não foi possível buscar o CEP agora. Preencha o endereço manualmente.");
        })
        .finally(() => {
            campos.forEach((c, i) => c.placeholder = placeholdersOriginais[i]);
        });
}

if (campoCep && campoRua && campoBairro && campoCidade) {
    // Já formata o CEP que veio do banco (na tela de editar)
    campoCep.value = formatarCep(campoCep.value);

    campoCep.addEventListener("input", function() {
        avisoCep("");
        campoCep.value = formatarCep(campoCep.value);

        // Busca assim que a pessoa termina de digitar os 8 números
        if (campoCep.value.length === 9) {
            buscarCep();
        } else {
            ultimoCepBuscado = "";
        }
    });
    campoCep.addEventListener("blur", buscarCep);
}
