<?php

declare(strict_types=1);

if (file_exists('parameters_monorepo.yaml')) {
    $testDirectory = './project-base/app/tests/FrontendApiBundle/Functional';
    $phpunitConfigFile = './project-base/app/phpunit.xml';
} else {
    $testDirectory = './tests/FrontendApiBundle/Functional';
    $phpunitConfigFile = 'phpunit.xml';
}

/**
 * @param mixed $dir
 */
function getTestFilesBySubdirectory($dir)
{
    $subdirectories = glob($dir . '/*', GLOB_ONLYDIR);
    $batches = [];

    foreach ($subdirectories as $subdirectory) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($subdirectory));

        foreach ($iterator as $file) {
            if ($file->isFile() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'php') {
                $files[] = $file->getPathname();
            }
        }

        if (count($files) > 0) {
            $batches[$subdirectory] = $files;
        }
    }

    return $batches;
}

$batches = getTestFilesBySubdirectory($testDirectory);

$startTime = microtime(true);

$allTestsPass = true;

foreach ($batches as $batchName => $batch) {
    echo 'Running batch: ' . $batchName . PHP_EOL;

    $returnVar = 0;
    $command = sprintf('./vendor/bin/phpunit --colors=always --configuration %s %s', $phpunitConfigFile, $batchName);
    passthru($command, $returnVar);

    if ($returnVar !== 0) {
        $allTestsPass = false;
        echo 'A test in the batch failed. Stopping execution.' . PHP_EOL;
    } else {
        echo 'All tests batch completed successfully.' . PHP_EOL;
    }

    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    echo 'Total execution time: ' . number_format($duration, 2) . ' seconds.' . PHP_EOL;
}

if ($allTestsPass === false) {
    return '1';
}
