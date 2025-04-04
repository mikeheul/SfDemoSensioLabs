<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientService
{
    public function __construct(private HttpClientInterface $httpClient) {}

    public function fetchExternalData(): array
    {
        $response = $this->httpClient->request('GET', 'https://opentdb.com/api.php?amount=10');
        return $response->toArray();
    }
}