<?php
//query para actualizar el producto en la DB
include('db.php');
$id=$_POST['id'];
$nombre=$_POST['nombre'];
$precio=$_POST['precio'];


$up=$conexion->query("UPDATE productos
                SET nombre='$nombre',
                    precio='$precio'
                    
                    WHERE id='$id'");
    if($up){
        header('location:productos.php');
    }else{
        header('location:Error.php');
    }
    ?>