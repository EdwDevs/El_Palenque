// scripts/auth.js
document.getElementById('formRegistro').addEventListener('submit', async (e) => {
    e.preventDefault();

    // Obtener datos del formulario
    const datos = {
        nombre: document.getElementById('nombreCompleto').value.trim(),
        email: document.getElementById('email').value.trim(),
        usuario: document.getElementById('usuarioRegistro').value.trim(),
        contraseña: document.getElementById('contraseñaRegistro').value,
        confirmacion: document.getElementById('confirmarContraseña').value
    };

        // ------------ VALIDACIONES AQUÍ ------------
    // 1. Validar formato de email
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(datos.email)) {
        alert('❌ El correo electrónico no es válido');
        return;
    }

    // 2. Validar contraseña fuerte (mínimo 8 caracteres)
    if (datos.contraseña.length < 8) {
        alert('⚠️ La contraseña debe tener al menos 8 caracteres');
        return;
    }

    // 3. Confirmar contraseña
    if (datos.contraseña !== datos.confirmacion) {
        alert('⚠️ Las contraseñas no coinciden');
        return;
    }
    // Validación básica frontend
    if (datos.contraseña !== datos.confirmacion) {
        alert('Las contraseñas no coinciden');
        return;
    }

    try {
        // Enviar datos al backend
        const response = await fetch('server/api/registro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.success) {
            alert('Registro exitoso!');
            window.location.href = 'menu.html';
        } else {
            alert(`Error: ${resultado.error}`);
        }
    } catch (error) {
        alert('Error de conexión con el servidor');
    }
});