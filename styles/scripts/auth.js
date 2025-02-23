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

    // ========== VALIDACIONES FRONTEND ==========
    // 1. Campos vacíos
    if (!datos.nombre || !datos.email || !datos.usuario || !datos.contraseña) {
        alert('⚠️ Todos los campos son obligatorios');
        return;
    }

    // 2. Formato de email
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(datos.email)) {
        alert('❌ Formato de email inválido');
        return;
    }

    // 3. Contraseña segura
    if (datos.contraseña.length < 8) {
        alert('⚠️ La contraseña debe tener mínimo 8 caracteres');
        return;
    }

    // 4. Confirmación de contraseña
    if (datos.contraseña !== datos.confirmacion) {
        alert('⚠️ Las contraseñas no coinciden');
        return;
    }

    // ========== ENVÍO AL SERVIDOR ==========
    try {
        const response = await fetch('server/api/registro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.success) {
            alert('✅ Registro exitoso! Redirigiendo...');
            window.location.href = 'menu.html';
        } else {
            alert(`❌ Error: ${resultado.error}`);
        }
    } catch (error) {
        alert('🔥 Error de conexión con el servidor');
    }
});