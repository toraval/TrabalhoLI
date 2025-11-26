document.getElementById("step2Form").addEventListener("submit", function(e){

    let idade = document.querySelector("input[name='idade']").value;
    let salario = document.querySelector("input[name='salario']").value;

    if (idade < 10 || idade > 100) {
        alert("Insira uma idade válida.");
        e.preventDefault();
        return;
    }

    if (salario < 0) {
        alert("O salário não pode ser negativo.");
        e.preventDefault();
        return;
    }

});
