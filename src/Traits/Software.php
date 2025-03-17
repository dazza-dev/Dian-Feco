<?php

namespace DazzaDev\DianFeco\Traits;

trait Software
{
    /**
     * Software identifier
     */
    protected string $softwareIdentifier;

    /**
     * Software test id
     */
    protected ?string $softwareTestSetId = null;

    /**
     * Software pin
     */
    protected string $softwarePin;

    /**
     * Set software
     */
    public function setSoftware(array $software)
    {
        $this->softwareIdentifier = $software['identifier'];
        $this->softwareTestSetId = $software['test_set_id'] ?? null;
        $this->softwarePin = $software['pin'];
    }

    /**
     * Get software
     */
    public function getSoftware()
    {
        return [
            'identifier' => $this->softwareIdentifier,
            'test_set_id' => $this->softwareTestSetId,
            'pin' => $this->softwarePin,
        ];
    }

    /**
     * Get software identifier
     */
    public function getSoftwareIdentifier(): string
    {
        return $this->softwareIdentifier;
    }

    /**
     * Get software test set id
     */
    public function getSoftwareTestSetId(): ?string
    {
        return $this->softwareTestSetId;
    }

    /**
     * Get software pin
     */
    public function getSoftwarePin(): string
    {
        return $this->softwarePin;
    }
}
