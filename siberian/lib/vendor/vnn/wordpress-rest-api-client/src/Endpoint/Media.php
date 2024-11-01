<?php

namespace Vnn\WpApiClient\Endpoint;

use GuzzleHttp\Psr7\Request;
use RuntimeException;

/**
 * Class Media
 * @package Vnn\WpApiClient\Endpoint
 */
class Media extends AbstractWpEndpoint
{
    /**
     * {@inheritdoc}
     */
    protected function getEndpoint()
    {
        return '/wp-json/wp/v2/media';
    }

    /**
     * @param string $filePath absolute path of file to upload, or full URL of resource
     * @param array $data
     * @param string $mimeType if $filePath is a URL, the mime type of the file to be uploaded
     * @throws \RuntimeException
     * @return array
     */
    public function upload(string $filePath, array $data = [], $mimeType = null) : array
    {
        $url = $this->getEndpoint();

        if (isset($data['id'])) {
            $url .= '/' . $data['id'];
            unset($data['id']);
        }

        $fileName = basename($filePath);
        $fileHandle = fopen($filePath, "r");

        if ($fileHandle !== false) {
            if (!$mimeType) {
                $mimeType = mime_content_type($filePath);
            }

            $request = new Request(
                'POST',
                $url,
                [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
                ],
                $fileHandle
            );
            $response = $this->client->send($request);
            fclose($fileHandle);
            if ($response->hasHeader('Content-Type') &&
                substr($response->getHeader('Content-Type')[0], 0, 16) === 'application/json') {
                    return json_decode($response->getBody()->getContents(), true);
            }
        }
        throw new RuntimeException('Unexpected response');
    }
}
