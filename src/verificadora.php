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

        public static function ExisteUsuario ($obj){

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

        public function ValidarParametrosUsuario(Request $request, RequestHandler $handler) :  ResponseMW {

            $params = $request->getParsedBody();
            $responseMW = new ResponseMW();
            $rtaJson = new stdClass();
            $rtaJson->mensaje = 'Error.';
            $rtaJson->status = 403;

            if(isset($params['obj_json']))
            {
                
                $jsonAtr = json_decode($params['obj_json']);
                if(isset($jsonAtr->clave))
                {
                    if(isset($jsonAtr->correo))
                    {
                        $response = $handler->handle($request);
                        $responseMW->withStatus($response->getStatusCode());
                        $responseMW->getBody()->write((string)$response->getBody());

                        return $responseMW;
                    }
                    else
                    {
                        $rtaJson->mensaje = 'Error. No existe el parametro correo';
                    }
                }
                else
                {
                    $rtaJson->mensaje = 'Error. No existe el parametro clave';
                }   
                
            }
            else 
            {
           
                $rtaJson->mensaje = 'Error. No existe el parametro obj json';
     
            }
               
            $responseMW->withStatus(403);

            $responseMW->getBody()->write(json_encode($rtaJson));

            return $responseMW;
        }

        public function ObtenerDataJWT(Request $request ,Response $response, array $args) : Response {
        
            $rtaJson = new stdClass();
            $newResponse = $response->withStatus(403);

            $token = $request->getHeader('token')[0];
        

            $datos = Autentificadora::ObtenerPayLoad($token);

            if($datos->exito) {

                $rtaJson->exito= true;
                $rtaJson->payload= $datos->payload;
                $rtaJson->mensaje = $datos->mensaje;
                $newResponse = $response->withStatus(200);
                
            }
            
            
            $newResponse->getBody()->write(json_encode($rtaJson));

            return $newResponse->withHeader('Content-Type' , 'application/json');
        }

        public function ChequearJWT(Request $request, RequestHandler $handler) :  ResponseMW {

            $rtaJson = new stdClass();
            $rtaJson->exito = false;
            $rtaJson->mensaje = 'Error middleware';
            $responseMW = new ResponseMW();
            $encabezado = $request->getHeader('token')[0];

            if(isset($encabezado)){
                
                $verificar = Autentificadora::VerificarJWT($encabezado);

                if($verificar->verificado){

                    $response = $handler->handle($request);
                    $responseMW->withStatus($response->getStatusCode());
                    $responseMW->getBody()->write((string)$response->getBody());
                    return $responseMW;

                }
            }

            $responseMW->withStatus(403);

            $responseMW->getBody()->write(json_encode($rtaJson));

            return $responseMW;
             
        }
           
    
    
    }

?>
