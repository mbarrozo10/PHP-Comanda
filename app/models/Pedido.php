<?php
class Pedido{
    public $id;
    public $idProducto;
    public $cantidad;
    public $idMesa;
    public $codigo;
    public $estado;
    public $tiempo;
    public $idMesero;

    public function __construct($idProducto, $cantidad, $idMesa, $codigo, $estado, $idMesero, $tiempo=0) {
        $this->idProducto = $idProducto;
        $this->idMesa = $idMesa;
        $this->codigo = $codigo;
        $this->estado = $estado;
        $this->tiempo = $tiempo;
        $this->cantidad = $cantidad;
        $this->idMesero = $idMesero;
    }

    public function CargarPedido(){

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (idProducto, cantidad, idMesa, codigo, estado, tiempoAproximado, idMesero, horarioCargado) 
        VALUES (:idProducto, :cantidad, :idMesa, :codigo, :estado, :tiempoAproximado, :idMesero, :hora)");
        $consulta->bindValue(':idProducto', $this->idProducto);
        $consulta->bindValue(':cantidad', $this->cantidad);
        $consulta->bindValue(':idMesa', $this->idMesa);
        $consulta->bindValue(':codigo', $this->codigo);
        $consulta->bindValue(':estado', $this->estado);
        $consulta->bindValue(':tiempoAproximado', $this->tiempo);
        $consulta->bindValue(':idMesero', $this->idMesero);
        $hora= date("H:i");
        $consulta->bindValue(':hora', $hora);
        $consulta->execute();
    }

    public static function TraerTodos($pedido){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta($pedido);
        $consulta->execute();
        $pedidos= array();
            while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
                array_push($pedidos,$fila);
            }        

        return $pedidos;
    }

    static function ComprobarMesa($idMesa){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas where id = :codigo");
        $consulta->bindValue(':codigo', $idMesa);
        $consulta->execute();

        if($consulta->rowCount() > 0){
            $fila = $consulta->fetch(PDO::FETCH_ASSOC);
            if($fila['estado'] == "Vacia"){
                $consulta= $objAccesoDatos->prepararConsulta("UPDATE mesas set estado = 'con cliente esperando pedido' where id= :id"); 
                $consulta->bindValue(":id",$idMesa);
                $consulta->execute();
                return true;
            }else {
                $consulta= $objAccesoDatos->prepararConsulta("Select codigo from pedidos where idMesa= :id and estado != 'Cobrado'"); 
                $consulta->bindValue(":id",$idMesa);
                $consulta->execute();
                $dato= $consulta->fetch(PDO::FETCH_ASSOC);
                return $dato['codigo'];
            }
        }else return false;
    }

    public static function modificarPedido($tiempo, $estado, $id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        switch($estado){
            case 'En preparacion':
                $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET tiempoAproximado = :usuario, estado = :clave WHERE id = :id");
                $consulta->bindValue(':usuario', $tiempo);
                $consulta->bindValue(':clave', $estado);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                break;
            case 'Listo':
                $consulta = $objAccesoDato->prepararConsulta("SELECT horarioCargado,tiempoAproximado FROM pedidos WHERE id = :id");
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno=$consulta->fetch(PDO::FETCH_ASSOC);
                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $horaActual= strtotime("now");
                $horaCargado= strtotime($retorno['horarioCargado']);
                $tiempoPretendido = strtotime($retorno['tiempoAproximado']);
                $diferencia = $horaCargado - $horaActual;
                $minutos = floor(($diferencia % 3600) / 60);
                $minutosAproximado= floor(($tiempoPretendido %3600)/60);
                $resultado = $minutosAproximado + $minutos;
               
                $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET tiempoAproximado = :usuario, estado = :clave WHERE id = :id");
                $consulta->bindValue(':usuario', $tiempo);
                $consulta->bindValue(':clave', $estado);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                if($resultado>= 0) {
                    $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET ATiempo = true WHERE id = :id");
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                }
                else {
                    $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET ATiempo = false WHERE id = :id");
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);}
                break;
        }
       
        $consulta->execute();
    }
    public static function DescontarStock($id,$cantidad)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET cantidad = :usuario WHERE id = :id");
        $consulta->bindValue(':usuario', $cantidad);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function ConsultaActualizar($id, $pedido){
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta($pedido);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function ConseguirDatos($fila){
        $objAccesoDatos= AccesoDatos::obtenerInstancia();
        echo $fila[4];
        // $consulta = $objAccesoDatos->prepararConsulta("SELECT id as idProducto from productos WHERE nombre = '{$fila[1]}' cross join ( SELECT id as idMesa FROM mesas WHERE codigo = '{$fila[4]}') ");
        $consulta = $objAccesoDatos->prepararConsulta("SELECT G.idProducto, I.idMesa from (select id idProducto from productos where nombre= '{$fila[1]}') G cross join ( SELECT id idMesa FROM mesas WHERE codigo = '{$fila[4]}') I ");
        $consulta->execute();
        $dato = $consulta->fetch(PDO::FETCH_ASSOC);
        $pedido = new Pedido($dato['idProducto'],$fila[3],$dato['idMesa'],$fila[5],$fila[6],$fila[7]);
        return $pedido;
    }

}
?>