document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('server/api/productos.php');
        const productos = await response.json();

        let html = '';
        productos.forEach(producto => {
            html += `
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">${producto.nombre}</h5>
                            <p class="card-text">${producto.descripcion}</p>
                            <p class="text-success">$${producto.precio}</p>
                            <button class="btn btn-primary">Comprar</button>
                        </div>
                    </div>
                </div>
            `;
        });

        document.getElementById('listaProductos').innerHTML = html;
    } catch (error) {
        console.error('Error cargando productos:', error);
    }
});
async function publicarPost() {
    const contenido = document.getElementById('nuevoPost').value;
    if (!contenido) {
        alert('⚠️ Escribe un mensaje');
        return;
    }

    try {
        const response = await fetch('server/api/foro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ contenido })
        });
        if (response.ok) {
            location.reload(); // Recargar para ver el nuevo post
        }
    } catch (error) {
        alert('Error al publicar');
    }
}