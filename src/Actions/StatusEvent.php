<?php

namespace DazzaDev\DianFeco\Actions;

use DazzaDev\DianXmlGenerator\Builders\EventBuilder;
use DazzaDev\DianXmlGenerator\Models\Event\Event;
use Lopezsoft\UBL21dian\Templates\SOAP\GetStatusEvents;
use Lopezsoft\UBL21dian\Templates\SOAP\SendEvent;
use Lopezsoft\UBL21dian\XAdES\SignEvent;

trait StatusEvent
{
    private string $eventCode;

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
    public function sendEvent()
    {
        // Sign event
        $signEvent = $this->signEvent();

        // set zip and xml files
        $this->generateZipFile();

        //
        $sendEvent = new SendEvent(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );
        $sendEvent->To = $this->getEnvironmentUrl();
        $sendEvent->contentFile = $this->zipBase64Bytes;

        // Only for test environment
        if ($this->isTestEnvironment()) {
            $sendEvent->testSetId = $this->getSoftwareTestSetId();
        }

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

    /**
     * Sign Event
     */
    public function signEvent()
    {
        $signEvent = new SignEvent(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );

        $signEvent->softwareID = $this->getSoftwareIdentifier();
        $signEvent->pin = $this->getSoftwarePin();

        // Signed document
        $signEvent->sign($this->documentXml);
        $this->signedDocument = $signEvent->xml;

        return $signEvent;
    }

    /**
     * Set event code
     */
    public function setEventCode(string $eventCode): void
    {
        $this->eventCode = $eventCode;
    }

    /**
     * Get event code
     */
    public function getEventCode(): string
    {
        return $this->eventCode;
    }

    /**
     * Set event info
     */
    public function setEventData(array $eventData): void
    {
        $this->eventData = $eventData;

        // Get event Model and XML
        $eventBuilder = new EventBuilder(
            $this->eventCode,
            $this->eventData,
            $this->getEnvironment()['code'],
            $this->getSoftware()
        );

        $this->document = $eventBuilder->getEvent();
        $this->documentXml = $eventBuilder->getXml();
    }
}
