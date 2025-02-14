<?php

namespace DazzaDev\DianFeco\Actions;

use Lopezsoft\UBL21dian\Templates\SOAP\GetNumberingRange;

trait NumberingRange
{
    /**
     * Get numbering range
     */
    public function getNumberingRange(string $documentNumber)
    {
        $getNumberingRange = new GetNumberingRange(
            $this->getCertificatePath(),
            $this->getCertificatePassword()
        );
        $getNumberingRange->To = $this->getEnvironmentUrl();
        $getNumberingRange->accountCode = $documentNumber;
        $getNumberingRange->accountCodeT = $documentNumber;
        $getNumberingRange->softwareCode = $this->getSoftwareIdentifier();

        // Get response
        $responseDian = $getNumberingRange->signToSend()->getResponseToObject();

        // Result
        $result = $responseDian->Envelope->Body
            ->GetNumberingRangeResponse
            ->GetNumberingRangeResult;

        return [
            'OperationCode' => $result->OperationCode,
            'OperationDescription' => $result->OperationDescription,
            'Data' => ($result->OperationCode == 100)
                ? $this->formatNumberingRanges($result->ResponseList->NumberRangeResponse)
                : [],
        ];
    }

    /**
     * Format the numbering ranges
     */
    private function formatNumberingRanges($numberingRangeResponse): array
    {
        // Convert to array if there is only one numbering range
        if (is_countable($numberingRangeResponse)) {
            foreach ($numberingRangeResponse as $numberingRange) {
                $numberingRanges[] = (array) $numberingRange;
            }
        } else {
            $numberingRanges[] = (array) $numberingRangeResponse;
        }

        // Map the numbering ranges
        return array_map(function ($item) {
            $item['Prefix'] = is_object($item['Prefix'])
                ? ''
                : $item['Prefix'];

            return $item;
        }, $numberingRanges);
    }
}
