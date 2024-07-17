<?php

namespace App\Command;

use App\Repository\BookRepository;
use App\Service\BookConverter;
use App\Service\BookFormatStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:book:convert',
    description: 'Add a short description for your command',
)]
class BookConvertCommand extends Command
{
    public function __construct(
        protected BookRepository $bookRepository,
        protected BookConverter $bookConverter)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('bookId', InputArgument::REQUIRED, 'Book ID')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Book format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = $input->getOption('format');
        $format = !is_string($format) || $format === '' ? BookFormatStorage::FORMAT_EPUB_KOBO : $format;
        $id = $input->getArgument('bookId');
        $book = $this->bookRepository->findOneBy(['id' => $id]);

        if ($book === null) {
            $output->writeln('Book not found');

            return 1;
        }

        $this->bookConverter->convert($book, $format);

        return Command::SUCCESS;
    }
}
