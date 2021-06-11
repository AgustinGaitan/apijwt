<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    use Slim\Factory\AppFactory;

    
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../src/verificadora.php';
   
    use Firebase\JWT\JWT;

    $app = AppFactory::create();

    $app->post('/login[/]', Verificadora::class . ':VerificarUsuario')->add(Verificadora::class . ':ValidarParametrosUsuario');  
    $app->get('/login/test', Verificadora::class . ':ObtenerDataJWT');
    $app->run();
