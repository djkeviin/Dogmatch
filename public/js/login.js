// Función para mostrar/ocultar contraseña
// Mostrar/ocultar contraseña al pasar por el icono
const passwordInput = document.getElementById("passwordInput");
const togglePassword = document.getElementById("togglePassword");

// Mostrar al pasar el mouse
togglePassword.addEventListener("mouseover", () => {
  passwordInput.type = "text";
});

// Ocultar al salir del icono
togglePassword.addEventListener("mouseout", () => {
  passwordInput.type = "password";
});

// Contador de intentos fallidos
let intentosFallidos = 0;
const MAX_INTENTOS = 3;

// Función para manejar el inicio de sesión
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const errorElement = document.getElementById('loginError');
    
    // Verificar si la cuenta está bloqueada
    if (sessionStorage.getItem('cuentaBloqueada')) {
        const tiempoRestante = Math.ceil((parseInt(sessionStorage.getItem('tiempoDesbloqueo')) - Date.now()) / 1000 / 60);
        e.preventDefault();
        errorElement.textContent = `Cuenta bloqueada. Intente nuevamente en ${tiempoRestante} minutos.`;
        return;
    }

    // Si hay error en el login (se maneja en PHP), incrementar contador
    if (errorElement && errorElement.textContent.includes('Credenciales incorrectas')) {
        intentosFallidos++;
        if (intentosFallidos >= MAX_INTENTOS) {
            e.preventDefault();
            // Bloquear cuenta por 15 minutos
            sessionStorage.setItem('cuentaBloqueada', 'true');
            sessionStorage.setItem('tiempoDesbloqueo', Date.now() + (15 * 60 * 1000));
            errorElement.textContent = 'Cuenta bloqueada por 15 minutos debido a múltiples intentos fallidos.';
        }
    }
}); 