<?php
class Producto{
    public $id;
    public $nombre;
    public $precio;
    public $tipo;

    public function __construct( $nombre, $precio, $tipo){
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->tipo = $tipo;
    }

    public function CargarABd(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, precio, tipo) VALUES (:nombre, :precio, :tipo)");
        $consulta->bindValue(':nombre', $this->nombre);
        $consulta->bindValue(':precio', $this->precio);
        $consulta->bindValue(':tipo', $this->tipo);
        $consulta->execute();
    }

    public static function TraerTodos(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();
        $productos= array();

        while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
            $id= $fila['id'];
            $nombre= $fila['nombre'];
            $precio= $fila['precio'];
            $tipo= $fila['tipo'];
            
            $producto= new Producto($nombre,$precio,$tipo);

            $producto->id= $id;
            array_push($productos,$producto);
        }
        return $productos;
    }

}
?>