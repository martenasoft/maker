<?php

namespace MartenaSoft\Maker\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class CreateBundleCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'mm:bundle';

    protected function configure()
    {
        $this
            ->setDescription('Create bundle')
            ->setHelp('This command allows you to create a bundle...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $section1 = $output->section();
        $section2 = $output->section();

        $section1->writeln('Hello');
        $section2->writeln('World!');

        $section1->overwrite('Goodbye');

        $section2->clear();
        return Command::SUCCESS;
    }
}