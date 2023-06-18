<?php

class Mesa{

    public $id;
    public $codigo;
    public $estado;

    public function ComprobarCodigo($codigo){ 
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas where codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo);
        $consulta->execute();

        if($consulta->rowCount() > 0)return false;
        else return true;
    }

    public function CargarMesa(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo, estado) VALUES (:codigo, :estado)");
        $consulta->bindValue(':codigo', $this->codigo);
        $consulta->bindValue(':estado', $this->estado);
        $consulta->execute();
    }

    public static function TraerMesas(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        $mesas= array();

        while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
            $mesa= new Mesa();
            $mesa->id=$fila['id'];
            $mesa->codigo=$fila['codigo'];
            $mesa->fecha=$fila['estado'];
            array_push($mesas,$mesa);
        }
        return $mesas;
    }

    public static function ActualizarMesa($id){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT pedidos.estado FROM pedidos INNER JOIN mesas on pedidos.idMesa= mesas.id where pedidos.idMesa = '$id'");
        $consulta->execute();
        $flag=false;
        
        while($mesa= $consulta->fetch(PDO::FETCH_ASSOC)){
            if($mesa['estado']!= "Listo"){
                $flag= true;
                break;
            }
        }
        if(!$flag) {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado= 'Comiendo' where id = '$id'");
            $consulta->execute();
            return true;
        }
        return false;
    }
}
?>