<?php
namespace App\Message;

class UploadXlsxMessage
{
    public function __construct(
        private int $id,
        private string $filePath,
        private string $originalName,
        private ?int $quoteId = null
    ) {
    $this->id = (int) $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getQuoteId(): ?int
    {
        return $this->quoteId;
    }
}

