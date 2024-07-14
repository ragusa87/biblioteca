<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\BookFormat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BookConverter
{
    protected string $converterUrl = 'http://calibre:7654/convert';

    public function __construct(
        private BookFileSystemManager $fileSystemManager,
        private BookFormatStorageInterface $storage,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $client,
        private string $cacheDir
    ) {
    }

    public function convert(Book $book, string $format = BookFormatStorageInterface::FORMAT_EPUB_KOBO): void
    {
        $response = $this->doRequest($book, $format);
        $file = $this->responseToFile($response);
        $this->storage->store($book, $format, $file);

        if (false === $book->hasFormat($format)) {
            $bookFormat = new BookFormat($book, $format);
            $book->addBookFormat($bookFormat);
            $this->entityManager->persist($bookFormat);
            $this->entityManager->flush();
        }
    }

    public function responseToFile(ResponseInterface $response): \SplFileInfo
    {
        $random = bin2hex(random_bytes(8));
        $tempDest = $this->cacheDir.'/'.$random.'.ebook';
        try {
            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(sprintf('Invalid status code %s, %s', $response->getStatusCode(), $response->getContent(false)));
            }

            $content = $response->getContent(false);

            $dirname = dirname($tempDest);
            if (false === is_dir($dirname)) {
                mkdir($dirname, 0777, true);
            }
            $status = file_put_contents($tempDest, $content);
            if ($status === false) {
                throw new \RuntimeException('Could not write to file');
            }

            return new \SplFileInfo($tempDest);
        } catch (\Exception $e) {
            if (file_exists($tempDest)) {
                unlink($tempDest);
            }
            throw $e;
        }
    }

    protected function doRequest(Book $book, string $format): ResponseInterface
    {
        $file = $this->fileSystemManager->getBookFile($book);
        $data = [
            'input_format' => $book->getExtension(),
            'output_format' => $format,
            'file' => DataPart::fromPath($file->getPathname(), 'name.'.$format),
        ];

        $formData = new FormDataPart($data);

        $options = [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ];

        return $this->client->request('POST', $this->converterUrl, $options);
    }

    public function setConverterUrl(string $converterUrl): void
    {
        $this->converterUrl = $converterUrl;
    }
}
