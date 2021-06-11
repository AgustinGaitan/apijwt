<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


require_once "accesoDatos.php";


class Usuario
{
    public $id;
    public $nombre;
    public $apellido;
    public $correo;
    public $clave;
    public $foto;
    public $id_perfil;

    /*
    public function ToJSON()
    {
        $objJson = new stdClass();
        $objJson->nombre = $this->nombre;
        $objJson->correo = $this->correo;
        $objJson->clave = $this->clave;

        return json_encode($objJson);
        
    }
    public static function TraerTodosJSON()
    {
        $path = "archivos/usuarios.json";
        $archivo = fopen($path, "r");
        $usuarios= "";

          
            $cadena = fread($archivo,filesize($path));
            $usuarios = json_decode($cadena);
       
        fclose($archivo);
         

        return $usuarios;
    }*/
   
    public static function TraerTodosLosUsuarios()
    {      
        $objAccesoDatos = AccesoDatos::NuevoObjetoAcceso();
        $consulta = $objAccesoDatos->RetornarConsulta("SELECT * FROM usuarios");

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS,"Usuario");
    }

    public function TraerTodos(Request $request, Response $response, array $args) : Response
    {
        $todosLosUsuarios = Usuario::TraerTodosLosUsuarios();

        $newResponse = $response->withStatus(200,"OK");
        $newResponse->getBody()->write(json_encode($todosLosUsuarios));

        return $newResponse->withHeader('Content-Type','application/json');
    }

    public static function TraerUno($id)
    {
        $objAccesoDatos = AccesoDatos::NuevoObjetoAcceso();

        $consulta = $objAccesoDatos->RetornarConsulta("SELECT * FROM usuarios WHERE id = $id");
    
        $consulta->execute();
        
        
        $usuario = $consulta->fetchObject('Usuario');

        return $usuario;
       
    }

    public function TraerUnUsuario(Request $request, Response $response, array $args) : Response
    {
        $id = $args['id'];
        $usuarioTraido = Usuario::TraerUno($id);

        $newResponse = $response->withStatus(200,"OK");
        $newResponse->getBody()->write(json_encode($usuarioTraido));

        return $newResponse->withHeader('Content-Type','application/json');
    }

    public function AgregarUnUsuario(Request $request, Response $response, array $args) : Response
    {
        $rtaJson = new stdClass();
        $rtaJson->rta= "Error al eliminar.";
        $arrayDeParametros = $request->getParsedBody();

        $nombre = $arrayDeParametros['nombre'];
        $apellido = $arrayDeParametros['apellido'];
        $correo = $arrayDeParametros['correo'];
        $clave = $arrayDeParametros['clave'];
        $id_perfil = $arrayDeParametros['id_perfil'];
     

        $usuario = new Usuario();
        $usuario->nombre = $nombre;
        $usuario->apellido = $apellido;
        $usuario->correo = $correo;
        $usuario->clave = $clave;
        $usuario->id_perfil = $id_perfil;
        
        

        $archivos = $request->getUploadedFiles();
        $destino = "../fotos/";

        $nombreAnterior = $archivos['foto']->getClientFilename();
        $extension = explode(".", $nombreAnterior);

        $extension = array_reverse($extension);
        $nombreFinal = $destino .  $nombre . "." . $extension[0];
		$archivos['foto']->moveTo($destino .  $nombre . "." . $extension[0]);
        $usuario->foto = $nombreFinal;
        if($usuario->Agregar())
        {
            $rtaJson->rta = "Exito al agregar al usuario.";
            $newResponse = $response->withStatus(200, "OK");
            $newResponse->getBody()->write(json_encode($rtaJson));	

           
        }
		
        return $newResponse->withHeader('Content-Type', 'application/json');

  
    }

    public function Agregar()
    {
        $objAccesoDatos = AccesoDatos::NuevoObjetoAcceso();
        $consulta = $objAccesoDatos->RetornarConsulta("INSERT INTO usuarios (nombre,apellido,correo,clave,foto,id_perfil) VALUES (:nombre,:apellido,:correo,:clave,:foto,:id_perfil)");
        

        $consulta->bindValue(":nombre", $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(":apellido", $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(":correo", $this->correo, PDO::PARAM_STR);
        $consulta->bindValue(":clave", $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(":id_perfil", $this->id_perfil, PDO::PARAM_INT);
        $consulta->bindValue(":foto", $this->foto, PDO::PARAM_STR);

        $consulta->execute();

        if($consulta->rowCount() == 1)
        {
            return true;
        }
        
        return false;
 
    }

    public function ModificarUnUsuario(Request $request, Response $response, array $args) : Response
    {
        $objJson = json_decode(($args["cadenaJson"]));

        $usuario = new Usuario();
        $usuario->id = $objJson->id;
        $usuario->nombre = $objJson->nombre;
        $usuario->apellido = $objJson->apellido;
        $usuario->correo = $objJson->correo;
        $usuario->clave = $objJson->clave;
        $usuario->id_perfil = $objJson->id_perfil;

        $objDelaRespuesta = new stdclass();
		$objDelaRespuesta->rta = "Fallo el modificar.";

        if($usuario->Modificar())
        {   
            $objDelaRespuesta->rta = "Exito al modificar.";
            $newResponse = $response->withStatus(200, "OK");
            $newResponse->getBody()->write(json_encode($objDelaRespuesta));
        }
        
        return $newResponse->withHeader('Content-Type', 'application/json');


    }

    public function Modificar()
    {
       $objAccesoDatos = AccesoDatos::NuevoObjetoAcceso();

       $consulta = $objAccesoDatos->RetornarConsulta("UPDATE usuarios SET correo = :correo,
                                                                           nombre = :nombre,
                                                                           clave = :clave,
                                                                           apellido=:apellido,
                                                                           id_perfil = :id_perfil
                                                     WHERE id = :id");
                                                    
       $consulta->bindValue(":correo", $this->correo, PDO::PARAM_STR);
       $consulta->bindValue(":nombre", $this->nombre, PDO::PARAM_STR);
       $consulta->bindValue(":apellido", $this->apellido, PDO::PARAM_STR);
       $consulta->bindValue(":id_perfil", $this->id_perfil, PDO::PARAM_INT);
       $consulta->bindValue(":clave", $this->clave, PDO::PARAM_STR);
       $consulta->bindValue(":id", $this->id, PDO::PARAM_INT);

       try
       {
            $consulta->execute();
            if($consulta->rowCount() > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
       }
       catch(PDOException $e)
       {
           echo "Error. " . $e->getMessage();

           return false;
       }
    }

    public function EliminarUnUsuario(Request $request, Response $response, array $args) : Response
    {   
        $rtaJson = new stdClass();
        $rtaJson->rta= "Error al eliminar.";

        $id = $args['id'];
        $id = intval($id);
        $usuario = new Usuario();
        $usuario = Usuario::TraerUno($id);
        unlink($usuario->foto);

		if($usuario->Eliminar($id))
        {

            $rtaJson->rta= "Exito al eliminar.";
            
        }

        $newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($rtaJson));	

		return $newResponse->withHeader('Content-Type', 'application/json');
        
    }


    public static function Eliminar($idParams)
    {
        $objAccesoDatos = AccesoDatos::NuevoObjetoAcceso();

        $consulta = $objAccesoDatos->RetornarConsulta("DELETE FROM usuarios WHERE id = :id");

        $consulta->bindValue(":id", $idParams, PDO::PARAM_INT);

        try
        {
            $consulta->execute();
            if($consulta->rowCount()>0){
                return true;
            }
            else
            {
                return false;
            }
        }
        catch(PDOException $e)
        {
            echo "Error. " . $e->getMessage();
        }

    }
    public function Login(Request $request, Response $response, array $args) : Response 
    {
        $correo = $args['correo'];
        $clave = $args['clave'];

        $newResponse = $response->withStatus(404, "ERROR");
        
        $rtaJson = Usuario::VerificarUsuario($correo,$clave);
        $rtaJson = json_decode($rtaJson);

        if($rtaJson->exito == true){
            
            $newResponse = $response->withStatus(200, "OK");
        }
		        
        $newResponse->getBody()->write(json_encode($rtaJson));
        	
       

		return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public static function VerificarUsuario($correo, $clave){

        session_start();
        $_SESSION['correo'] = $correo;
        $_SESSION['clave'] = $clave;
        
        $rtaJson = new stdClass();
        $rtaJson->rta= "Error al loguear.";
        $rtaJson->exito= false;
        $usuarios = Usuario::TraerTodosLosUsuarios();

        foreach($usuarios as $item)
        {
            
            if($item->correo == $_SESSION['correo'] && $item->clave == $_SESSION['clave'])
            {
                
                $rtaJson->rta= "Logueado.";
                $rtaJson->exito= true;
                     
            }
        }

        return json_encode($rtaJson);

    }
}



?>