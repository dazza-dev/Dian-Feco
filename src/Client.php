<?php

namespace DazzaDev\DianFeco;

use DazzaDev\DianFeco\Actions\AttachedDocument;
use DazzaDev\DianFeco\Actions\Document;
use DazzaDev\DianFeco\Actions\GetDocumentById;
use DazzaDev\DianFeco\Actions\NumberingRange;
use DazzaDev\DianFeco\Actions\Payroll as PayrollAction;
use DazzaDev\DianFeco\Actions\StatusEvent;
use DazzaDev\DianFeco\Actions\ZipStatus;
use DazzaDev\DianFeco\Traits\Certificate;
use DazzaDev\DianFeco\Traits\File;
use DazzaDev\DianFeco\Traits\Listing;
use DazzaDev\DianFeco\Traits\Software;
use DazzaDev\DianXmlGenerator\Enums\Environments;
use DazzaDev\DianXmlGenerator\Models\CreditNote\CreditNote;
use DazzaDev\DianXmlGenerator\Models\DebitNote\DebitNote;
use DazzaDev\DianXmlGenerator\Models\Event\Event;
use DazzaDev\DianXmlGenerator\Models\Invoice\Invoice;
use DazzaDev\DianXmlGenerator\Models\Invoice\SupportDocument;
use DazzaDev\DianXmlGenerator\Models\Payroll\AdjustmentNote;
use DazzaDev\DianXmlGenerator\Models\Payroll\Payroll;
use DOMDocument;

class Client
{
    use AttachedDocument;
    use Certificate;
    use Document;
    use File;
    use GetDocumentById;
    use Listing;
    use NumberingRange;
    use PayrollAction;
    use Software;
    use StatusEvent;
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
     * Document
     */
    private Invoice|SupportDocument|CreditNote|DebitNote|Event|Payroll|AdjustmentNote $document;

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

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        $errors = [];
        if (isset($this->responseDian->ErrorMessage->string)) {
            $errorsList = $this->responseDian->ErrorMessage->string;
            $errors = (is_array($errorsList)) ? $errorsList : [$errorsList];
        }

        return $errors;
    }

    /**
     * Get status message
     */
    public function getStatusMessage(): string
    {
        return is_string($this->responseDian->StatusMessage) ? $this->responseDian->StatusMessage : '';
    }

    /**
     * Is valid
     */
    public function isValid(): bool
    {
        return filter_var($this->responseDian->IsValid, FILTER_VALIDATE_BOOLEAN);
    }
}
