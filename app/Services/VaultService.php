<?php

namespace App\Services;


use App\Exceptions\VaultException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;

class VaultService
{
    /** @var Client */
    private $http;

    /** @var FilesystemContract  */
    private $fs;

    public function __construct(Client $http, FilesystemContract $fs)
    {
        $this->http = $http;
        $this->fs = $fs;
    }

    public function downloadHostKeys($inventory)
    {
        foreach (explode("<br />", nl2br($inventory)) as $row) {
            if (!strpos($row, '@')) {
                throw new VaultException("Missing user. Please follow user@host pattern when adding hosts");
            }
            list($user, $host) = explode('@', trim($row), 2);

            try {
                $key = $this->download($user, $host);
                $this->fs->put($row, $key['password']);
            } catch (ClientException $e) {
                throw new VaultException($e->getMessage(), 0, $e);
            }
        }
    }

    private function download($user, $hostname)
    {
        return $this->http->get('api/deployKey', [
            'query' => ['user' => $user, 'host' => $hostname],
        ])->json();
    }
}
