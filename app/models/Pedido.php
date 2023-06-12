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
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (idProducto, cantidad, idMesa, codigo, estado, tiempoAproximado, idMesero) 
        VALUES (:idProducto, :cantidad, :idMesa, :codigo, :estado, :tiempoAproximado, :idMesero)");
        $consulta->bindValue(':idProducto', $this->idProducto);
        $consulta->bindValue(':cantidad', $this->cantidad);
        $consulta->bindValue(':idMesa', $this->idMesa);
        $consulta->bindValue(':codigo', $this->codigo);
        $consulta->bindValue(':estado', $this->estado);
        $consulta->bindValue(':tiempoAproximado', $this->tiempo);
        $consulta->bindValue(':idMesero', $this->idMesero);
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
            if($fila['estado'] == "Libre"){
                $consulta= $objAccesoDatos->prepararConsulta("UPDATE mesas set estado = 'con cliente esperando pedido' where id= :id"); 
                $consulta->bindValue(":id",$idMesa);
                $consulta->execute();
                return true;
            }else {
                $consulta= $objAccesoDatos->prepararConsulta("Select codigo from pedidos where idMesa= :id"); 
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
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET tiempoAproximado = :usuario, estado = :clave WHERE id = :id");
        $consulta->bindValue(':usuario', $tiempo);
        $consulta->bindValue(':clave', $estado);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

}
?>