<?php namespace App\Http\Controllers\Git;

use App\Http\Controllers\Controller;
use App\Model\Repo;
use App\Traits\ManageFilesystem;
use Illuminate\Http\Response;

/**
 * Class GitInfoController
 */
class GitInfoController extends Controller
{
    /**
     * @param string $line
     * @return string
     */
    private function writeLn($line)
    {
        $line = "$line\n";
        return sprintf("%04x", strlen($line) + 4) . "$line";
    }

    /**
     * @param Repo $repo
     * @return Response
     */
    public function getRefs(Repo $repo)
    {
        $out = "001f# service=git-receive-pack\n0000";

        $i = 0;
        /** @var \Gitonomy\Git\Reference\Branch $item */
        foreach ($repo->git()->getReferences() as $item) {
            if ($i == 0) {
                $out .= $this->writeLn("{$item->getCommitHash()} {$item->getRevision()}\0 report-status delete-refs side-band-64k quiet ofs-delta agent=git/1.8.3.1");
            } else {
                $out .= $this->writeLn($item->getCommitHash() . " " . $item->getRevision());
            }
            $i++;
        }

        return new Response($out . "0000", 200, [
            'Content-Type' => 'application/x-git-receive-pack-advertisement'
        ]);
    }

    /**
     * @param Repo $repo
     * @param string $inv
     */
    public function receivePack(Repo $repo, $inv)
    {
        $repo->checkHooks();

        $input = file_get_contents("php://input");
        header("Content-type: application/x-git-receive-pack-result");
        header('X-Accel-Buffering: no');
        ini_set("max_execution_time", 0);
        ini_set("implicit_flush", true);
        ob_implicit_flush(1);

        $input = $this->gzBody($input);
        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
        ];

        $p = proc_open("INVENTORY=$inv git-receive-pack --stateless-rpc {$repo->repoPath()}", $descriptors, $pipes);
        if (is_resource($p)) {
            fwrite($pipes[0], $input);
            fclose($pipes[0]);
            while (!feof($pipes[1])) {
                $data = fread($pipes[1], 8192);
                @ob_end_flush();
                echo $data;
            }

            fclose($pipes[1]);
            proc_close($p);
        }
        exit;
    }

    /**
     * @param string $gzData
     * @return string
     */
    public function gzBody($gzData)
    {
        if (substr($gzData, 0, 3) == "\x1f\x8b\x08") {
            $i = 10;
            $flg = ord(substr($gzData, 3, 1));
            if ($flg > 0) {
                if ($flg & 4) {
                    list($xlen) = unpack('v', substr($gzData, $i, 2));
                    $i = $i + 2 + $xlen;
                }
                if ($flg & 8) {
                    $i = strpos($gzData, "\0", $i) + 1;
                }
                if ($flg & 16) {
                    $i = strpos($gzData, "\0", $i) + 1;
                }
                if ($flg & 2) {
                    $i = $i + 2;
                }
            }
            return gzinflate(substr($gzData, $i, -8));
        } else {
            return $gzData;
        }
    }
}
