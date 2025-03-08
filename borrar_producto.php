<?php
//Este codigo es para borrar producto
include('db.php');
$id=$_REQUEST['id'];
$del=$conexion->query("DELETE FROM productos WHERE id=".$id);


if($del){
    header('location:productos.php');
}
?>