function showPassword() {
  let passwordInput = document.getElementById("passwordInput");
  if (passwordInput.type === "password") {
    passwordInput.type = "text";
  } else {
    passwordInput.type = "password";
  }
}

function comparePassword(e) {
  let passwordInput = document.getElementById("passwordInput").value;
  let comfirmPasswordInput = document.getElementById("comfirmPasswordInput").value;

  if (passwordInput !== comfirmPasswordInput) {
    document.getElementById("errorDiv").innerHTML = `
    <div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-2' role='alert'>
        <strong class='font-bold'>Erro!</strong>
        <span class='block sm:inline'>As palavras-passses não coincidem!</span>
    </div>`;

    e.preventDefault();
  }
}