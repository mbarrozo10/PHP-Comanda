<?php
require_once ('./models/Jws.php');
require_once('./models/Usuario.php');
use Slim\Psr7\Response as ResponseMW;

class Logger
{
    public function GenerarToken($request, $handler): ResponseMW {
		$parametros= $request->getParsedBody();
        $response= new ResponseMW();
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
		 return $response;   
	}

    public function VerificarToken($request, $handler): ResponseMW {
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";
		$parametros= $request->getParsedBody();
		$response= new ResponseMW();
		if($request->getMethod()=="GET")
		{
		 $response->getBody()->write('<p>NO necesita credenciales para los get </p>');
         $response= $handler->handle($request);
		}
		else{
		if(!isset($parametros['token'])){
			$usuario= Usuario::obtenerUsuario($parametros['id']);
			$datos = array('usuario' => $usuario->usuario,'perfil' => $usuario->tipo);
			$token= Autenticador::CrearToken($datos);
		}else $token= $parametros['token'];
		// $objDelaRespuesta->esValido=true; 

		try 
		{
			Autenticador::verificarToken($token);
			$objDelaRespuesta->esValido=true;      
		}
		catch (Exception $e) {      
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;     
		}

		if($objDelaRespuesta->esValido)
		{						
			$payload=Autenticador::ObtenerData($token);
			//var_dump($payload);
			if($payload->perfil=="socio")
			{
				$response = $handler->handle($request);
			}		           	
			else
			{	
				$objDelaRespuesta->respuesta="Solo administradores";
			}		          
		}    
		else
		{
			//   $handler->getBody()->write('<p>no tenes habilitado el ingreso</p>');
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
			$objDelaRespuesta->elToken=$token;

		}  	
	}				
	$response->getBody()->write($objDelaRespuesta->respuesta);
	return $response;				
	}

}