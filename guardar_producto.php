<?php
//Consulta en la base de datos e ingresa el producto en crud de productos
include('db.php');
$nombre=$_POST['nombre'];
$descripcion=$_POST['descripcion'];
$precio=$_POST['precio'];
$fecha_creacion=$_POST['fecha_creacion'];

//Hago el string de Insercion
$ins=$conexion->query("INSERT INTO productos(nombre,descripcion,precio,fecha_creacion)
VALUES('$nombre','$descripcion','$precio','$fecha_creacion')");
       
    if($ins){
        echo "<h1>Registro ok</h1><br>";
    }else{
        echo"<h1>ERROR AL GUARDAR</h1><br>";
    }                    
    echo"<h1><a href='productos.php'<a/>Regresar</h1>";
    ?>