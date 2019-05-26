<?php
declare(strict_types=1);


namespace Mshavliuk\SignalEventsBundle\Command;


use Mshavliuk\SignalEventsBundle\Service\SignalHandlerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SignalHandlerCommand extends Command
{
    public const READY_MESSAGE = 'Handler is ready to handle signal';
    protected const SIGNAL_ARGUMENT = 'signal-argument';

    /**
     * @var SignalHandlerService
     */
    private $signalHandlerService;

    public function __construct(SignalHandlerService $signalHandlerService)
    {
        parent::__construct('signal-handler');

        $this->signalHandlerService = $signalHandlerService;
    }

    protected function configure()
    {
        $this->setDescription('Signal handle checker')
            ->addArgument(self::SIGNAL_ARGUMENT, InputArgument::REQUIRED, 'Name of signal to handle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $signalName = $input->getArgument(self::SIGNAL_ARGUMENT);
        $signal = constant($signalName);
        if ($signal === null) {
            $output->writeln('PHP don\'t know this type of signal');
            return 1;
        }
        $handledSignal = null;

        $this->signalHandlerService->addSignalHandler(
            static function ($signal) use ($output, $signalName) {
                $output->writeln("Successfully handle $signalName ($signal) signal");
            die(0);
        }, [$signal]);

        $output->writeln(self::READY_MESSAGE);
        while (true) {
            usleep((int)1e3);
        }
        return 2;
    }

}