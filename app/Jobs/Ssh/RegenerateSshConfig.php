<?php namespace App\Jobs\Ssh;

use App\Jobs\Job;
use App\Model\SshKey;
use App\Traits\ManageFilesystem;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class RegenerateSshConfig
 */
class RegenerateSshConfig extends Job implements ShouldQueue, SelfHandling
{
    use ManageFilesystem;

    /**
     *
     */
    public function handle()
    {
        $config = "";
        foreach (SshKey::all() as $key) {
            chown($key->path(), 'nobody');
            chgrp($key->path(), 'nobody');
            chmod($key->path(), 0644);

            $host = $key->host();
            $user = null;

            if (strpos($host, '@')) {
                list($user, $host) = explode('@', $host);
            }

            $config .= "Host {$host}\n";
            $config .= "  HostName {$host}\n";
            if ($user) {
                $config .= "  User {$user}\n";
            }
            $config .= "  IdentityFile {$key->path()}\n";
            $config .= "  StrictHostKeyChecking no\n";
            $config .= "  UserKnownHostsFile=/dev/null\n";
            $config .= "\n";
        }

        $this->fs()->put(storage_path("ssh_config"), $config);
    }
}
