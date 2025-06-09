<?php

namespace DazzaDev\DianFeco\Traits;

use DazzaDev\DianFeco\Exceptions\FileException;
use ZipArchive;

trait File
{
    /**
     * File path
     */
    protected ?string $filePath = null;

    /**
     * File name
     */
    protected ?string $fileName = null;

    /**
     * File path
     */
    protected function validateFilePath()
    {
        if (is_null($this->filePath)) {
            throw new FileException('File path is not set');
        }
    }

    /**
     * Get file path
     */
    public function getFilePath()
    {
        $this->validateFilePath();

        return $this->filePath;
    }

    /**
     * Get zip path
     */
    public function getZipPath(): string
    {
        $this->validateFilePath();

        return $this->filePath.'/zip';
    }

    /**
     * Get XML path
     */
    public function getXmlPath(): string
    {
        $this->validateFilePath();

        return $this->filePath.'/xml';
    }

    /**
     * Set file path
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * Get zip file name
     */
    public function getXmlFileName(): string
    {
        return $this->fileName.'.xml';
    }

    /**
     * Get file name
     */
    public function getZipFileName(): string
    {
        return $this->fileName.'.zip';
    }

    /**
     * Get base file name
     */
    public function getBaseFileName(): string
    {
        $number = $this->document->getNumber();
        $prefix = $this->document->getDocumentType()->getCodeType();

        // Document Number by type
        if ($prefix == 'ar') {
            $company = $this->document->getReceiver();
        } elseif ($prefix == 'nie' || $prefix == 'niae') {
            $company = $this->document->getEmployer();
        } else {
            $company = $this->document->getCompany();
        }

        // Company Identification Number
        $companyIdentificationNumber = $company->getIdentificationNumber();

        //
        return $prefix.
            $this->stuffedString($companyIdentificationNumber).
            '000'.
            date('y', strtotime('now America/Bogota')).
            $this->stuffedString($number, 8);
    }

    /**
     * Set file name
     */
    public function setFileName(): void
    {
        $this->fileName = $this->getBaseFileName();
    }

    /**
     * Get file name
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Generate zip file
     */
    protected function generateZipFile()
    {
        $this->setFileName();

        // Routes
        $filenameXml = $this->getXmlFileName();
        $filenameZip = $this->getZipFileName();

        // Create directories
        $this->createDirectories();

        // Save signed XML document to file
        $xmlPath = $this->getXmlPath().'/'.$filenameXml;
        file_put_contents($xmlPath, $this->signedDocument);

        // Zip path
        $zipPath = $this->getZipPath().'/'.$filenameZip;

        // Create zip file
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFile($xmlPath, $filenameXml);
        $zip->close();

        // Base64 bytes
        $this->zipBase64Bytes = base64_encode(file_get_contents($zipPath));

        return $this->zipBase64Bytes;
    }

    /**
     * Create directories
     */
    protected function createDirectories()
    {
        $filePath = $this->getFilePath();

        // Create base directory if it doesn't exist
        if (! file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }

        // Create xml directory if it doesn't exist
        if (! file_exists($filePath.'/xml')) {
            mkdir($filePath.'/xml', 0777, true);
        }

        // Create zip directory if it doesn't exist
        if (! file_exists($filePath.'/zip')) {
            mkdir($filePath.'/zip', 0777, true);
        }
    }

    /**
     * Stuffed string
     */
    public function stuffedString(string $string, int $length = 10, int $padString = 0): string
    {
        return str_pad($string, $length, $padString, STR_PAD_LEFT);
    }
}
