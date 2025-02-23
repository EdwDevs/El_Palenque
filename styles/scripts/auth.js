// ------------------------- UTILIDADES -------------------------
// Hashing básico (requiere crypto-js en tu HTML)
async function hashPassword(password) {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    return Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');
}

// Mostrar/Ocultar loading
function toggleLoading(form, show) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (show) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Procesando...';
    } else {
        submitButton.disabled = false;
        submitButton.innerHTML = form.id === 'formLogin' ? 'Ingresar' : 'Crear Cuenta';
    }
}

// ------------------------- MANEJO DE LOGIN -------------------------
document.getElementById('formLogin').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const usuario = form.usuario.value.trim();
    const contraseña = form.contraseña.value;
    const errorContainer = document.getElementById('loginError');

    // Reset mensajes
    errorContainer.innerHTML = '';
    
    // Validación mejorada
    if (!usuario || !contraseña) {
        showError(errorContainer, '⚠️ Todos los campos son obligatorios');
        return;
    }

    try {
        toggleLoading(form, true);
        
        // Hashing de contraseña
        const hashedPassword = await hashPassword(contraseña);
        
        const response = await fetch('server/api/login.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('[name="csrfToken"]').content
            },
            body: JSON.stringify({ usuario, contraseña: hashedPassword })
        });
        
        const data = await response.json();

        if (data.success) {
            sessionStorage.setItem('authToken', data.token);
            window.location.href = 'menu.html';
        } else {
            showError(errorContainer, data.error || '❌ Credenciales incorrectas');
        }
    } catch (error) {
        showError(errorContainer, 'Error de conexión con el servidor');
    } finally {
        toggleLoading(form, false);
    }
});

// ---------------------- MANEJO DE REGISTRO ----------------------
document.getElementById('formRegistro').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const nombre = form.nombreCompleto.value.trim();
    const email = form.email.value.trim();
    const usuario = form.usuarioRegistro.value.trim();
    const contraseña = form.contraseñaRegistro.value;
    const confirmacion = form.confirmarContraseña.value;
    const errorContainer = document.getElementById('registroError');

    errorContainer.innerHTML = '';

    // Validación mejorada
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError(errorContainer, '⚠️ Formato de email inválido');
        return;
    }
    
    if (contraseña.length < 8) {
        showError(errorContainer, '⚠️ La contraseña debe tener al menos 8 caracteres');
        return;
    }
    
    if (contraseña !== confirmacion) {
        showError(errorContainer, '⚠️ Las contraseñas no coinciden');
        return;
    }

    try {
        toggleLoading(form, true);
        const hashedPassword = await hashPassword(contraseña);
        
        const response = await fetch('server/api/registro.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('[name="csrfToken"]').content
            },
            body: JSON.stringify({ nombre, email, usuario, contraseña: hashedPassword })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('✅ Registro exitoso! Redirigiendo...');
            setTimeout(() => {
                $('#registroModal').modal('hide');
                window.location.reload();
            }, 1500);
        } else {
            showError(errorContainer, data.error || '❌ Error en el registro');
        }
    } catch (error) {
        showError(errorContainer, 'Error de conexión');
    } finally {
        toggleLoading(form, false);
    }
});

// ------------------------- HELPERS -------------------------
function showError(container, message) {
    container.innerHTML = `<div class="alert alert-danger mt-2">${message}</div>`;
}

function showSuccess(message) {
    const container = document.getElementById('globalAlerts');
    container.innerHTML = `<div class="alert alert-success">${message}</div>`;
    setTimeout(() => container.innerHTML = '', 3000);
}