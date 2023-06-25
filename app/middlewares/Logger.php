<?php
require_once ('./models/Jws.php');
require_once('./models/Usuario.php');
use Slim\Psr7\Response as ResponseMW;

class Logger
{
    public function GenerarToken($request, $handler): ResponseMW {
		$parametros= $request->getParsedBody();
		$response= new ResponseMW();

		if($request->getMethod()=="GET")
		{
		 $response->getBody()->write('<p>NO necesita credenciales para los get </p>');
         $response= $handler->handle($request);
		}else{
			$parametros = $request->getParsedBody();
			$usuario= new Usuario();
			$usuario->usuario= $parametros['usuario'];
			$usuario->clave= $parametros['clave'];
			$usuario->tipo= $parametros['tipo'];
			$datos = array('usuario' => $usuario->usuario,'perfil' => $usuario->tipo);
			
			$token= Autenticador::CrearToken($datos);
					
		//$response= new ResponseMW();
			echo "Token: " .  $token;
			//$response->getBody()->write($token);
			$response= $handler->handle($request);
		}
		return $response;   
	}

	public function Login($request, $handler) : ResponseMW{
		$parametros= $request->getParsedBody();
		$response = new ResponseMW();
		if(isset($parametros['usuario'])){
			$usuario= Usuario::obtenerUsuario($parametros['usuario']);
			if($usuario){
				$datos = array('usuario' => $usuario->usuario,'perfil' => $usuario->tipo);
				$token= Autenticador::CrearToken($datos);
				$response->getBody()->write("Login exitoso, su token es: \n" . $token);
			}else{
				$response->getBody()->write("No existe ninguna cuenta con ese usuario.");
			}
		}else $response->getBody()->write("No ingreso el usuario.");

		return $response;
	}

    public function VerificarToken($request, $handler): ResponseMW {
		$objDelaRespuesta= new stdclass();
		$seccion= self::Prueba($_SERVER['REQUEST_URI']);
		$objDelaRespuesta->respuesta="";
		$parametros= $request->getParsedBody();
		$response= new ResponseMW();
		$token="";
		try 
		{
			if($request->getMethod()=="GET")
			{
			$response->getBody()->write('<p>NO necesita credenciales para los get </p>');
			$response= $handler->handle($request);
			return $response;
			}
			else{
			// if(!isset($parametros['token'])){
			// 		// $usuario= Usuario::obtenerUsuario($parametros['id']);
			// 		// if($usuario== false) throw new Exception("Error");
			// 		// $datos = array('usuario' => $usuario->usuario,'perfil' => $usuario->tipo);
			// 		// $token= Autenticador::CrearToken($datos);				
			// }else
			if(isset($parametros['token'])){
				$token= $parametros['token'];
				Autenticador::verificarToken($token);
				$objDelaRespuesta->esValido=true;   
			}  else $objDelaRespuesta->esValido=false;
	}

		}
		catch (Exception $e) {      
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;     
		}

		if($objDelaRespuesta->esValido)
		{		
			$payload=Autenticador::ObtenerData($token);
			switch($seccion)	{
				case 'mesa':
					if($payload->perfil=="socio" || $payload->perfil == "mozo")
						$response = $handler->handle($request);           	
					else	
						$objDelaRespuesta->respuesta="Solo administradores";
				case 'pedido':
						$response = $handler->handle($request);	           	
				default:
				if($payload->perfil=="socio")
				{
					$response = $handler->handle($request);
				}		           	
				else
				{	
					$objDelaRespuesta->respuesta="Solo administradores";
				}
				break;
			}			
			//var_dump($payload);
				          
		}    
		else
		{
			//   $handler->getBody()->write('<p>no tenes habilitado el ingreso</p>');
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
			$objDelaRespuesta->elToken=$token;

		}  				
	$response->getBody()->write($objDelaRespuesta->respuesta);
	return $response;				
	}

	private function Prueba($string){
		$array=explode("/",$string);

		return $array[3];
	}

}