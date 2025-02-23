document.addEventListener('DOMContentLoaded', () => {
    // Elementos de la interfaz
    const nombreUsuario = document.getElementById('nombreUsuario');
    const logoutBtn = document.getElementById('logoutBtn');
    const authToken = sessionStorage.getItem('authToken');

    // Verificar autenticación
    if (!authToken) {
        window.location.href = 'index.html';
        return;
    }

    // Cargar datos del usuario
    const cargarDatosUsuario = async () => {
        try {
            const response = await fetch('server/api/user.php', {
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'X-CSRF-Token': document.querySelector('[name="csrfToken"]').content
                }
            });
            
            if (!response.ok) throw new Error('Error en la solicitud');
            
            const data = await response.json();
            nombreUsuario.textContent = data.nombre || 'Usuario';
            
        } catch (error) {
            console.error('Error:', error);
            sessionStorage.removeItem('authToken');
            window.location.href = 'index.html';
        }
    };

    // Manejar cierre de sesión
    logoutBtn.addEventListener('click', () => {
        sessionStorage.removeItem('authToken');
        window.location.href = 'index.html';
    });

    // Iniciar carga de datos
    cargarDatosUsuario();
});