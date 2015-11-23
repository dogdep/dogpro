<?php namespace App\Console;

use App\Jobs\Release\PrepareReleaseJob;
use App\Model\Release;
use App\Model\Repo;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DeployCommand
 */
class DeployCommand extends Command
{
    use DispatchesJobs;

    protected function configure()
    {
        $this->setName("deploy");
        $this->addArgument('repo', InputArgument::REQUIRED, "Repository to use");
        $this->addArgument('inventory', InputArgument::REQUIRED, "Inventory name");
        $this->addOption('commit', 'c', InputOption::VALUE_REQUIRED, "Commit hash");
    }

    public function fire(Dispatcher $bus)
    {
        $repo = $this->loadRepo();
        $inv = $this->loadInventory($repo);
        $commit = $this->getCommit($repo);

        $release = Release::create([
            'repo_id' => $repo->id,
            'commit' => $commit->getHash(),
            'status' => Release::QUEUED,
            'roles' => ["dogpro.deploy"],
            'inventory_id' => $inv->id,
            'user_id' => 1,
            'params' => [],
        ]);

        $this->output->getFormatter()->setDecorated(true);
        $release->logger()->setOutput($this->output);
        try {
            $bus->dispatchNow(new PrepareReleaseJob($release, true));
        } catch (\Exception $e) {
            $this->output->writeln($e->getMessage());
        }
    }

    /**
     * @param $repo
     * @return mixed
     */
    protected function getCommit(Repo $repo)
    {
        $input = $this->option('commit');
        if ($commit = $input) {
            return $repo->git()->getCommit($commit);
        }

        return $repo->git()->getHeadCommit();
    }

    /**
     * @param $repo
     * @return mixed
     */
    protected function loadInventory(Repo $repo)
    {
        $input = $this->argument('inventory');

        foreach ($repo->inventories as $inventory) {
            if ($inventory->name == $input || $inventory->id == $input) {
                return $inventory;
            }
        }

        throw new \InvalidArgumentException("Could not find inventory $input");
    }

    /**
     * @return mixed|static
     */
    protected function loadRepo()
    {
        $input = $this->argument('repo');
        if ($repo = Repo::whereId($input)->first()) {
            return $repo;
        }

        if ($repo = Repo::whereUrl($input)->first()) {
            return $repo;
        }
        throw new \InvalidArgumentException("Could not find repo $input");
    }

}
