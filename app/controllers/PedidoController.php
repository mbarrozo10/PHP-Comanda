<?php
require_once './models/Pedido.php'; 
require_once './interfaces/ApiInterface.php';
require_once './models/Usuario.php';
use Slim\Psr7\Response as ResponseMW;

class PedidoController implements ApiInterface{

	public function TraerTodos($request, $response, $args){
        $array= Pedido::TraerTodos("SELECT pedidos.id, productos.nombre as 'Producto' , productos.precio as 'Precio' ,pedidos.cantidad as 'Cantidad' , mesas.codigo as 'CodigoMesa', pedidos.codigo as 'CodigoPedido' , pedidos.estado as 'EstadoPedido', pedidos.tiempoAproximado FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id");

        $retorno= json_encode(array("Todos los Pedidos"=>$array));

        $response->getBody()->write($retorno);

        return $response;
    }
	public function CargarUno($request, $response, $args){
        $parametros= $request->getParsedBody();

        $codigo=Pedido::ComprobarMesa($parametros['mesa']);
        if ($codigo === true){
            $codigo= substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
        }
        $pedido= new Pedido($parametros['producto'], $parametros['cantidad'], $parametros['mesa'],$codigo, "En espera", $parametros['idMesero'] );    
        
        $pedido->CargarPedido(); 

        if(isset($_FILES['foto'])){
            $foto= $_FILES['foto'];
            $ruta= 'imagenes/' . $codigo . "-" . date("y-m-d") . ".png";
            move_uploaded_file($foto['tmp_name'],$ruta);
        }
        
        
        $retorno = json_encode(array("Pedido Realizado correctamente con el codigo:" => $codigo));

        $response->getBody()->write($retorno);

        return $response;
    }

    public function TraerFiltrado($request, $response, $args) {
        $retorno= json_encode(array("Algo Salio malo" => 404));
        if(isset($args['id'])){
        $usuario= Usuario::obtenerUsuario($args['id']);
        switch ($usuario->tipo) {
            case 'cocinero':
                $array= Pedido::TraerTodos("SELECT pedidos.id, productos.nombre as 'Producto' , productos.precio as 'Precio' ,pedidos.cantidad as 'Cantidad' , mesas.codigo as 'CodigoMesa', pedidos.codigo as 'CodigoPedido' , pedidos.estado as 'EstadoPedido', pedidos.tiempoAproximado FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id where productos.tipo = 'comida' and pedidos.estado != 'Cobrado'");
                break;
            case 'bartender' :
                $array= Pedido::TraerTodos("SELECT pedidos.id, productos.nombre as 'Producto' , productos.precio as 'Precio' ,pedidos.cantidad as 'Cantidad' , mesas.codigo as 'CodigoMesa', pedidos.codigo as 'CodigoPedido' , pedidos.estado as 'EstadoPedido', pedidos.tiempoAproximado FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id where productos.tipo = 'bebida' and pedidos.estado != 'Cobrado'");
                break;
            case 'cervecero':
                $array= Pedido::TraerTodos("SELECT pedidos.id, productos.nombre as 'Producto' , productos.precio as 'Precio' ,pedidos.cantidad as 'Cantidad' , mesas.codigo as 'CodigoMesa', pedidos.codigo as 'CodigoPedido' , pedidos.estado as 'EstadoPedido', pedidos.tiempoAproximado FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id where productos.tipo = 'cerveza' and pedidos.estado != 'Cobrado'");
                break;
            case 'socio':
                $array= Pedido::TraerTodos("SELECT pedidos.id, productos.nombre as 'Producto' , productos.precio as 'Precio' ,pedidos.cantidad as 'Cantidad' , mesas.codigo as 'CodigoMesa', pedidos.codigo as 'CodigoPedido' , pedidos.estado as 'EstadoPedido', pedidos.tiempoAproximado FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id");
                break;
            case 'mozo':
                $array= Pedido::TraerTodos("SELECT pedidos.id, productos.nombre as 'Producto' , productos.precio as 'Precio' ,pedidos.cantidad as 'Cantidad' , mesas.codigo as 'CodigoMesa', pedidos.codigo as 'CodigoPedido' , pedidos.estado as 'EstadoPedido', pedidos.tiempoAproximado FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id where pedidos.estado = 'Listo'");
                break;
            default:
        }
        
    }
    $retorno= json_encode(array("Los pedidos de $usuario->usuario <br/>"=>$array));
    $response->getBody()->write($retorno);
    
    return $response;
    }

    public function AtenderPedido($request, $response, $args){
        $parametros= $request->getParsedBody();

        $usuario= Usuario::obtenerUsuario($parametros['idUsuario']);
        Pedido::modificarPedido($parametros['tiempo'], $parametros['estado'], $parametros['idPedido']);

        $retorno= json_encode(array("Se actualizo el pedido, tiempo aproximado:" => $parametros['tiempo']));

        $response->getBody()->write($retorno);

        return $response;

    }

    public function VerificarStock($request, $handler) : ResponseMW{
        $response= new ResponseMW();
        $parametros= $request->getParsedBody();
        if($request->getMethod() == 'POST'){
            $retorno= Pedido::TraerTodos("SELECT cantidad FROM productos where id= {$parametros['producto']}");
            $cantidad= $retorno[0];
            if($cantidad['cantidad'] >= $parametros['cantidad']){
                Pedido::DescontarStock($parametros['producto'],$cantidad['cantidad']- $parametros['cantidad']);
                $response= $handler->handle($request);
            }  
            else $response->getBody()->write("No hay stock ");
        }else{
            $response= $handler->handle($request);
        }
        return $response;
    }

    public function Cobrar($request, $response){
        $parametros= $request->getParsedBody();
        
        $pedidos= Pedido::TraerTodos("SELECT pedidos.id, productos.precio as precioProductos, pedidos.cantidad as cantidad, pedidos.idMesa from pedidos
        inner join productos on pedidos.idProducto= productos.id inner join mesas on mesas.id = pedidos.idMesa where pedidos.codigo= '{$parametros['codigo']}'");
        $totalApagar=0;
        $idMesa=0;
        foreach($pedidos as $pedido){
            $totalApagar+= $pedido['precioProductos']* $pedido['cantidad'];
            Pedido::ConsultaActualizar($pedido['id'], "UPDATE pedidos SET estado = 'Cobrado' WHERE id = :id");
            $idMesa= $pedido['idMesa'];
        }
        Pedido::ConsultaActualizar($idMesa, "UPDATE mesas SET estado = 'Vacia' WHERE id = :id");
        $response->getBody()->write("Total a pagar: ". $totalApagar);

        return $response;
    }
}
?>