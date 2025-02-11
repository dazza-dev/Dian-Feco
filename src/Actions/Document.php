<?php

namespace DazzaDev\DianFeco\Actions;

use DazzaDev\DianFeco\Exceptions\DocumentException;
use DazzaDev\DianXmlGenerator\Builders\DocumentBuilder;
use Lopezsoft\UBL21dian\Templates\SOAP\SendBillSync;
use Lopezsoft\UBL21dian\Templates\SOAP\SendTestSetAsync;
use Lopezsoft\UBL21dian\XAdES\SignCreditNote;
use Lopezsoft\UBL21dian\XAdES\SignDebitNote;
use Lopezsoft\UBL21dian\XAdES\SignInvoice;

trait Document
{
    private string $documentType;

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
        if ($this->isTestEnvironment()) {
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
        if ($this->isTestEnvironment()) {
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
        if ($this->isTestEnvironment()) {
            $zipKey = $responseDian->SendTestSetAsyncResponse
                ->SendTestSetAsyncResult
                ->ZipKey;
            $this->responseDian = $this->validateZipStatus($zipKey);
        } else {
            $this->responseDian = $responseDian->SendBillSyncResponse
                ->SendBillSyncResult;
        }

        // Status Message
        $statusMessage = is_string($this->responseDian->StatusMessage) ? $this->responseDian->StatusMessage : '';

        // Format Errors
        $errors = [];
        if (isset($this->responseDian->ErrorMessage->string)) {
            $errorsList = $this->responseDian->ErrorMessage->string;
            $errors = (is_array($errorsList)) ? $errorsList : [$errorsList];
        }

        // Set unique code
        $uniqueCode = $this->documentType == 'invoice'
            ? $signDocument->ConsultarCUFEEVENT()
            : $signDocument->ConsultarCUDEEVENT();

        $this->setUniqueCode($uniqueCode);

        // Generate Attached XML
        if ($isValid = filter_var($this->responseDian->IsValid, FILTER_VALIDATE_BOOLEAN)) {
            $this->generateAttachedDocument();
        }

        return [
            'isValid' => $isValid,
            'StatusCode' => $this->responseDian->StatusCode,
            'StatusDescription' => $this->responseDian->StatusDescription,
            'StatusMessage' => $statusMessage,
            'ErrorMessage' => $errors,
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
        $signDocument->technicalKey = $this->getTechnicalKey();

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
