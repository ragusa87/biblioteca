<?php

namespace App\Kobo;

use App\Entity\Book;
use App\Entity\KoboDevice;
use App\Exception\BookFileNotFound;
use App\Kobo\ImageProcessor\CoverTransformer;
use App\Kobo\Messenger\KepubifyMessage;
use App\Service\BookFileSystemManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DownloadHelper
{
    public function __construct(
        private readonly BookFileSystemManager $fileSystemManager,
        private readonly CoverTransformer $coverTransformer,
        protected UrlGeneratorInterface $urlGenerator,
        protected LoggerInterface $logger,
        protected MessageBusInterface $messageBus
    ) {
    }

    protected function getBookFilename(Book $book): string
    {
        return $this->fileSystemManager->getBookFilename($book);
    }

    public function getSize(Book $book): int
    {
        return $this->fileSystemManager->getBookSize($book) ?? 0;
    }

    public function getCoverSize(Book $book): int
    {
        return $this->fileSystemManager->getCoverSize($book) ?? 0;
    }

    public function getUrlForKoboDevice(Book $book, KoboDevice $kobo): string
    {
        return $this->urlGenerator->generate('app_kobodownload', [
            'id' => $book->getId(),
            'accessKey' => $kobo->getAccessKey(),
            'extension' => $book->getExtension(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function exists(Book $book): bool
    {
        return $this->fileSystemManager->fileExist($book);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getCoverResponse(Book $book, int $width, int $height, string $extensionWithDot, bool $grayscale = false, bool $asAttachement = true): StreamedResponse
    {
        $coverPath = $this->fileSystemManager->getCoverFilename($book);
        if ($coverPath === null || false === $this->fileSystemManager->coverExist($book)) {
            throw new BookFileNotFound($coverPath);
        }
        $responseExtensionWithDot = $this->coverTransformer->canConvertFile($coverPath) ? $extensionWithDot : '.'.pathinfo($coverPath, PATHINFO_EXTENSION);
        $response = new StreamedResponse(function () use ($coverPath, $width, $height, $grayscale, $responseExtensionWithDot) {
            $this->coverTransformer->streamFile($coverPath, $width, $height, $responseExtensionWithDot, $grayscale);
        }, Response::HTTP_OK);

        match ($responseExtensionWithDot) {
            CoverTransformer::JPG, CoverTransformer::JPEG => $response->headers->set('Content-Type', 'image/jpeg'),
            CoverTransformer::PNG => $response->headers->set('Content-Type', 'image/png'),
            CoverTransformer::GIF => $response->headers->set('Content-Type', 'image/gif'),
            default => $response->headers->set('Content-Type', 'application/octet-stream'),
        };

        $filename = $book->getImageFilename();
        if ($filename === null) {
            return $response;
        }

        $encodedFilename = rawurlencode($filename);
        $simpleName = rawurlencode(sprintf('book-cover--%s-%s', $book->getId(), preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $filename)));
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition($asAttachement ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE, $encodedFilename, $simpleName));

        return $response;
    }

    public function getResponse(Book $book): Response
    {
        $bookPath = $this->getBookFilename($book);
        if (false === $this->exists($book)) {
            throw new BookFileNotFound($bookPath);
        }

        // Convert the file to kepub (Kobo's epub format) as a temporary file
        $temporaryFile = $this->runKepubify($bookPath);
        $fileToStream = $temporaryFile ?? $bookPath;
        $fileSize = filesize($fileToStream);

        $response = (new BinaryFileResponse($fileToStream, Response::HTTP_OK))
            ->deleteFileAfterSend($temporaryFile !== null);

        $filename = $book->getBookFilename();
        $encodedFilename = rawurlencode($filename);
        $simpleName = rawurlencode(sprintf('book-%s-%s', $book->getId(), preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $filename)));

        $response->headers->set('Content-Type', match (strtolower($book->getExtension())) {
            'epub', 'epub3' => 'application/epub+zip',
            default => 'application/octet-stream'
        });
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $encodedFilename, $simpleName));

        if ($fileSize !== false) {
            $response->headers->set('Content-Length', (string) $fileSize);
        }

        return $response;
    }

    public function coverExist(Book $book): bool
    {
        return $this->fileSystemManager->coverExist($book);
    }

    private function runKepubify(string $bookPath): ?string
    {
        $conversionDto = new KepubifyMessage($bookPath);
        $this->messageBus->dispatch($conversionDto);

        return $conversionDto->destination;
    }
}
