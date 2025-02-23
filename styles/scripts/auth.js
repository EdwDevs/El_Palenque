document.getElementById('formLogin').addEventListener('submit', async (e) => {
    e.preventDefault();
    const usuario = document.getElementById('usuario').value;
    const contraseña = document.getElementById('contraseña').value;

    if (!usuario || !contraseña) {
        alert('⚠️ Usuario y contraseña son obligatorios');
        return;
    }

    try {
        const response = await fetch('server/api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario, contraseña })
        });
        const data = await response.json();

        if (data.success) {
            window.location.href = 'menu.html'; 
        } else {
            alert('❌ Credenciales incorrectas');
        }
    } catch (error) {
        alert('Error de conexión');
    }
});
// Validación del formulario de registro
document.getElementById('formRegistro').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const nombre = document.getElementById('nombre').value;
    const email = document.getElementById('email').value;
    const usuario = document.getElementById('usuarioRegistro').value;
    const contraseña = document.getElementById('contraseñaRegistro').value;
    const confirmacion = document.getElementById('confirmarContraseña').value;

    // Validación básica
    if (contraseña !== confirmacion) {
        alert('⚠️ Las contraseñas no coinciden');
        return;
    }

    try {
        const response = await fetch('server/api/registro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, email, usuario, contraseña })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Registro exitoso! Ya puedes iniciar sesión');
            $('#registroModal').modal('hide');
        } else {
            alert(`❌ Error: ${data.error}`);
        }
    } catch (error) {
        alert('Error de conexión');
    }
});