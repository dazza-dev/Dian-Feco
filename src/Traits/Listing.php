<?php

namespace DazzaDev\DianFeco\Traits;

use Composer\InstalledVersions;
use Exception;

trait Listing
{
    private function getDataDirectory(): string
    {
        return InstalledVersions::getInstallPath('dazza-dev/dian-xml-generator').'/src/Data/';
    }

    /**
     * Get listings
     */
    public function getListings(): array
    {
        $dataDirectory = $this->getDataDirectory();

        if (! is_dir($dataDirectory)) {
            throw new Exception('Directory not found');
        }

        $files = array_diff(scandir($dataDirectory), ['..', '.']);
        $fileNamesWithoutExtension = array_map(function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $files);

        return array_values($fileNamesWithoutExtension);
    }

    /**
     * Get Listing By Type
     */
    public function getListing(string $type): array
    {
        $filePath = $this->getDataDirectory()."$type.json";
        if (! file_exists($filePath)) {
            throw new Exception('File not found');
        }

        return json_decode(file_get_contents($filePath), true);
    }
}
