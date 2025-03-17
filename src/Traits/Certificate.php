<?php

namespace DazzaDev\DianFeco\Traits;

use DazzaDev\DianFeco\CertificateValidator;
use DazzaDev\DianFeco\Exceptions\CertificateException;

trait Certificate
{
    /**
     * Certificate path
     */
    protected string $certificatePath;

    /**
     * Certificate password
     */
    protected string $certificatePassword;

    /**
     * Certificate data
     */
    protected ?array $certificateData = null;

    /**
     * Set and validate certificate
     *
     * @throws CertificateException
     */
    public function setCertificate(array $certificate): void
    {
        if (! isset($certificate['path'], $certificate['password'])) {
            throw new CertificateException('Certificate path and password are required');
        }

        $this->setCertificatePath($certificate['path']);
        $this->setCertificatePassword($certificate['password']);

        // Validate certificate
        $validator = new CertificateValidator($this->certificatePath, $this->certificatePassword);
        $this->certificateData = $validator->validate();
    }

    /**
     * Set certificate path
     */
    public function setCertificatePath(string $certificatePath): void
    {
        $this->certificatePath = $certificatePath;
    }

    /**
     * Get certificate path
     */
    public function getCertificatePath(): string
    {
        return $this->certificatePath;
    }

    /**
     * Set certificate password
     */
    public function setCertificatePassword(string $certificatePassword): void
    {
        $this->certificatePassword = $certificatePassword;
    }

    /**
     * Get certificate password
     */
    public function getCertificatePassword(): string
    {
        return $this->certificatePassword;
    }

    /**
     * Get validated certificate data
     *
     * @throws CertificateException
     */
    public function getCertificateData(): array
    {
        if ($this->certificateData === null) {
            throw new CertificateException('Certificate has not been validated yet');
        }

        return $this->certificateData;
    }
}
