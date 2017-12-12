<?php


namespace AppBundle\DataProcessor;

class DataProcessorFactory
{

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @param string $tempDir
     */
    public function __construct($tempDir)
    {
        $this->tempDir = $tempDir;

        if(!file_exists($tempDir)){
            mkdir($tempDir, 0777, true);
        }
    }

    /**
     * @return string
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @return DataProcessor
     */
    public function createProcessor()
    {
        return new DataProcessor($this);
    }

}