# API Intermediaria SUNAT

Este proyecto es una API intermediaria entre un backend existente y la SUNAT (Superintendencia Nacional de Administración Tributaria) de Perú, específicamente para el manejo de Guías de Remisión Electrónica utilizando la nueva plataforma.

## Características

- Endpoint único POST para procesar guías de remisión electrónicas
- Utiliza la librería [greenter/gre-api](https://github.com/thegreenter/gre-api)
- Dockerizado para fácil despliegue
- Configuración flexible mediante variables de entorno

## Requisitos

- Docker y Docker Compose
- Credenciales de SUNAT (Client ID y Client Secret)

## Instalación

1. Clona este repositorio:
   ```
   git clone https://github.com/tu-usuario/sunat-api-php.git
   cd sunat-api-php
   ```

2. Copia el archivo de ejemplo de variables de entorno y configúralo con tus credenciales:
   ```
   cp .env.example .env
   ```

3. Edita el archivo `.env` y configura tus credenciales de SUNAT.

4. Construye y levanta los contenedores Docker:
   ```
   docker-compose up -d
   ```

## Uso

### Endpoint

```
POST /api/sunat/guia-remision
```

### Estructura del Payload

A continuación, un ejemplo del formato JSON que debe enviarse al endpoint:

```json
{
  "version": "2022",
  "tipoDoc": "09",
  "serie": "T001",
  "correlativo": "1",
  "fechaEmision": "2023-06-15",
  "company": {
    "ruc": "20123456789",
    "razonSocial": "MI EMPRESA S.A.C.",
    "nombreComercial": "MI EMPRESA"
  },
  "destinatario": {
    "tipoDoc": "6",
    "numDoc": "20987654321",
    "rznSocial": "EMPRESA DESTINATARIA S.A.C."
  },
  "transportista": {
    "tipoDoc": "6",
    "numDoc": "20111222333",
    "rznSocial": "TRANSPORTES S.A.C.",
    "nroMtc": "0001"
  },
  "envio": {
    "codTraslado": "01",
    "modTraslado": "01",
    "fecTraslado": "2023-06-16",
    "pesoTotal": 12.5,
    "undPesoTotal": "KGM",
    "numBultos": 2,
    "llegada": {
      "ubigeo": "150101",
      "direccion": "AV LIMA 123, LIMA"
    },
    "partida": {
      "ubigeo": "150203",
      "direccion": "AV ITALIA 456, CALLAO"
    }
  },
  "detalles": [
    {
      "cantidad": 2,
      "unidad": "ZZ",
      "descripcion": "PRODUCTO 1",
      "codigo": "PROD001",
      "codProducto": "12345678"
    },
    {
      "cantidad": 3,
      "unidad": "ZZ",
      "descripcion": "PRODUCTO 2",
      "codigo": "PROD002"
    }
  ]
}
```

En este archivo se describe la estructura completa para una guía de remisión. Puedes encontrar más ejemplos en el directorio `/examples`.

### Explicación de Campos Principales

- **version**: Versión del formato (por defecto "2022")
- **tipoDoc**: Tipo de documento (09 para Guía de Remisión)
- **serie**: Serie de la guía (ej. T001)
- **correlativo**: Número correlativo
- **fechaEmision**: Fecha de emisión en formato YYYY-MM-DD
- **company**: Datos de la empresa remitente
- **destinatario**: Datos del destinatario
- **transportista**: Datos del transportista
- **envio**: Detalles del envío
  - **codTraslado**: Código de motivo de traslado (01=Venta)
  - **modTraslado**: Modalidad de transporte (01=Transporte Público)
  - **llegada/partida**: Direcciones de origen y destino
- **detalles**: Lista de productos a transportar

### Ejemplo de respuesta exitosa

```json
{
  "success": true,
  "ticket": "1234567890",
  "status": "ACEPTADO",
  "links": {
    "pdf": "https://url-al-pdf",
    "xml": "https://url-al-xml"
  }
}
```

## Desarrollo

Para desarrollar localmente sin Docker:

1. Instala las dependencias:
   ```
   composer install
   ```

2. Copia y configura el archivo `.env`

3. Levanta el servidor PHP:
   ```
   cd public
   php -S localhost:8080
   ```

## Licencia

MIT 