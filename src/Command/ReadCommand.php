<?php

namespace Alcohol\PasteBundle\Command;

use Alcohol\PasteBundle\Entity\PasteManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadCommand extends Command
{
    /** @var PasteManager */
    protected $manager;

    /**
     * @inheritDoc
     */
    public function __construct(PasteManager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('paste:read')
            ->setDescription('Returns a count of currently stored pasties.')
            ->addArgument('code', InputArgument::REQUIRED, 'Code of paste to read.')
            ->addOption('--include-body', '-b', InputOption::VALUE_NONE, 'Include body in output.')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paste = $this->manager->read($input->getArgument('code'));

        $output
            ->getFormatter()
            ->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']))
        ;

        $output->writeln(sprintf('<bold>Code:</bold> %s', $paste->getCode()));
        $output->writeln(sprintf('<bold>Token:</bold> %s', $paste->getToken()));

        if ($input->getOption('include-body')) {
            $output->writeln(sprintf('<bold>Body:</bold> %s', $paste->getBody()));
        }

        return 0;
    }
}