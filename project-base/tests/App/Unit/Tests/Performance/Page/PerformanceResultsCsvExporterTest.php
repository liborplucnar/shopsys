<?php

declare(strict_types=1);

namespace Tests\App\Unit\Tests\Performance\Page;

use PHPUnit\Framework\TestCase;
use Tests\App\Performance\JmeterCsvReporter;
use Tests\App\Performance\Page\PerformanceResultsCsvExporter;
use Tests\App\Performance\Page\PerformanceTestSample;

class PerformanceResultsCsvExporterTest extends TestCase
{
    public function testExportJmeterCsvReportWritesExpectedHeader(): void
    {
        $outputFilename = $this->getTemporaryFilename();

        $performanceResultsCsvExporter = $this->createPerformanceResultsCsvExporter();

        $performanceResultsCsvExporter->exportJmeterCsvReport(
            $this->getPerformanceTestSamples(),
            $outputFilename
        );

        $expectedLine = [
            'timestamp',
            'elapsed',
            'label',
            'responseCode',
            'success',
            'URL',
            'Variables',
        ];

        $this->assertCsvRowEquals($expectedLine, $outputFilename, 0);
    }

    public function testExportJmeterCsvReportRoundsDuration(): void
    {
        $outputFilename = $this->getTemporaryFilename();

        $performanceResultsCsvExporter = $this->createPerformanceResultsCsvExporter();

        $performanceResultsCsvExporter->exportJmeterCsvReport(
            $this->getPerformanceTestSamples(),
            $outputFilename
        );

        $line = $this->getCsvLine($outputFilename, 1);

        $this->assertEquals(1000, $line[1]);
    }

    /**
     * @return string
     */
    private function getTemporaryFilename(): string
    {
        return tempnam(sys_get_temp_dir(), 'test');
    }

    /**
     * @return \Tests\App\Performance\Page\PerformanceTestSample[]
     */
    private function getPerformanceTestSamples(): array
    {
        $performanceTestSamples = [];

        $performanceTestSamples[] = new PerformanceTestSample(
            'routeName1',
            'url1',
            1000.1,
            10,
            200,
            true
        );
        $performanceTestSamples[] = new PerformanceTestSample(
            'routeName2',
            'url2',
            2000,
            20,
            301,
            true
        );

        return $performanceTestSamples;
    }

    /**
     * @param array $expectedLine
     * @param string $filename
     * @param int $lineIndex
     */
    private function assertCsvRowEquals(array $expectedLine, string $filename, int $lineIndex): void
    {
        $actualLine = $this->getCsvLine($filename, $lineIndex);

        $this->assertSame($expectedLine, $actualLine);
    }

    /**
     * @param string $filename
     * @param int $lineIndex
     * @return array
     */
    private function getCsvLine(\string $filename, \int $lineIndex): array
    {
        $handle = fopen($filename, 'r');

        // seek to $rowIndex
        for ($i = 0; $i < $lineIndex; $i++) {
            fgetcsv($handle);
        }

        return fgetcsv($handle);
    }

    /**
     * @return \Tests\App\Performance\Page\PerformanceResultsCsvExporter
     */
    private function createPerformanceResultsCsvExporter(): \Tests\App\Performance\Page\PerformanceResultsCsvExporter
    {
        return new PerformanceResultsCsvExporter(new JmeterCsvReporter());
    }
}
