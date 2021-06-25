<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


require_once "AccesoDatos.php";

class Cd 
{
	public $id;
 	public $titulo;
  	public $cantante;
  	public $anio;

//*********************************************************************************************//
/* IMPLEMENTO LAS FUNCIONES PARA SLIM */
//*********************************************************************************************//

	public function TraerTodos(Request $request, Response $response, array $args): Response 
	{
		$todosLosCds = Cd::TraerTodoLosCds();
  
		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($todosLosCds));

		return $newResponse->withHeader('Content-Type', 'application/json');	
	}

	public function TraerUno(Request $request, Response $response, array $args): Response 
	{
     	$id = $args['id'];
    	$elCd = Cd::TraerUnCd($id);

		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($elCd));	

		return $newResponse->withHeader('Content-Type', 'application/json');
	}
	
	public function Agregar(Request $request, Response $response, array $args): Response 
	{
        $arrayDeParametros = $request->getParsedBody();
		$rtaJson = new stdClass();
		$rtaJson->id_agregado = null;
		$rtaJson->mensaje = 'No se pudo agregar el cd.';

		$titulo= $arrayDeParametros['titulo'];
        $cantante= $arrayDeParametros['cantante'];
        $anio= $arrayDeParametros['anio'];
        
        $micd = new Cd();
        $micd->titulo = $titulo;
        $micd->cantante = $cantante;
		$micd->anio = $anio;		

        $rtaJson->id_agregado = $micd->InsertarCd();

//*********************************************************************************************//
//SUBIDA DE ARCHIVOS (SE PUEDEN TENER FUNCIONES DEFINIDAS)
//*********************************************************************************************//
		/*
		$archivos = $request->getUploadedFiles();
        $destino = __DIR__ . "/../fotos/";

        $nombreAnterior = $archivos['foto']->getClientFilename();
        $extension = explode(".", $nombreAnterior);

        $extension = array_reverse($extension);

		$archivos['foto']->moveTo($destino . $id_agregado . $titulo . "." . $extension[0]);
		
		*/
		if($rtaJson->id_agregado != null){
			$rtaJson->mensaje = 'Cd agregado con exito.';
		}

        $response->getBody()->write(json_encode($rtaJson));

      	return $response;
    }
	
	public function Modificar(Request $request, Response $response, array $args): Response
	{
		$obj = json_decode($request->getBody());   
		
		$rtaJson = new stdclass();
		$rtaJson->mensaje = 'No se pudo modificar';
		$rtaJson->id_modificado = null;

	    $micd = new Cd();
	    $micd->id = $obj->id;
	    $micd->titulo = $obj->titulo;
	    $micd->cantante = $obj->cantante;
	    $micd->anio = $obj->anio;

		$resultado = $micd->ModificarCd();
		
		if($resultado != null){
			$rtaJson->id_modificado = $resultado;
			$rtaJson->mensaje = 'Modificado con exito';
		}
		
		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($rtaJson));

		return $newResponse->withHeader('Content-Type', 'application/json');		
	}
	
	public function Eliminar(Request $request, Response $response, array $args): Response 
	{		 
     	$obj = json_decode($request->getBody());
		 
		$cd= new Cd();
		$cd->id = $obj->id;
		 
     	$cantidadDeBorrados = $cd->BorrarCd();

     	$objDeLaRespuesta= new stdclass();
		$objDeLaRespuesta->cantidad = $cantidadDeBorrados;
		
	    if($cantidadDeBorrados>0)
	    {
	    	$objDeLaRespuesta->resultado = "...algo borro!!!";
	    }
	    else
	    {
	    	$objDeLaRespuesta->resultado = "...no borro nada!!!";
		}

		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($objDeLaRespuesta));	

		return $newResponse->withHeader('Content-Type', 'application/json');
    }
	
//*********************************************************************************************//
/* FIN - AGREGO FUNCIONES PARA SLIM */
//*********************************************************************************************//

	public static function TraerTodoLosCds()
	{
		$objetoAccesoDato = AccesoDatos::NuevoObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select id, titel as titulo, interpret as cantante, jahr as anio from cds");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "Cd");		
	}

	public static function TraerUnCd($id) 
	{
		$objetoAccesoDato = AccesoDatos::NuevoObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select id, titel as titulo, interpret as cantante, jahr as anio from cds where id = $id");
		$consulta->execute();
		$cdBuscado= $consulta->fetchObject('cd');
		if($cdBuscado != null){
			return $cdBuscado;		

		}
		else
		{
			return 'No hay un cd con ese id.';
		}
	}

	public function InsertarCd()
	{
		$objetoAccesoDato = AccesoDatos::NuevoObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT into cds (titel,interpret,jahr)values(:titulo,:cantante,:anio)");
		$consulta->bindValue(':titulo',$this->titulo, PDO::PARAM_STR);
		$consulta->bindValue(':anio', $this->anio, PDO::PARAM_INT);
		$consulta->bindValue(':cantante', $this->cantante, PDO::PARAM_STR);
		$consulta->execute();		
		return $objetoAccesoDato->RetornarUltimoIdInsertado();
	}

	public function ModificarCd()
	{
		$objetoAccesoDato = AccesoDatos::NuevoObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
				update cds 
				set titel=:titulo,
				interpret=:cantante,
				jahr=:anio
				WHERE id=:id");
		$consulta->bindValue(':id',$this->id, PDO::PARAM_INT);
		$consulta->bindValue(':titulo',$this->titulo, PDO::PARAM_STR);
		$consulta->bindValue(':anio', $this->anio, PDO::PARAM_INT);
		$consulta->bindValue(':cantante', $this->cantante, PDO::PARAM_STR);
		return $consulta->execute();
	 }

	public function BorrarCd()
	{
	 	$objetoAccesoDato = AccesoDatos::NuevoObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("delete from cds	WHERE id=:id");	
		$consulta->bindValue(':id',$this->id, PDO::PARAM_INT);		
		$consulta->execute();
		return $consulta->rowCount();
	}

}