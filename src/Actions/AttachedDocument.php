<?php

namespace DazzaDev\DianFeco\Actions;

use DazzaDev\DianXmlGenerator\Builders\AttachedDocumentBuilder;

trait AttachedDocument
{
    /**
     * Application response
     */
    private $applicationResponse;

    /**
     * Attached document
     */
    private $attachedDocument;

    /**
     * Attached document XML
     */
    private $attachedDocumentXml;

    /**
     * Generate attached document
     */
    public function generateAttachedDocument(): void
    {
        $fileName = $this->getXmlFileName();
        $document = $this->document;
        $documentTypeCode = $document->getDocumentType()->getCodeType();
        $this->applicationResponse = base64_decode($this->responseDian->XmlBase64Bytes);

        // Generate attached document
        $attachedDocumentBuilder = $this->buildAttachedDocument();
        $this->attachedDocument = $attachedDocumentBuilder->getAttachedDocument();
        $this->attachedDocumentXml = $attachedDocumentBuilder->getXml();

        // Attached Document File
        $attachedDocumentName = str_replace($documentTypeCode, 'ad', $fileName);
        $fileAttachedDocumentPath = $this->getXmlPath().'/'.$attachedDocumentName;
        file_put_contents($fileAttachedDocumentPath, $this->attachedDocumentXml);

        // Application Response File
        $applicationResponseName = str_replace($documentTypeCode, 'ar', $fileName);
        $fileApplicationResponsePath = $this->getXmlPath().'/'.$applicationResponseName;
        file_put_contents($fileApplicationResponsePath, $this->applicationResponse);
    }

    /**
     * Build attached document
     */
    public function buildAttachedDocument(): AttachedDocumentBuilder
    {
        $document = $this->document;
        $customer = $document->getCustomer();
        $company = $document->getCompany();

        // Attached Document Builder
        return new AttachedDocumentBuilder([
            'prefix' => $document->getPrefix(),
            'number' => $document->getNumber(),
            'date' => $document->getDate(),
            'unique_code' => $this->getUniqueCode(),
            'customer' => [
                'identification_type' => $customer->getIdentificationType()->getCode(),
                'identification_number' => $customer->getIdentificationNumber(),
                'entity_type' => $customer->getEntityType()->getCode(),
                'regime' => $customer->getRegime()->getCode(),
                'liability' => $customer->getLiability()->getCode(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'phone' => $customer->getPhone(),
            ],
            'company' => [
                'identification_type' => $company->getIdentificationType()->getCode(),
                'identification_number' => $company->getIdentificationNumber(),
                'entity_type' => $company->getEntityType()->getCode(),
                'regime' => $company->getRegime()->getCode(),
                'liability' => $company->getLiability()->getCode(),
                'merchant_registration' => $company->getMerchantRegistration(),
                'name' => $company->getName(),
                'email' => $company->getEmail(),
                'phone' => $company->getPhone(),
            ],
            'signed_xml' => $this->signedDocument,
            'application_response' => $this->applicationResponse,
        ]);
    }
}
