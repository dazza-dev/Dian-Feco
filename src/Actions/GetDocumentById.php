<?php

namespace DazzaDev\DianFeco\Actions;

use Lopezsoft\UBL21dian\Templates\SOAP\GetXmlByDocumentKey;

trait GetDocumentById
{
    /**
     * Get document by Id
     */
    public function getDocumentById(string $uniqueId)
    {
        $getDocumentById = new GetXmlByDocumentKey(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );
        $getDocumentById->To = $this->getEnvironmentUrl();
        $getDocumentById->trackId = $uniqueId;

        // Get response
        $responseDian = $getDocumentById->signToSend()->getResponseToObject();

        $this->responseDian = $responseDian->Envelope->Body
            ->GetXmlByDocumentKeyResponse
            ->GetXmlByDocumentKeyResult;

        return [
            'Code' => $this->responseDian->Code,
            'Message' => $this->responseDian->Message,
            'XmlBytesBase64' => $this->responseDian->XmlBytesBase64,
        ];
    }
}
