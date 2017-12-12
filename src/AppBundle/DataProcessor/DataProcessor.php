<?php

namespace AppBundle\DataProcessor;

use AppBundle\DataProcessor\Exception\DataProcessorException;

class DataProcessor
{

    const COLUMN_NAME_SORT = '_sort';
    const COLUMN_NAME_DEBUG = '_debug';

    /**
     * @var DataProcessorFactory
     */
    protected $factory;

    /**
     * @var string|null
     */
    protected $sortFilePath;

    /**
     * @var string|null
     */
    protected $outputDir;

    /**
     * @var \PHPExcel_Reader_IReader
     */
    protected $reader;

    /**
     * @var string|null
     */
    protected $sortMatchColumnName;

    /**
     * @var string|null
     */
    protected $personMatchColumnName;

    /**
     * @var string|null
     */
    protected $sortPidColumnName;

    /**
     * @param DataProcessorFactory $factory
     * @throws \PHPExcel_Reader_Exception
     */
    public function __construct(DataProcessorFactory $factory)
    {
        $this->reader = \PHPExcel_IOFactory::createReader('Excel2007');
        $this->factory = $factory;

        $this->outputDir = $this->factory->getTempDir() . '/output';
    }

    /**
     * @throws DataProcessorException
     */
    protected function assertReady()
    {
        if($this->outputDir === null){
            throw new DataProcessorException(sprintf('Output directory not specified in %s', static::class));
        }
        if($this->sortFilePath === null){
            throw new DataProcessorException(sprintf('Sort file path not specified in %s', static::class));
        }
        if($this->sortMatchColumnName === null){
            throw new DataProcessorException(sprintf('Sort match column name not specified in %s', static::class));
        }
        if($this->sortPidColumnName === null){
            throw new DataProcessorException(sprintf('Sort PID column name not specified in %s', static::class));
        }
        if($this->personMatchColumnName === null){
            throw new DataProcessorException(sprintf('Person match column name not specified in %s', static::class));
        }
    }

    /**
     * @param null|string $outputDir
     * @return $this
     */
    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getTempDir()
    {
        return $this->factory->getTempDir();
    }

    /**
     * @param null|string $sortFilePath
     * @return $this
     */
    public function setSortFilePath($sortFilePath)
    {
        $this->sortFilePath = $sortFilePath;
        return $this;
    }

    /**
     * @param null|string $sortMatchColumnName
     * @return $this
     */
    public function setSortMatchColumnName($sortMatchColumnName)
    {
        $this->sortMatchColumnName = $sortMatchColumnName;
        return $this;
    }

    /**
     * @param null|string $sortPidColumnName
     * @return $this
     */
    public function setSortPidColumnName($sortPidColumnName)
    {
        $this->sortPidColumnName = $sortPidColumnName;
        return $this;
    }

    /**
     * @param null|string $personMatchColumnName
     * @return $this
     */
    public function setPersonMatchColumnName($personMatchColumnName)
    {
        $this->personMatchColumnName = $personMatchColumnName;
        return $this;
    }

    /**
     * @param \PHPExcel_Worksheet $sheet
     * @return array
     */
    protected function createSheetHeader(\PHPExcel_Worksheet $sheet)
    {
        $highest = $sheet->getHighestRowAndColumn();
        $range = $sheet->rangeToArray(sprintf('A1:%s1', $highest['column']), null, true, true, true);
        return $range[1];
    }

    public function loadExcelData($path)
    {
        $excel = $this->reader->load($path);

        $data = [];

        $sheet = $excel->getActiveSheet();

        $header = $this->createSheetHeader($sheet);

        $highest = $sheet->getHighestRowAndColumn();
        $range = $sheet->rangeToArray(sprintf('A2:%s%s', $highest['column'], $highest['row']), null, true, true, true);

        foreach($range as $row => $columns){

            $index = $row - 2;

            $data[$index] = [];
            foreach($header as $column => $key){
                $data[$index][$key] = $columns[$column];
            }
        }

        return $data;
    }

    /**
     * @param string $personFilePath
     * @return int
     * @throws DataProcessorException
     */
    public static function resolvePersonFilePid($personFilePath)
    {
        $filename = basename($personFilePath);
        if(!preg_match('/pid(?<pid>[0-9]+)/i', $filename, $match)){
            throw new DataProcessorException(sprintf(
                'Could not determine PID for %s',
                $personFilePath
            ));
        }
        return (int)$match['pid'];
    }

    /**
     * @return array
     */
    protected function loadSortData()
    {
        $sortData = $this->loadExcelData($this->sortFilePath);
        $this->validateSortData($sortData, $this->sortFilePath);
        return $sortData;
    }

    /**
     * @param array $sortData
     */
    protected function validateSortData(array $sortData, $path)
    {
        // Validate sort data
        array_walk($sortData, function(array $columns, $index) use ($path){

            $row = $index + 2;

            if(!isset($columns[$this->sortPidColumnName])){
                throw new DataProcessorException(sprintf(
                    'Column "%s" missing from %s on row %s',
                    $this->sortPidColumnName,
                    basename($path),
                    $row
                ));
            }

            $pid = $columns[$this->sortPidColumnName];
            $matchValue = $columns[$this->sortMatchColumnName];

            if($pid === null || $pid === ''){
                throw new DataProcessorException(sprintf(
                    'Column "%s" empty on row %s in %s',
                    $this->sortPidColumnName,
                    $row,
                    basename($path)
                ));
            }

            if($matchValue === null || $matchValue === ''){
                throw new DataProcessorException(sprintf(
                    'Column "%s" empty on row %s in %s',
                    $this->sortMatchColumnName,
                    $row,
                    basename($path)
                ));
            }

        });
    }

    protected function validatePersonData(array $personData, $path)
    {
        // Validate sort data
        array_walk($personData, function(array $columns, $index) use ($path){

            $row = $index + 2;

            if(!isset($columns[$this->personMatchColumnName])){
                throw new DataProcessorException(sprintf(
                    'Column "%s" missing from %s on row %s',
                    $this->personMatchColumnName,
                    basename($path),
                    $row
                ));
            }

            $matchValue = $columns[$this->personMatchColumnName];

            if($matchValue === null || $matchValue === ''){
                throw new DataProcessorException(sprintf(
                    'Column "%s" empty on row %s in %s',
                    $this->personMatchColumnName,
                    $row,
                    basename($path)
                ));
            }

        });
    }

    public function createSortedData($personFilePath)
    {


        $this->assertReady();

        $personData = $this->loadExcelData($personFilePath);
        $this->validatePersonData($personData, $personFilePath);

        $pid = self::resolvePersonFilePid($personFilePath);

        $sortData = $this->loadExcelData($this->sortFilePath);

        $personData = array_map(function(array $columns, $index) use ($sortData, $personFilePath, $pid){

            $row = $index + 1;

            $matchValue = $columns[$this->personMatchColumnName];

            foreach($sortData as $sortIndex => $sortColumns){

                $sortRow = $sortIndex + 2;
                $sortPid = (int)$sortColumns[$this->sortPidColumnName];
                $sortMatchValue = $sortColumns[$this->sortMatchColumnName];

                if($sortPid !== $pid) continue;
                if($sortMatchValue !== $matchValue) continue;

                $debugData = [
                    'sort' => [
                        'pid_column' => $this->sortPidColumnName,
                        'match_column' => $this->sortMatchColumnName,
                        'match_value' => $sortMatchValue,
                        'row' => $sortRow,
                        'index' => $sortIndex
                    ],
                    'person' => [
                        'pid' => $pid,
                        'match_column' => $this->personMatchColumnName,
                        'match_value' => $matchValue,
                        'row' => $row
                    ]
                ];

                return array_merge(
                    $columns,
                    [
                        self::COLUMN_NAME_SORT => $sortIndex,
                        self::COLUMN_NAME_DEBUG => json_encode($debugData)
                    ]
                );

            }

            throw new DataProcessorException(sprintf(
                'Unable to determine sorting value for row %s in %s',
                $row,
                basename($personFilePath)
            ));

        }, $personData, array_keys($personData));

        usort($personData, function(array $a, array $b){
            if($a[self::COLUMN_NAME_SORT] === $b[self::COLUMN_NAME_SORT]) return 0;
            return ($a[self::COLUMN_NAME_SORT] > $b[self::COLUMN_NAME_SORT] ? 1 : -1);
        });

        $personData = array_values($personData);

        return $personData;

    }

}