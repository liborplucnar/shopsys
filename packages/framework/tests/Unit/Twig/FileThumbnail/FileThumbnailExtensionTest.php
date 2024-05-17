<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Twig\FileThumbnail;

use Intervention\Image\Image;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageThumbnailFactory;
use Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension;

class FileThumbnailExtensionTest extends TestCase
{
    public function testGetFileThumbnailInfoByTemporaryFilenameBrokenImage(): void
    {
        $temporaryFilename = 'filename.jpg';

        $fileUploadMock = $this->getMockBuilder(FileUpload::class)
            ->onlyMethods(['getTemporaryFilepath'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn(
            'dir/' . $temporaryFilename,
        );

        $exception = new FileIsNotSupportedImageException($temporaryFilename);
        $imageThumbnailFactoryMock = $this->getMockBuilder(ImageThumbnailFactory::class)
            ->onlyMethods(['getImageThumbnail'])
            ->disableOriginalConstructor()
            ->getMock();
        $imageThumbnailFactoryMock->expects($this->once())->method('getImageThumbnail')->willThrowException(
            $exception,
        );

        $fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageThumbnailFactoryMock);
        $fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

        $this->assertSame(FileThumbnailExtension::DEFAULT_ICON_TYPE, $fileThumbnailInfo->getIconType());
        $this->assertNull($fileThumbnailInfo->getImageUri());
    }

    public function testGetFileThumbnailInfoByTemporaryFilenameImage(): void
    {
        $temporaryFilename = 'filename.jpg';
        $encodedData = 'encodedData';

        $fileUploadMock = $this->getMockBuilder(FileUpload::class)
            ->onlyMethods(['getTemporaryFilepath'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn(
            'dir/' . $temporaryFilename,
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Intervention\Image\Image $imageMock */
        $imageMock = $this->getMockBuilder(Image::class)
            ->onlyMethods(['encode', '__call'])
            ->disableOriginalConstructor()
            ->getMock();
        $imageMock->expects($this->once())->method('encode')->willReturnSelf();
        $imageMock->expects($this->once())->method('__call')->with('destroy');
        $imageMock->setEncoded($encodedData);

        $imageThumbnailMock = $this->getMockBuilder(ImageThumbnailFactory::class)
            ->onlyMethods(['getImageThumbnail'])
            ->disableOriginalConstructor()
            ->getMock();
        $imageThumbnailMock->expects($this->once())->method('getImageThumbnail')->willReturn($imageMock);

        $fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageThumbnailMock);
        $fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

        $this->assertNull($fileThumbnailInfo->getIconType());
        $this->assertSame($encodedData, $fileThumbnailInfo->getImageUri());
    }

    public function testGetFileThumbnailInfoByTemporaryFilenameImageDocument(): void
    {
        $temporaryFilename = 'filename.doc';

        $fileUploadMock = $this->getMockBuilder(FileUpload::class)
            ->onlyMethods(['getTemporaryFilepath'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn(
            'dir/' . $temporaryFilename,
        );

        $exception = new FileIsNotSupportedImageException($temporaryFilename);
        $imageThumbnailFactoryMock = $this->getMockBuilder(ImageThumbnailFactory::class)
            ->onlyMethods(['getImageThumbnail'])
            ->disableOriginalConstructor()
            ->getMock();
        $imageThumbnailFactoryMock->expects($this->once())->method('getImageThumbnail')->willThrowException(
            $exception,
        );

        $fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageThumbnailFactoryMock);
        $fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

        $this->assertSame('word', $fileThumbnailInfo->getIconType());
        $this->assertNull($fileThumbnailInfo->getImageUri());
    }
}
