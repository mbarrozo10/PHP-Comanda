<?php

    require_once ('./models/Usuario.php');
    require_once ('./models/Mesa.php');
    require_once('./models/Pedido.php');
    require_once('./models/Producto.php');
    class GuardarController{
        public function GuardarUsuarios($request, $response){
            try{
            $usuarios= Usuario::obtenerTodos();
            $archivo= fopen('./csv/usuarios.csv', 'w');

            $datos= array('id', 'nombre','tipo' );
            fputcsv($archivo, $datos);

            foreach($usuarios as $users){
                $fila= get_object_vars( $users );
                fputcsv($archivo, $fila);
            }

            $response->getBody()->write("Archivo guardado correctamente");
            }catch(Exception $e){
                $response->getBody()->write(e->getMessage);
            }finally{
                fclose($archivo);
            }

            return $response;
        }

        public function GuardarMesas($request, $response){
            try{
                $usuarios= Mesa::TraerMesas();
                $archivo= fopen('./csv/mesas.csv', 'w');
    
                $datos= array('id', 'codigo','estadoDelMomento' );
                fputcsv($archivo, $datos);
    
                foreach($usuarios as $users){
                    $fila= get_object_vars( $users );
                    fputcsv($archivo, $fila);
                }
    
                $response->getBody()->write("Archivo guardado correctamente");
                }catch(Exception $e){
                    $response->getBody()->write(e->getMessage);
                }finally{
                    fclose($archivo);
                }
    
                return $response;
            }
        public function GuardarPedidos($request, $response){
            try{
                $usuarios= Pedido::TraerTodos("SELECT pedidos.id, productos.nombre as 'Producto' , productos.precio as 'Precio' ,pedidos.cantidad as 'Cantidad' , mesas.codigo as 'CodigoMesa', pedidos.codigo as 'CodigoPedido' , pedidos.estado as 'EstadoPedido' FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id");
                $archivo= fopen('./csv/pedidos.csv', 'w');
    
                $datos= array('id', 'nombreProducto','precio', 'cantidad', 'codigoMesa' , 'codigoPedido', 'estadoPedido',);
                fputcsv($archivo, $datos);
    
                foreach($usuarios as $users){
                    //$fila= get_object_vars( $users );
                    fputcsv($archivo, $users);
                }
    
                $response->getBody()->write("Archivo guardado correctamente");
                }catch(Exception $e){
                    $response->getBody()->write(e->getMessage);
                }finally{
                    fclose($archivo);
                }
    
            return $response;
        }
        public function GuardarProductos($request, $response){
            try{
                $usuarios= Producto::TraerTodos();

                $archivo= fopen('./csv/productos.csv', 'w');

                $datos= array('id', 'nombre','precio', 'tipo', 'cantidad');
                fputcsv($archivo, $datos);

                foreach($usuarios as $users){
                    $fila= get_object_vars( $users );
                    fputcsv($archivo, $fila);
                }
                $response->getBody()->write("Archivo guardado correctamente");
                }catch(Exception $e){
                    $response->getBody()->write(e->getMessage);
                }finally{
                    fclose($archivo);
                }
    
            return $response;
        }
    }
?>