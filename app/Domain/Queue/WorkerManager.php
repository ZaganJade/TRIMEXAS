<?php

namespace App\Domain\Queue;

use Symfony\Component\Process\Process;

/**
 * Self-spawning queue worker manager (Decision D2).
 *
 * - Cek `storage/app/queue-worker.pid`; jika tidak ada / stale, spawn worker.
 * - Worker: `php artisan queue:work --queue=seleksi --stop-when-empty`.
 * - Atomic file lock (flock) dipakai untuk mencegah race-condition saat dua admin
 *   trigger batch bersamaan.
 *
 * Disiapkan untuk dipakai dari controller / job. Process spawning di-isolate ke
 * `ProcessFactory` agar mudah di-mock di test (R-D2).
 */
class WorkerManager
{
    public function __construct(
        private readonly string $pidFile,
        private readonly ProcessFactory $factory,
        /** @var \Closure(int):bool */
        private readonly \Closure $isAlive,
        private readonly string $artisanPath,
    ) {
    }

    public static function default(string $basePath): self
    {
        $pidDir = $basePath.'/storage/app';
        if (! is_dir($pidDir)) {
            @mkdir($pidDir, 0775, true);
        }

        return new self(
            pidFile: $pidDir.'/queue-worker.pid',
            factory: new ProcessFactory(),
            isAlive: \Closure::fromCallable([self::class, 'processIsAlive']),
            artisanPath: $basePath.'/artisan',
        );
    }

    /**
     * Pastikan ada satu worker hidup. Idempotent: aman dipanggil beberapa kali.
     *
     * @return array{spawned: bool, pid: ?int, reason: ?string}
     */
    public function ensureRunning(): array
    {
        $handle = @fopen($this->pidFile, 'c+');
        if ($handle === false) {
            return ['spawned' => false, 'pid' => null, 'reason' => 'cannot_open_pid_file'];
        }

        try {
            if (! flock($handle, LOCK_EX | LOCK_NB)) {
                return ['spawned' => false, 'pid' => null, 'reason' => 'lock_busy'];
            }

            $existing = trim((string) stream_get_contents($handle));
            $pid = is_numeric($existing) ? (int) $existing : null;

            if ($pid !== null && ($this->isAlive)($pid)) {
                return ['spawned' => false, 'pid' => $pid, 'reason' => 'already_running'];
            }

            $process = $this->factory->makeWorker($this->artisanPath);
            try {
                $process->start();
            } catch (\Throwable $e) {
                return [
                    'spawned' => false,
                    'pid' => null,
                    'reason' => 'spawn_failed: '.$e->getMessage(),
                ];
            }

            $newPid = $process->getPid();
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string) $newPid);

            return ['spawned' => true, 'pid' => $newPid, 'reason' => null];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public static function processIsAlive(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (\function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        // Windows fallback via tasklist.
        $output = [];
        $ret = 0;
        @exec(sprintf('tasklist /FI "PID eq %d" 2>NUL', $pid), $output, $ret);

        foreach ($output as $line) {
            if (str_contains($line, (string) $pid)) {
                return true;
            }
        }

        return false;
    }
}
