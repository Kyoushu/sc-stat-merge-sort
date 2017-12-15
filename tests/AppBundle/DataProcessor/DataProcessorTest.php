<?php

namespace Tests\AppBundle\DataProcessor;

use AppBundle\DataProcessor\DataProcessor;
use AppBundle\DataProcessor\DataProcessorFactory;
use PHPUnit\Framework\TestCase;

class DataProcessorTest extends TestCase
{

    /**
     * @return \AppBundle\DataProcessor\DataProcessor
     */
    protected function createProcessor()
    {
        $factory = new DataProcessorFactory(__DIR__ . '/../../../var/test/' . uniqid());
        return $factory->createProcessor();
    }

    public function testLoadExcelData()
    {
        $processor = $this->createProcessor();

        $data = $processor->loadExcelData(__DIR__ . '/../Resources/sample_data/sort_data.xlsx');

        $this->assertEquals([
            ['PID' => 1, 'MatchValue' => 12.321],
            ['PID' => 1, 'MatchValue' => 14.392],
            ['PID' => 2, 'MatchValue' => 14.392],
            ['PID' => 2, 'MatchValue' => 12.321],
            ['PID' => 3, 'MatchValue' => 28.134],
            ['PID' => 3, 'MatchValue' => 14.392],
            ['PID' => 3, 'MatchValue' => 12.321]
        ], $data);

        $data = $processor->loadExcelData(__DIR__ . '/../Resources/sample_data/people/pid1.xlsx');

        $this->assertEquals([
            ['MatchValue' => 14.392, 'ExampleData' => 'PID1_B'],
            ['MatchValue' => 12.321, 'ExampleData' => 'PID1_A']
        ], $data);
    }

    public function testResolvePersonFilePid()
    {
        $this->assertEquals(123, DataProcessor::resolvePersonFilePid('/foo/bar/PID123.xlsx'));
        $this->assertEquals(234, DataProcessor::resolvePersonFilePid('/foo/bar/Pid234.xlsx'));
        $this->assertEquals(456, DataProcessor::resolvePersonFilePid('/foo/bar/foo_bar_Pid456.xlsx'));
    }

    public function testCreateData()
    {
        $processor = $this->createProcessor();

        $processor->setSortFilePath(__DIR__ . '/../Resources/sample_data/sort_data.xlsx');

        $processor->setSortMatchColumnName('MatchValue');
        $processor->setSortPidColumnName('PID');
        $processor->setPersonMatchColumnName('MatchValue');

        $data = $processor->createSortedData(__DIR__ . '/../Resources/sample_data/people/pid1.xlsx');
        $this->assertCount(2, $data);
        $this->assertEquals('PID1_A', $data[0]['ExampleData']);
        $this->assertEquals('PID1_B', $data[1]['ExampleData']);

        $data = $processor->createSortedData(__DIR__ . '/../Resources/sample_data/people/ASJHDGJHASGDJH_ppt2.xlsx');
        $this->assertCount(2, $data);
        $this->assertEquals('PID2_B', $data[0]['ExampleData']);
        $this->assertEquals('PID2_A', $data[1]['ExampleData']);

        $data = $processor->createSortedData(__DIR__ . '/../Resources/sample_data/people/pid3.xlsx');
        $this->assertCount(3, $data);
        $this->assertEquals('PID3_C', $data[0]['ExampleData']);
        $this->assertEquals('PID3_B', $data[1]['ExampleData']);
        $this->assertEquals('PID3_A', $data[2]['ExampleData']);
    }

}