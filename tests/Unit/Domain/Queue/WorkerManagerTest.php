<?php

use App\Domain\Queue\ProcessFactory;
use App\Domain\Queue\WorkerManager;
use Symfony\Component\Process\Process;

beforeEach(function () {
    $this->pidFile = sys_get_temp_dir().'/wm-'.uniqid().'.pid';
});

afterEach(function () {
    @unlink($this->pidFile);
});

function makeManager(string $pidFile, ProcessFactory $factory, \Closure $isAlive): WorkerManager
{
    return new WorkerManager(
        pidFile: $pidFile,
        factory: $factory,
        isAlive: $isAlive,
        artisanPath: '/path/to/artisan',
    );
}

function makeFakeProcessFactory(int $pid, bool $shouldStart = true): ProcessFactory
{
    return new class($pid, $shouldStart) extends ProcessFactory {
        public function __construct(private readonly int $pid, private readonly bool $shouldStart)
        {
        }

        public function makeWorker(string $artisanPath): Process
        {
            $shouldStart = $this->shouldStart;
            $pid = $this->pid;

            return new class($shouldStart, $pid) extends Process {
                public function __construct(private readonly bool $shouldStart, private readonly int $pidValue)
                {
                    parent::__construct(['echo', 'noop']);
                }

                public function start(?callable $callback = null, array $env = []): void
                {
                    if (! $this->shouldStart) {
                        throw new \RuntimeException('spawn failed');
                    }
                }

                public function getPid(): ?int
                {
                    return $this->pidValue;
                }
            };
        }
    };
}

it('spawns a worker when no PID file exists', function () {
    $factory = makeFakeProcessFactory(pid: 9999);
    $manager = makeManager($this->pidFile, $factory, fn (int $pid) => false);

    $result = $manager->ensureRunning();

    expect($result['spawned'])->toBeTrue();
    expect($result['pid'])->toBe(9999);
    expect((int) trim((string) file_get_contents($this->pidFile)))->toBe(9999);
});

it('does not spawn a second worker when an existing PID is alive', function () {
    file_put_contents($this->pidFile, '12345');

    $factory = makeFakeProcessFactory(pid: 9999);
    $manager = makeManager($this->pidFile, $factory, fn (int $pid) => $pid === 12345);

    $result = $manager->ensureRunning();

    expect($result['spawned'])->toBeFalse();
    expect($result['pid'])->toBe(12345);
});

it('respawns when the existing PID is stale', function () {
    file_put_contents($this->pidFile, '12345');

    $factory = makeFakeProcessFactory(pid: 7777);
    $manager = makeManager($this->pidFile, $factory, fn (int $pid) => false);

    $result = $manager->ensureRunning();

    expect($result['spawned'])->toBeTrue();
    expect($result['pid'])->toBe(7777);
    expect((int) trim((string) file_get_contents($this->pidFile)))->toBe(7777);
});

it('returns spawn_failed when proc start throws', function () {
    $factory = makeFakeProcessFactory(pid: 0, shouldStart: false);
    $manager = makeManager($this->pidFile, $factory, fn (int $pid) => false);

    $result = $manager->ensureRunning();

    expect($result['spawned'])->toBeFalse();
    expect($result['reason'])->toContain('spawn_failed');
});

it('prevents race-condition double spawn via flock', function () {
    // Simulate concurrent attempts by holding the lock manually.
    $factory = makeFakeProcessFactory(pid: 9999);
    $handle = fopen($this->pidFile, 'c+');
    flock($handle, LOCK_EX);

    $manager = makeManager($this->pidFile, $factory, fn (int $pid) => false);
    $result = $manager->ensureRunning();

    expect($result['spawned'])->toBeFalse();
    expect($result['reason'])->toBe('lock_busy');

    flock($handle, LOCK_UN);
    fclose($handle);
});
