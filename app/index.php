<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './middlewares/Logger.php';
require_once './controllers/GuardarController.php';
require_once './controllers/CargarController.php';
require_once './controllers/ClientesControllers.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

$app->setBasePath('/Tp_Programacion3/app');
$app->addErrorMiddleware(true, true, true);

$app->addBodyParsingMiddleware();

// Routes
$app->group('/login', function (RouteCollectorProxy $group){
  $group->post('[/]', \Logger::class . ':Login');
});

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
  })->add(\Logger::class . ':GenerarToken')->add(\Logger::class . ':VerificarToken');

$app->group('/productos', function (RouteCollectorProxy $group){
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->post('[/]', \ProductoController::class . ':CargarUno');
})->add(\Logger::class . ':VerificarToken');

$app->group('/mesa', function (RouteCollectorProxy $group){
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->post('[/]', \MesaController::class . ':CargarUno');
  $group->put('/actualizar', \MesaController::class . ':Actualizar');
  $group->put('[/]', \MesaController::class . ':CerrarMesa');
})->add(\Logger::class . ':VerificarToken');

$app->group('/pedido', function (RouteCollectorProxy $group){
  $group->post('[/]', \PedidoController::class . ':CargarUno')->add(\PedidoController::class . ':VerificarStock');
  $group->put('/cobrar', \PedidoController::class . ':Cobrar');
  $group->get('/{id}', \PedidoController::class . ':TraerFiltrado');
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->put('/modificar', \PedidoController::class . ':AtenderPedido');
})->add(\Logger::class . ':VerificarToken');;

$app->group('/cliente', function (RouteCollectorProxy $group){
  $group->post('[/]', \ClientesController::class . ':ConsultaCliente');
  $group->get('[/]', \ClientesController::class . ':TopComentarios');
  $group->get('/mesas', \ClientesController::class . ':TopMesa');
});

$app->group('/guardar', function (RouteCollectorProxy $group){
  $group->post('/usuarios', \GuardarController::class . ':GuardarUsuarios');
  $group->post('/mesas', \GuardarController::class . ':GuardarMesas');
  $group->post('/pedidos', \GuardarController::class . ':GuardarPedidos');
  $group->post('/productos', \GuardarController::class . ':GuardarProductos');
})->add(\Logger::class . ':VerificarToken');


$app->group('/cargar', function (RouteCollectorProxy $group){
  $group->post('/usuarios', \CargarController::class . ':CargarUsuarios');
  $group->post('/mesas', \CargarController::class . ':CargarMesas');
  $group->post('/pedidos', \CargarController::class . ':CargarPedidos');
  $group->post('/productos', \CargarController::class . ':CargarProductos');
})->add(\Logger::class . ':VerificarToken');

$app->run();
