<?php

require_once 'usuario.php';
require_once 'autentificadora.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;


    class Verificadora{


        public function VerificarUsuario(Request $request ,Response $response, array $args) : Response {

            $rtaJson = new stdClass();
            $rtaJson->jwt = null;
            $rtaJson->status = 403;

            $arrayDatos = $request->getParsedBody();

            $newResponse = $response->withStatus(403);    
            $json = json_decode(($arrayDatos['obj_json']));
            $usuarioVerificado = Verificadora::ExisteUsuario($json);
    
            if($usuarioVerificado != null)
            {

                $rtaJson->jwt = Autentificadora::CrearJWT(json_encode($usuarioVerificado));
                
                $newResponse = $response->withStatus(200);
                $rtaJson->status = 200;
            }
            
            
            $newResponse->getBody()->write(json_encode($rtaJson));

            return $newResponse->withHeader('Content-Type' , 'application/json');

        }

        static function ExisteUsuario ($obj){

            $objAccesoDatos = AccesoDatos::NuevoObjetoAcceso();


            $consulta = $objAccesoDatos->RetornarConsulta("SELECT * FROM usuarios WHERE correo = :correo AND clave = :clave");
        
            $consulta->bindValue(':correo', $obj->correo, PDO::PARAM_STR);
            $consulta->bindValue(':clave', $obj->clave, PDO::PARAM_STR);

            try
            { 
                $consulta->execute();
                return $consulta->fetchObject('Usuario');

            }
            catch(PDOException $e)
            {
                echo $e->getMessage();
            }
            
            
            

        }




    
    }

?>
