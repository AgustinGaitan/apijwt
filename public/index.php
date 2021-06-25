<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    use Slim\Factory\AppFactory;

    
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../src/verificadora.php';
    require_once __DIR__ . '/../src/cd.php';
    
    use Firebase\JWT\JWT;

    $app = AppFactory::create();
    //-------PARTE 1 Y 2-------
    $app->post('/login[/]', Verificadora::class . ':VerificarUsuario')->add(Verificadora::class . ':ValidarParametrosUsuario');  
    //-------PARTE 3 Y 4-------
    $app->get('/login/test', Verificadora::class . ':ObtenerDataJWT')->add(Verificadora::class . ':ChequearJWT');

    //------PARTE 5------------
    $app->group('/json_bd', function(\Slim\Routing\RouteCollectorProxy $grupo) {

        $grupo->get('/', cd::class . ':TraerTodos');
        $grupo->get('/{id}', cd::class . ':TraerUno');
        $grupo->post('/', cd::class . ':Agregar');
        $grupo->put('/', cd::class . ':Modificar');
        $grupo->delete('/', cd::class . ':Eliminar');
    });
        $app->run();
