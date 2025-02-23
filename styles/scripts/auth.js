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