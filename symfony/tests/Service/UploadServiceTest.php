<?php

namespace App\Tests\Service;

use App\Entity\Upload;
use App\Repository\UploadRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class UploadServiceTest extends TestCase
{
    private $uploadRepositoryMock;
    private $entityManagerMock;
    private $sluggerMock;
    private UploadService $service;

    protected function setUp(): void
    {
        $this->uploadRepositoryMock = $this->createMock(UploadRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->sluggerMock = $this->createMock(SluggerInterface::class);

        $this->service = new UploadService(
            $this->uploadRepositoryMock,
            $this->entityManagerMock,
            '/tmp',
            $this->sluggerMock
        );
    }

    public function testStorePersistsUpload(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('plik.txt');
        $file->method('guessExtension')->willReturn('txt');
        $file->expects($this->once())->method('move');

        $this->sluggerMock->method('slug')->willReturn(new UnicodeString('plik'));

        $this->entityManagerMock->expects($this->once())->method('persist')->with($this->isInstanceOf(Upload::class));
        $this->entityManagerMock->expects($this->once())->method('flush');

        $upload = $this->service->store($file);

        $this->assertInstanceOf(Upload::class, $upload);
        $this->assertEquals('plik.txt', $upload->getOriginalName());
        $this->assertEquals('pending', $upload->getStatus());
        $this->assertNotNull($upload->getFilePath());
    }

    public function testGetAllReturnsArray(): void
    {
        $upload = new Upload();
        $upload->setFilePath('/tmp/plik.txt');
        $upload->setOriginalName('plik.txt');
        $upload->setStatus('done');
        $upload->setCreatedAt(new \DateTime());
        $upload->setUpdatedAt(new \DateTime());

        $this->uploadRepositoryMock->method('findBy')->willReturn([$upload]);

        $result = $this->service->getAll();
        $this->assertIsArray($result);
        $this->assertEquals('plik.txt', $result[0]['original_name']);
        $this->assertEquals('/api/upload/'.$upload->getId().'/download', $result[0]['download_url']);
    }

    public function testGetStatusReturnsNullIfNotFound(): void
    {
        $this->uploadRepositoryMock->method('find')->willReturn(null);
        $this->assertNull($this->service->getStatus(123));
    }

    public function testGetStatusReturnsArray(): void
    {
        $upload = new Upload();
        $upload->setStatus('pending');
        $upload->setCreatedAt(new \DateTime());
        $upload->setUpdatedAt(new \DateTime());

        $this->uploadRepositoryMock->method('find')->willReturn($upload);

        $result = $this->service->getStatus(1);
        $this->assertIsArray($result);
        $this->assertEquals('pending', $result['status']);
    }

    public function testGetFilePathReturnsNullIfNotFoundOrNotDone(): void
    {
        $this->uploadRepositoryMock->method('find')->willReturn(null);
        $this->assertNull($this->service->getFilePath(1));

        $upload = new Upload();
        $upload->setStatus('pending');
        $this->uploadRepositoryMock->method('find')->willReturn($upload);
        $this->assertNull($this->service->getFilePath(1));
    }
}