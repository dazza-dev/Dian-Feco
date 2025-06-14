# DIAN FECO

Paquete para enviar documentos electrónicos (Facturas, Notas Crédito y Notas Débito) a la DIAN.

## Instalación

```bash
composer require dazza-dev/dian-feco
```

## Configuración

```php
use DazzaDev\DianFeco\Client;

$client = new Client(test: true); // true or false

$client->setSoftware([
    'identifier' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'test_set_id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'pin' => 'pin_software',
]);

$client->setCertificate([
    'path' => _DIR_ . '/certificado.p12',
    'password' => 'clave_certificado',
]);

// Ruta donde se guardarán los archivos xml y zip
$client->setFilePath(_DIR_ . '/feco');
```

## Uso

### Enviar un documento electrónico (factura, nota de débito o nota de crédito)

La estructura de los datos de la factura la puedes encontrar en: [dazza-dev/dian-xml-generator](https://github.com/dazza-dev/dian-xml-generator).

```php
$client->setDocumentType('invoice'); // Tipo de documento ('invoice', 'support-document', 'debit-note', 'credit-note')
$client->setDocumentData($documentData); // Datos del documento

$client->setTechnicalKey('clave_tecnica'); // Clave técnica (Solo para facturas)

$document = $client->sendDocument();
```

### Enviar nomina electrónica (individual, nota de ajuste reemplazo o eliminación)

La estructura de los datos de nomina la puedes encontrar en: [dazza-dev/dian-xml-generator](https://github.com/dazza-dev/dian-xml-generator).

```php
$client->setPayrollType('individual'); // Tipo de nomina ('individual', 'adjustment-note')
$client->setPayrollData($payrollData); // Datos de la nomina

$payroll = $client->sendPayroll();
```

### Obtener las numeraciones

Después de asignar los prefijos dentro del modulo Facturando electrónicamente de la [DIAN](https://catalogo-vpfe.dian.gov.co/User/Login), puedes obtener las numeraciones asi:

```php
$numberingRange = $client->getNumberingRange('nit_emisor');
```

### Obtener los listados

La DIAN tiene una lista de códigos que este paquete te pone a disposición para facilitar el trabajo de consultar esto en el anexo técnico de la DIAN:

```php
$listings = $client->getListings();
$listingByType = $client->getListing('identification-types');
```

### Emitir Eventos

La estructura de los datos del evento la puedes encontrar en: [dazza-dev/dian-xml-generator](https://github.com/dazza-dev/dian-xml-generator).

```php
$client->setEventCode('030'); // Consultar listado de eventos
$client->setEventData($eventData); // Datos del evento

$document = $client->sendEvent();
```

### Obtener los eventos de un documento

Después de enviar un documento electrónico, puedes obtener los eventos de ese documento asi:

```php
$events = $client->getStatusEvent('cufe/cude_documento');
```

## Contribuciones

Contribuciones son bienvenidas. Si encuentras algún error o tienes ideas para mejoras, por favor abre un issue o envía un pull request. Asegúrate de seguir las guías de contribución.

## Autor

DIAN FECO fue creado por [DAZZA](https://github.com/dazza-dev).

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).
