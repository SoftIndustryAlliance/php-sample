<?php

namespace App\Import;

use App\Models\File;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\ResponseInterface;

/**
 * File uploader.
 *
 * @package App\Import\Loader
 */
class FileUpload
{

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Uploads a file from the url to the destination path.
     *
     * @param string $url
     * @param string $path
     *
     * @return File
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upload(string $url, string $path = ''): File
    {
        $response = $this->getResponse($url);
        $contentType = $response->getHeaderLine('content-type');
        [$type, $subType] = explode('/', $contentType);
        if (!empty($type) && !empty($subType)) {
            $fileName = md5($url) . '.' . $subType;
        } else {
            throw new \RuntimeException("Couldn't get content-type from: $url");
        }
        $fileSize = $response->getHeaderLine('content-length');
        $filePath = $path . '/' . $fileName;
        $file = null;
        if (Storage::exists($filePath)) {
            $file = File::where('hashed', $fileName)->first();
        }
        if (empty($file)) {
            Storage::put($filePath, $response->getBody());
            $file = new File();
            $file->name = $fileName;
            $file->mimetype = $contentType;
            $file->filesize = $fileSize;
            $file->hashed = $fileName;
            $file->save();
        }
        return $file;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * @param string $url
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getResponse(string $url): ResponseInterface
    {
        return $this->httpClient->request('GET', $url);
    }
}
