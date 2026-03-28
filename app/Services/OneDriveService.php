<?php

namespace App\Services;

use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Microsoft\Graph\Model\DriveItem;

class OneDriveService
{
    protected $graph;
    protected $userId;

    public function __construct()
    {
        $tenantId     = config('services.microsoft.tenant_id');
        $clientId     = config('services.microsoft.client_id');
        $clientSecret = config('services.microsoft.client_secret');
        $this->userId = config('services.microsoft.user_id');

        $guzzle = new Client();
        $response = $guzzle->post(
            "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
            [
                'form_params' => [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'scope'         => 'https://graph.microsoft.com/.default',
                    'grant_type'    => 'client_credentials',
                ],
            ]
        );

        $tokenData = json_decode($response->getBody()->getContents());

        if (!$tokenData || !isset($tokenData->access_token)) {
            throw new \RuntimeException('Failed to obtain Microsoft access token');
        }

        $this->graph = new Graph();
        $this->graph->setAccessToken((string) $tokenData->access_token);
    }

    /**
     * Upload ไฟล์ไปยัง OneDrive และคืน share URL
     */
    public function upload(string $localPath, string $fileName, string $folder = 'Bookings'): string
    {
        $fileContent = file_get_contents($localPath);

        $result = $this->graph
            ->createRequest('PUT', "/users/{$this->userId}/drive/root:/{$folder}/{$fileName}:/content")
            ->addHeaders(['Content-Type' => 'application/octet-stream'])
            ->attachBody($fileContent)
            ->setReturnType(DriveItem::class)
            ->execute();

        /** @var Model\DriveItem $driveItem */
        $driveItem = $result;

        $shareResponse = $this->graph
            ->createRequest('POST', "/users/{$this->userId}/drive/items/{$driveItem->getId()}/createLink")
            ->attachBody(['type' => 'view', 'scope' => 'organization'])
            ->execute();

        return $shareResponse->getBody()['link']['webUrl'];
    }
}
