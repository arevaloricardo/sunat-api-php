<?php
declare(strict_types=1);

namespace SunatApi\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Greenter\GreApi\GreSender;
use Greenter\GreApi\Model\Shipment;
use Greenter\GreApi\Model\Direction;
use Greenter\GreApi\Model\Transportist;
use Greenter\GreApi\Model\Company;
use Greenter\GreApi\Model\Client;
use Greenter\GreApi\Model\DespatchDetail;
use DateTime;

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
        // 1. Datos del transportista
        $transportista = new Transportist();
        if (isset($data['transportista'])) {
            $transportista->setTipoDoc($data['transportista']['tipoDoc'] ?? '6')
                ->setNumDoc($data['transportista']['numDoc'] ?? '')
                ->setRznSocial($data['transportista']['rznSocial'] ?? '')
                ->setNroMtc($data['transportista']['nroMtc'] ?? '');
        }

        // 2. Datos del envío (Shipment)
        $shipment = new Shipment();
        
        // Información básica del envío
        $shipment->setVersion($data['version'] ?? '2022')
            ->setTipoDoc($data['tipoDoc'] ?? '09')
            ->setSerie($data['serie'] ?? '')
            ->setCorrelativo($data['correlativo'] ?? '')
            ->setFechaEmision(new DateTime($data['fechaEmision'] ?? 'now'));
        
        // 3. Datos del traslado 
        $envio = isset($data['envio']) ? $data['envio'] : [];
        
        $shipment->setCodTraslado($envio['codTraslado'] ?? '01') // Cat.20 - Venta por defecto
            ->setModTraslado($envio['modTraslado'] ?? '01') // Cat.18 - Transp. Publico por defecto
            ->setFecTraslado(new DateTime($envio['fecTraslado'] ?? 'now'))
            ->setPesoTotal((float)($envio['pesoTotal'] ?? 0))
            ->setUndPesoTotal($envio['undPesoTotal'] ?? 'KGM');
        
        // Opcionalmente número de bultos (solo para importaciones)
        if (isset($envio['numBultos'])) {
            $shipment->setNumBultos((int)$envio['numBultos']);
        }
        
        // 4. Direcciones de partida y llegada
        // Dirección de llegada
        if (isset($envio['llegada'])) {
            $llegada = $envio['llegada'];
            $direccionLlegada = new Direction(
                $llegada['ubigeo'] ?? '',
                $llegada['direccion'] ?? ''
            );
            $shipment->setLlegada($direccionLlegada);
        }
        
        // Dirección de partida
        if (isset($envio['partida'])) {
            $partida = $envio['partida'];
            $direccionPartida = new Direction(
                $partida['ubigeo'] ?? '',
                $partida['direccion'] ?? ''
            );
            $shipment->setPartida($direccionPartida);
        }
        
        // 5. Asignar transportista
        $shipment->setTransportista($transportista);
        
        // 6. Empresa remitente
        if (isset($data['company'])) {
            $company = new Company();
            $company->setRuc($data['company']['ruc'] ?? '')
                ->setRazonSocial($data['company']['razonSocial'] ?? '')
                ->setNombreComercial($data['company']['nombreComercial'] ?? '');
            
            $shipment->setCompany($company);
        }
        
        // 7. Destinatario
        if (isset($data['destinatario'])) {
            $destinatario = new Client();
            $destinatario->setTipoDoc($data['destinatario']['tipoDoc'] ?? '6')
                ->setNumDoc($data['destinatario']['numDoc'] ?? '')
                ->setRznSocial($data['destinatario']['rznSocial'] ?? '');
            
            $shipment->setDestinatario($destinatario);
        }
        
        // 8. Detalles de la guía
        if (isset($data['detalles']) && is_array($data['detalles'])) {
            $detalles = [];
            
            foreach ($data['detalles'] as $item) {
                $detalle = new DespatchDetail();
                $detalle->setCantidad((float)($item['cantidad'] ?? 0))
                    ->setUnidad($item['unidad'] ?? 'ZZ')
                    ->setDescripcion($item['descripcion'] ?? '')
                    ->setCodigo($item['codigo'] ?? '');
                
                // Opcionalmente se puede añadir código de producto SUNAT
                if (isset($item['codProducto'])) {
                    $detalle->setCodProducto($item['codProducto']);
                }
                
                $detalles[] = $detalle;
            }
            
            $shipment->setDetails($detalles);
        }
        
        return $shipment;
    }
} 