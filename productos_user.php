<?php
//Pagina con los formularios y crud de producto
include('db.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link rel="stylesheet" href="estilos_home.css"> 
</head>
<body>
<header>
<a href= "user_home.php">
<h1><img src="imagenes/logo.jpeg" alt="PALENQUE" width= "1000" height = "150" ></h1>
<button class="btn-auth"> 
    <i class="fas fa -sign-out-alt"></i>Volver 
    <a href="user_home.php">
</button>
</a>
</header>

    <table border="1">
        <tr>
            <th>Nombres</th>
            <th>Precio</th>
            <th>carrito</th>
            
            <?php
                //Hago consulta a la BD
                 $sel=$conexion->query("SELECT * FROM productos");
                while($fila=$sel->fetch_assoc()){//ejecucion while abierto
          ?>
        <tr>
            <td><?php echo $fila['nombre']?></td>
            <td><?php echo $fila['precio']?></td>
          
            
            <td><a href="actualizar_producto.php?id=<?php echo $fila['id']?>">Agregar al carrito</a></td>
            
            </a></td>
            <?php
        }
        ?>
        </tr>

    </table>

</body>
</html>