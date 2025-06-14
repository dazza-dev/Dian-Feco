<?php

namespace DazzaDev\DianFeco\Actions;

use DazzaDev\DianFeco\Exceptions\DocumentException;
use DazzaDev\DianXmlGenerator\Builders\PayrollBuilder;
use Lopezsoft\UBL21dian\Templates\SOAP\SendNominaSync;
use Lopezsoft\UBL21dian\Templates\SOAP\SendTestSetAsync;
use Lopezsoft\UBL21dian\XAdES\SignPayroll;
use Lopezsoft\UBL21dian\XAdES\SignPayrollAdjustment;

trait Payroll
{
    /**
     * Payroll type
     */
    private string $payrollType;

    /**
     * Payroll data
     */
    private array $payrollData;

    /**
     * Send payroll
     */
    public function sendPayroll()
    {
        // Sign document
        $signDocument = $this->signPayroll();

        // set zip and xml files
        $this->generateZipFile();

        // Send document
        if ($this->getSoftwareTestSetId()) {
            $sendDocument = new SendTestSetAsync(
                $this->getCertificatePath(),
                $this->getCertificatePassword()
            );
        } else {
            $sendDocument = new SendNominaSync(
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
            $this->responseDian = $responseDian->SendNominaSyncResponse
                ->SendNominaSyncResult;
        }

        // Set unique code
        $uniqueCode = $signDocument->getCUNE();

        $this->setUniqueCode($uniqueCode);

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
     * Sign payroll
     */
    public function signPayroll()
    {
        $payrollClasses = [
            'individual' => SignPayroll::class,
            'adjustment-note' => SignPayrollAdjustment::class,
        ];

        // Validate payroll type
        if (! isset($payrollClasses[$this->payrollType])) {
            throw new DocumentException('Document type not supported');
        }

        // Get payroll class
        $signPayrollClass = $payrollClasses[$this->payrollType];

        // Create payroll
        $signDocument = new $signPayrollClass(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );

        $signDocument->softwareID = $this->getSoftwareIdentifier();
        $signDocument->pin = $this->getSoftwarePin();

        // Signed document
        $signDocument->sign($this->documentXml);
        $this->signedDocument = $signDocument->xml;

        return $signDocument;
    }

    /**
     * Set payroll type
     */
    public function setPayrollType(string $payrollType): void
    {
        $this->payrollType = $payrollType;
    }

    /**
     * Set payroll data
     */
    public function setPayrollData(array $payrollData): void
    {
        $this->payrollData = $payrollData;

        // Get payroll Model and XML
        $payrollBuilder = new PayrollBuilder(
            $this->payrollType,
            $this->payrollData,
            $this->getEnvironment()['code'],
            $this->getSoftware()
        );

        $this->document = $payrollBuilder->getPayroll();
        $this->documentXml = $payrollBuilder->getXml();
    }
}
