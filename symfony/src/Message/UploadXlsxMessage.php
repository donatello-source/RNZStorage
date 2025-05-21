<?php
namespace App\Message;

class UploadXlsxMessage
{
    public function __construct(
        private string $filePath,
        private string $originalName,
    ) {}

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }
}
