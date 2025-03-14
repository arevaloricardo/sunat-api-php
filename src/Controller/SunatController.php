<?php
declare(strict_types=1);

namespace SunatApi\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Greenter\GreApi\GreSender;
use Greenter\GreApi\Model\Shipment;

class SunatController
{
    private GreSender $sender;

    public function __construct()
    {
        // Inicializamos el cliente GreSender con las credenciales de SUNAT
        $this->sender = new GreSender(
            $_ENV['SUNAT_CLIENT_ID'],
            $_ENV['SUNAT_CLIENT_SECRET'],
            (bool)$_ENV['SUNAT_PRODUCTION_MODE']
        );
    }

    /**
     * Procesa una solicitud de guía de remisión
     */
    public function procesarGuiaRemision(Request $request, Response $response): Response
    {
        try {
            // Obtener los datos de la solicitud
            $data = $request->getParsedBody();
            
            if (!$data) {
                throw new \Exception("No se proporcionaron datos en la solicitud");
            }
            
            // Procesar los datos y crear el shipment
            $shipment = $this->crearShipment($data);
            
            // Enviar a SUNAT
            $result = $this->sender->send($shipment);
            
            // Preparar la respuesta
            $responseData = [
                'success' => true,
                'ticket' => $result->getTicket(),
                'status' => $result->getStatus(),
                'links' => $result->getLinks()
            ];
            
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            // Manejar errores
            $errorResponse = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**
     * Crea un objeto Shipment con los datos proporcionados
     */
    private function crearShipment(array $data): Shipment
    {
        $shipment = new Shipment();
        
        // Aquí iría la lógica para mapear los datos recibidos
        // a la estructura requerida por greenter/gre-api
        // Este es un ejemplo básico, deberá adaptarse según la documentación de greenter
        
        // Ejemplo de asignación de propiedades
        if (isset($data['shipment'])) {
            $shipmentData = $data['shipment'];
            
            // Configurar las propiedades según la documentación de greenter/gre-api
            // Por ejemplo:
            // $shipment->setSerie($shipmentData['serie'] ?? null);
            // $shipment->setCorrelativo($shipmentData['correlativo'] ?? null);
            // etc.
        }
        
        return $shipment;
    }
} 