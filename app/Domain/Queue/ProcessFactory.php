<?php

namespace App\Domain\Queue;

use Symfony\Component\Process\Process;

/**
 * Pembuat Symfony Process untuk worker queue.
 *
 * Dipisah agar mudah di-mock di test (R-D2 / 5.7).
 */
class ProcessFactory
{
    public function makeWorker(string $artisanPath): Process
    {
        $php = (PHP_BINARY !== '' ? PHP_BINARY : 'php');

        $process = new Process([
            $php,
            $artisanPath,
            'queue:work',
            '--queue=seleksi,notifications',
            '--stop-when-empty',
            '--tries=3',
        ]);
        $process->setOptions(['create_new_console' => true]);
        $process->setTimeout(null);

        return $process;
    }
}
