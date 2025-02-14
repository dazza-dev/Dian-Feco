<?php

namespace DazzaDev\DianFeco\Actions;

use Lopezsoft\UBL21dian\Templates\SOAP\GetStatusEvents;
use Lopezsoft\UBL21dian\Templates\SOAP\SendEvent;

trait StatusEvent
{
    /**
     * Get status event
     */
    public function getStatusEvent(string $uniqueId)
    {
        $getStatusEvents = new GetStatusEvents(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );
        $getStatusEvents->To = $this->getEnvironmentUrl();
        $getStatusEvents->trackId = $uniqueId;

        // Get response
        $responseDian = $getStatusEvents->signToSend()->getResponseToObject();

        // Result
        $this->responseDian = $responseDian->Envelope->Body
            ->GetStatusEventResponse
            ->GetStatusEventResult;

        return [
            'isValid' => $this->isValid(),
            'StatusCode' => $this->responseDian->StatusCode,
            'StatusDescription' => $this->responseDian->StatusDescription,
            'StatusMessage' => $this->getStatusMessage(),
            'ErrorMessage' => $this->getErrors(),
            'Cufe' => $this->responseDian->XmlDocumentKey,
            'ZipBase64Bytes' => $this->responseDian->XmlBase64Bytes,
            'XmlName' => $this->responseDian->XmlFileName,
        ];
    }

    /**
     * Send event
     */
    public function sendEvent(string $zipBase64Bytes)
    {
        $sendEvent = new SendEvent(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );
        $sendEvent->contentFile = $zipBase64Bytes;

        // Get response
        $responseDian = $sendEvent->signToSend()->getResponseToObject();

        $this->responseDian = $responseDian->Envelope->Body
            ->SendEventUpdateStatusResponse
            ->SendEventUpdateStatusResult;

        return [
            'isValid' => $this->isValid(),
            'StatusCode' => $this->responseDian->StatusCode,
            'StatusDescription' => $this->responseDian->StatusDescription,
            'StatusMessage' => $this->getStatusMessage(),
            'ErrorMessage' => $this->getErrors(),
            'Cufe' => $this->responseDian->XmlDocumentKey,
            'ZipBase64Bytes' => $this->responseDian->XmlBase64Bytes,
            'XmlName' => $this->responseDian->XmlFileName,
        ];
    }
}
