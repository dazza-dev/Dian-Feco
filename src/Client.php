<?php

namespace DazzaDev\DianFeco;

use DazzaDev\DianFeco\Actions\AttachedDocument;
use DazzaDev\DianFeco\Actions\Document;
use DazzaDev\DianFeco\Actions\NumberingRange;
use DazzaDev\DianFeco\Actions\ZipStatus;
use DazzaDev\DianFeco\Traits\Certificate;
use DazzaDev\DianFeco\Traits\File;
use DazzaDev\DianFeco\Traits\Software;
use DazzaDev\DianXmlGenerator\Enums\Environments;
use DazzaDev\DianXmlGenerator\Models\CreditNote\CreditNote;
use DazzaDev\DianXmlGenerator\Models\DebitNote\DebitNote;
use DazzaDev\DianXmlGenerator\Models\Invoice\Invoice;
use DOMDocument;

class Client
{
    use AttachedDocument;
    use Certificate;
    use Document;
    use File;
    use NumberingRange;
    use Software;
    use ZipStatus;

    /**
     * Is test environment
     */
    private bool $isTestEnvironment;

    /**
     * Environment
     */
    protected array $environment;

    /**
     * Technical key
     */
    protected ?string $technicalKey;

    /**
     * Document data
     */
    private array $documentData;

    /**
     * Document
     */
    private Invoice|CreditNote|DebitNote $document;

    /**
     * Document XML
     */
    private DOMDocument $documentXml;

    /**
     * Signed document
     */
    private string $signedDocument;

    /**
     * Response Dian
     */
    private $responseDian;

    /**
     * Unique code
     */
    private string $uniqueCode;

    /**
     * Zip Base64 bytes
     */
    private string $zipBase64Bytes;

    /**
     * Xml Base64 bytes
     */
    private string $xmlBase64Bytes;

    /**
     * Constructor
     */
    public function __construct(bool $test = false)
    {
        $this->isTestEnvironment = $test;

        // Set environment
        if ($this->isTestEnvironment) {
            $this->setEnvironment(Environments::TEST);
        } else {
            $this->setEnvironment(Environments::PRODUCTION);
        }
    }

    /**
     * Set environment
     */
    public function setEnvironment(Environments $environment): void
    {
        $this->environment = $environment->toArray();
    }

    /**
     * Get environment
     */
    public function getEnvironment(): array
    {
        return $this->environment;
    }

    /**
     * Get environment url
     */
    public function getEnvironmentUrl(): string
    {
        return $this->environment['service_url'];
    }

    /**
     * Is test environment
     */
    public function isTestEnvironment(): bool
    {
        return $this->environment['code'] == '2';
    }

    /**
     * Set technical key
     */
    public function setTechnicalKey(string $technicalKey): void
    {
        $this->technicalKey = $technicalKey;
    }

    /**
     * Get technical key
     */
    public function getTechnicalKey(): ?string
    {
        return $this->technicalKey ?? null;
    }

    /**
     * Set unique code
     */
    public function setUniqueCode(string $uniqueCode): void
    {
        $this->uniqueCode = $uniqueCode;
    }

    /**
     * Get unique code
     */
    public function getUniqueCode(): ?string
    {
        return $this->uniqueCode;
    }
}
