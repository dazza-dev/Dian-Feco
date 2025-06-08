<?php

namespace DazzaDev\DianFeco\Actions;

use DazzaDev\DianFeco\Exceptions\DocumentException;
use DazzaDev\DianXmlGenerator\Builders\DocumentBuilder;
use Lopezsoft\UBL21dian\Templates\SOAP\SendBillSync;
use Lopezsoft\UBL21dian\Templates\SOAP\SendTestSetAsync;
use Lopezsoft\UBL21dian\XAdES\SignCreditNote;
use Lopezsoft\UBL21dian\XAdES\SignDebitNote;
use Lopezsoft\UBL21dian\XAdES\SignDocumentSupport;
use Lopezsoft\UBL21dian\XAdES\SignInvoice;

trait Document
{
    /**
     * Document type
     */
    private string $documentType;

    /**
     * Document data
     */
    private array $documentData;

    /**
     * Send document
     */
    public function sendDocument()
    {
        // Sign document
        $signDocument = $this->signDocument();

        // set zip and xml files
        $this->generateZipFile();

        // Send document
        if ($this->getSoftwareTestSetId()) {
            $sendDocument = new SendTestSetAsync(
                $this->getCertificatePath(),
                $this->getCertificatePassword()
            );
        } else {
            $sendDocument = new SendBillSync(
                $this->getCertificatePath(),
                $this->getCertificatePassword()
            );
        }
        $sendDocument->To = $this->getEnvironmentUrl();
        $sendDocument->fileName = $this->document->getFullNumber().'.xml';
        $sendDocument->contentFile = $this->zipBase64Bytes;

        // Only for test environment
        if ($this->getSoftwareTestSetId()) {
            $sendDocument->testSetId = $this->getSoftwareTestSetId();
        }

        // Send request
        $send = $sendDocument->signToSend();

        // Get response
        $responseDian = $send->getResponseToObject()->Envelope->Body;

        // Check For Errors
        if (isset($responseDian->Fault)) {
            $errorFault = $responseDian->Fault->Reason->Text;
            throw new DocumentException('Error: '.$errorFault['_value']);
        }

        // Validate Response
        if ($this->getSoftwareTestSetId()) {
            $zipKey = $responseDian->SendTestSetAsyncResponse
                ->SendTestSetAsyncResult
                ->ZipKey;
            $this->responseDian = $this->validateZipStatus($zipKey);
        } else {
            $this->responseDian = $responseDian->SendBillSyncResponse
                ->SendBillSyncResult;
        }

        // Set unique code
        if ($this->documentType == 'invoice' || $this->documentType == 'equivalent-document') {
            $uniqueCode = $signDocument->ConsultarCUFEEVENT();
        } elseif ($this->documentType == 'support-document') {
            $uniqueCode = $signDocument->ConsultarCUDS();
        } else {
            $uniqueCode = $signDocument->ConsultarCUFEEVENT();
        }

        $this->setUniqueCode($uniqueCode);

        // Generate Attached XML
        if ($this->isValid()) {
            $this->generateAttachedDocument();
        }

        return [
            'isValid' => $this->isValid(),
            'StatusCode' => $this->responseDian->StatusCode,
            'StatusDescription' => $this->responseDian->StatusDescription,
            'StatusMessage' => $this->getStatusMessage(),
            'ErrorMessage' => $this->getErrors(),
            'Cufe' => $this->getUniqueCode(),
            'ZipBase64Bytes' => $this->zipBase64Bytes,
            'XmlName' => $this->getXmlFileName(),
            'QrCode' => base64_encode($signDocument->getQRData()),
        ];
    }

    /**
     * Sign document
     */
    public function signDocument()
    {
        $documentClasses = [
            'invoice' => SignInvoice::class,
            'support-document' => SignDocumentSupport::class,
            'equivalent-document' => SignInvoice::class,
            'credit-note' => SignCreditNote::class,
            'debit-note' => SignDebitNote::class,
        ];

        // Validate document type
        if (! isset($documentClasses[$this->documentType])) {
            throw new DocumentException('Document type not supported');
        }

        // Get document class
        $signDocumentClass = $documentClasses[$this->documentType];

        // Create document
        $signDocument = new $signDocumentClass(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );

        $signDocument->softwareID = $this->getSoftwareIdentifier();
        $signDocument->pin = $this->getSoftwarePin();

        // Technical Key only for invoices
        if ($this->documentType == 'invoice') {
            $signDocument->technicalKey = $this->getTechnicalKey();
        }

        // Signed document
        $signDocument->sign($this->documentXml);
        $this->signedDocument = $signDocument->xml;

        return $signDocument;
    }

    /**
     * Set document type
     */
    public function setDocumentType(string $documentType): void
    {
        $this->documentType = $documentType;
    }

    /**
     * Set document data
     */
    public function setDocumentData(array $documentData): void
    {
        $this->documentData = $documentData;

        // Get document Model and XML
        $documentBuilder = new DocumentBuilder(
            $this->documentType,
            $this->documentData,
            $this->getEnvironment()['code'],
            $this->getSoftware()
        );

        $this->document = $documentBuilder->getDocument();
        $this->documentXml = $documentBuilder->getXml();
    }
}
