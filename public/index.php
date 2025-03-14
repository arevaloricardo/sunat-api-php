<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use SunatApi\Controller\SunatController;

require __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Crear aplicación Slim
$app = AppFactory::create();

// Configuración de middleware
$app->addBodyParsingMiddleware();

// Definir rutas
$app->post('/api/sunat/guia-remision', [SunatController::class, 'procesarGuiaRemision']);

// Ejecutar la aplicación
$app->run(); 