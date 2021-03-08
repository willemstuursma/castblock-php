<?php

namespace WillemStuursma\CastBlock;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use WillemStuursma\CastBlock\ValueObjects\ChromeCast;
use WillemStuursma\CastBlock\ValueObjects\Status;

class ChromeCastConnector
{
    /**
     * @var string
     */
    private $goChromecastPath;

    public function __construct()
    {
        $executableFinder = new ExecutableFinder();
        $this->goChromecastPath = $executableFinder->find('go-chromecast', null, [
            dirname(__DIR__),
        ]);

        if ($this->goChromecastPath === null) {
            throw new \Exception("Cannot find go-chromecast");
        }
    }

    /**
     * @return ChromeCast[]|\Generator
     */
    public function listChromeCasts(): \Generator
    {
        $process = new Process([
            $this->goChromecastPath,
            'ls'
        ]);

        $process->start();

        $iterator = $process->getIterator($process::ITER_SKIP_ERR | $process::ITER_KEEP_OUTPUT);

        foreach ($iterator as $line) {
            yield from ChromeCast::fromGoChromeCastOutput($line);
        }
    }

    public function getStatus(ChromeCast $chromeCast): Status
    {
        $process = $this->createGoChromeCastProcess($chromeCast, [
            'status',
            '--debug',
        ]);

        $process->mustRun();

        return Status::fromGoChromeCastOutput($process->getOutput());

    }

    public function seekTo(ChromeCast $chromeCast, int $position): void
    {
        $process = $this->createGoChromeCastProcess($chromeCast, [
            'seek-to',
            $position,
        ]);

        $process->mustRun(); // no output
    }

    private function createGoChromeCastProcess(ChromeCast $chromeCast, array $command): Process
    {
        [$address, $port] = explode(":", $chromeCast->getAddress());

        $process = new Process(array_merge([
            $this->goChromecastPath,
        ], $command, [
            '--addr',
            $address,
            '--port',
            $port
        ]));

        return $process;
    }
}