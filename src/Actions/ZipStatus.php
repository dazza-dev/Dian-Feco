<?php

namespace DazzaDev\DianFeco\Actions;

use Lopezsoft\UBL21dian\Templates\SOAP\GetStatusZip;

trait ZipStatus
{
    /**
     * Validate zip status
     */
    public function validateZipStatus(string $zipKey)
    {
        $maxRetries = 5;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            $response = $this->getZipStatus($zipKey);
            $statusCode = (array) $response->StatusCode;

            // If the response is empty, increment the retry count
            if (empty($statusCode) || $statusCode[0] === 0 || $statusCode[0] === '0' || $statusCode[0] === false) {
                $retryCount++;

                // If the maximum number of retries is reached, return the response
                if ($retryCount >= $maxRetries) {
                    return $response;
                }

                sleep(5);
            } else {
                return $response;
            }
        }

        return $response ?? null;
    }

    /**
     * Get zip status
     */
    public function getZipStatus(string $zipKey)
    {
        $getZipStatus = new GetStatusZip(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );
        $getZipStatus->trackId = $zipKey;

        // Send request
        $responseDian = $getZipStatus->signToSend()->getResponseToObject();

        return $responseDian->Envelope->Body
            ->GetStatusZipResponse
            ->GetStatusZipResult
            ->DianResponse;
    }
}
