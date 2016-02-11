<?php namespace App\Git;

use phpseclib\Crypt\RSA;

/**
 * Class RSA
 */
class SSH
{
    /**
     * @var SSHAgent|null
     */
    private $agent = null;

    public static function generateKeyPair($comment = 'dogpro') {
        $rsa = new RSA();
        $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);
        $rsa->setComment($comment);
        return $rsa->createKey();
    }

    public static function writeKeyPair($comment, $publicPath, $privatePath)
    {
        $pair = self::generateKeyPair($comment);

        try {
            self::writeFile($publicPath, $pair['publickey']);
            self::writeFile($privatePath, $pair['privatekey']);
        } catch (\Exception $e) {
            unlink($publicPath);
            unlink($privatePath);
            throw $e;
        }

        return $pair;
    }

    private static function writeFile($path, $contents)
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
            throw new \RuntimeException("Failed to create dir: $dir");
        }

        if (@file_put_contents($path, $contents) === false) {
            throw new \RuntimeException("Failed to write file $path");
        }

        if (!@chmod($path, 0600)) {
            throw new \RuntimeException("Failed to set owner for file $path");
        }
    }

    public function getAgent()
    {
        if (is_null($this->agent)) {
            return $this->agent = SSHAgent::start();
        }

        return $this->agent;
    }
}
