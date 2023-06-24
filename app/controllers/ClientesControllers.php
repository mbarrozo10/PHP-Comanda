<?php

class ClientesController
{
    public function ConsultaCliente($request, $response){
        $args= $request->getParsedBody();
        $usuario= new stdClass();
        $usuario->usuario="Cliente";
        $codigoMesa= $args['codigoMesa'];
        $codigoPedido= $args['codigoPedido'];
        $array= Pedido::TraerTodos("SELECT pedidos.id, productos.nombre as 'Producto' , productos.precio as 'Precio' ,pedidos.cantidad as 'Cantidad' , mesas.codigo as 'CodigoMesa', pedidos.codigo as 'CodigoPedido' , pedidos.estado as 'EstadoPedido', pedidos.tiempoAproximado FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id where mesas.codigo= '{$codigoMesa}' and pedidos.codigo= '{$codigoPedido}'");
        $tiempoEsperado= Pedido::TraerTodos("SELECT MAX(pedidos.tiempoAproximado) as 'tiempoAproximado' FROM pedidos inner join productos on pedidos.idProducto= productos.id inner join mesas on pedidos.idMesa = mesas.id where mesas.codigo= '{$codigoMesa}' and pedidos.codigo= '{$codigoPedido}'");
        $retorno= json_encode($tiempoEsperado);
        $response->getBody()->write($retorno);

        self::LlenarEncuesta($args);

        $retorno= json_encode(array("Los pedidos de $usuario->usuario <br/>"=>$array ));
        $response->getBody()->write($retorno . " <br/> Encuesta llenada correctamente.");
        
        return $response;
    }

    private function LlenarEncuesta($args){
        $obj= AccesoDatos::obtenerInstancia();
        $consulta = $obj->prepararConsulta("INSERT INTO encuestas (notaGeneral, notaMesa, notaMozo, notaCocinero, comentario, codigoMesa, codigoPedido) VALUES(:notaG, :notaMe, :notaMozo, :notaCocinero, :comentario, :codigoMesa, :codigoPedido)");
        $consulta->bindValue(':notaG',$args['notaGeneral']);
        $consulta->bindValue(':notaMe',$args['notaMesa']);
        $consulta->bindValue(':notaCocinero',$args['notaCocinero']);
        $consulta->bindValue(':comentario',$args['comentario']);
        $consulta->bindValue(':notaMozo',$args['notaMozo']);
        $consulta->bindValue(':codigoPedido',$args['codigoPedido']);
        $consulta->bindValue(':codigoMesa',$args['codigoMesa']);
        $consulta->execute();
    }

    public function TopComentarios($request, $response){
        $obj= AccesoDatos::obtenerInstancia();
        $consulta = $obj->prepararConsulta("SELECT * FROM encuestas ORDER BY notaGeneral DESC LIMIT 5");
        $consulta->execute();

        $array= $consulta->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($array));

        return $response;
    }

    public function TopMesa($request, $response){
        $obj= AccesoDatos::obtenerInstancia();
        $consulta = $obj->prepararConsulta("SELECT  codigoMesa, COUNT(*) as cantidad FROM encuestas ORDER BY cantidad DESC LIMIT 1;");
        $consulta->execute();

        $array= $consulta->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode(array("La mesa mas usada es: " => $array)));

        return $response;
    }
}

?>