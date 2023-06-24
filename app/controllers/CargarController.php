<?php
require_once("./models/Pedido.php");
require_once("./models/Mesa.php");
require_once("./models/Producto.php");
require_once("./models/Usuario.php");

class CargarController{

    public static function CargarUsuarios( $request,  $response, $args){
        $archivo= "./csv/usuarios.csv";

        if(($lector= fopen($archivo, 'r')) !== false){
            $fila= fgetcsv($lector);
            while(($fila=fgetcsv($lector)) !== false){
                $usuario = new Usuario();
                $usuario->id= $fila[0];
                $usuario->usuario= $fila[1];
                $usuario->clave = $fila[2];
                $usuario->tipo = $fila[3];
                $usuario->crearUsuario();
            }
            fclose($lector);
        }
        $response->getBody()->write("Se cargo correctamente");
        return $response;
    }

    public static function CargarMesas( $request,  $response, $args){
        $archivo= "./csv/mesas.csv";

        if(($lector= fopen($archivo, 'r')) !== false){
            $fila= fgetcsv($lector);
            while(($fila=fgetcsv($lector)) !== false){
                $usuario = new Mesa();
                $usuario->id= $fila[0];
                $usuario->codigo= $fila[1];
                $usuario->estado = $fila[2];
                $usuario->CargarMesa();
            }
            fclose($lector);
        }
        $response->getBody()->write("Se cargo correctamente");
        return $response;
    }

    public static function CargarPedidos( $request,  $response, $args){
        $archivo= "./csv/pedidos.csv";

        if(($lector= fopen($archivo, 'r')) !== false){
            $fila= fgetcsv($lector);
            while(($fila=fgetcsv($lector)) !== false){
                $pedido = Pedido::ConseguirDatos($fila);
               
                $pedido->CargarPedido();
            }
            fclose($lector);
        }
        $response->getBody()->write("Se cargo correctamente");
        return $response;
    }

    public static function CargarProductos( $request,  $response, $args){
        $archivo= "./csv/productos.csv";

        if(($lector= fopen($archivo, 'r')) !== false){
            $fila= fgetcsv($lector);
            while(($fila=fgetcsv($lector)) !== false){
                $pedido = new Producto($fila[1], $fila[2], $fila[3], $fila[4]);
               
                $pedido->CargarABd();
            }
            fclose($lector);
        }
        $response->getBody()->write("Se cargo correctamente");
        return $response;
    }
   

}
?>