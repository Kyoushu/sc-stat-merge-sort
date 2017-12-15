<?php

namespace AppBundle\Command;

use AppBundle\DataProcessor\Exception\DataProcessorException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ProcessCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('process');
        $this->addArgument('sort-file', InputArgument::REQUIRED);
        $this->addArgument('people-dir', InputArgument::REQUIRED);
        $this->addArgument('output-dir', InputArgument::REQUIRED);

        $this->addOption('sort-match-column', null, InputOption::VALUE_REQUIRED, null, 'MatchValue');
        $this->addOption('sort-pid-column', null, InputOption::VALUE_REQUIRED, null, 'PID');
        $this->addOption('person-match-column', null, InputOption::VALUE_REQUIRED, null, 'MatchValue');

        $this->addOption('debug', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $peopleDir = $input->getArgument('people-dir');
        $outputDir = $input->getArgument('output-dir');
        $sortFile = $input->getArgument('sort-file');

        $sortMatchColumn = $input->getOption('sort-match-column');
        $sortPidColumn = $input->getOption('sort-pid-column');
        $personMatchColumn = $input->getOption('person-match-column');

        $debug = (bool)$input->getOption('debug');

        if(!file_exists($peopleDir)){
            throw new DataProcessorException(sprintf(
                '%s does not exist',
                $peopleDir
            ));
        }

        $processor = $this->getDataProcessorFactory()->createProcessor();
        $processor->setOutputDir($outputDir);
        $processor->setSortFilePath($sortFile);

        $processor->setSortMatchColumnName($sortMatchColumn);
        $processor->setSortPidColumnName($sortPidColumn);
        $processor->setPersonMatchColumnName($personMatchColumn);

        $processor->setDebug($debug);

        $finder = Finder::create()->files()->in($peopleDir)->name('/\.xlsx?/i');

        if($finder->count() === 0){
            throw new DataProcessorException(sprintf(
                'No .xlsx files found in %s',
                realpath($peopleDir)
            ));
        }

        foreach($finder as $file){
            $path = (string)$file;
            $output->writeln(sprintf('Processing <info>%s</info>', realpath($path)));
            $outputPath = $processor->createSortedFile($path);
            $output->writeln(sprintf('    - Created <info>%s</info>', realpath($outputPath)));
        }

        $output->write('');
        $output->write('Done');

    }

}