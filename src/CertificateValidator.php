<?php

namespace DazzaDev\DianFeco;

use DazzaDev\DianFeco\Exceptions\CertificateException;

class CertificateValidator
{
    /**
     * Certificate file
     */
    protected string $certificatePath;

    /**
     * Certificate password
     */
    protected string $certificatePassword;

    /**
     * Certificate data
     */
    protected ?array $certificate = null;

    /**
     * Constructor
     */
    public function __construct(string $certificatePath, string $certificatePassword)
    {
        $this->certificatePath = $certificatePath;
        $this->certificatePassword = $certificatePassword;
    }

    /**
     * Validate and load certificate
     *
     * @throws CertificateException
     */
    public function validate(): array
    {
        // Check if file exists
        if (! file_exists($this->certificatePath)) {
            throw new CertificateException('Certificate file not found at: '.$this->certificatePath);
        }

        // Read certificate file
        $certificateContent = file_get_contents($this->certificatePath);
        if ($certificateContent === false) {
            throw new CertificateException('Could not read certificate file');
        }

        // Validate certificate
        if (! openssl_pkcs12_read($certificateContent, $this->certificate, $this->certificatePassword)) {
            $error = openssl_error_string() ?: 'Certificate could not be read';
            throw new CertificateException($error);
        }

        return $this->certificate;
    }

    /**
     * Get the loaded certificate data
     *
     * @throws CertificateException
     */
    public function getCertificate(): array
    {
        if (! isset($this->certificate)) {
            throw new CertificateException('Certificate has not been validated yet');
        }

        return $this->certificate;
    }
}
