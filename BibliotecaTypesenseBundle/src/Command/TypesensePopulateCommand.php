<?php

namespace Biblioteca\TypesenseBundle\Command;

use Biblioteca\TypesenseBundle\CollectionName\AliasName;
use Biblioteca\TypesenseBundle\Mapper\MapperInterface;
use Biblioteca\TypesenseBundle\Mapper\MapperLocator;
use Biblioteca\TypesenseBundle\Populate\PopulateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'biblioteca:typesense:populate',
    description: 'Populate Typesense collections',
)]
class TypesensePopulateCommand extends Command
{
    public function __construct(
        private PopulateService $populateService,
        private MapperLocator $mapperLocator,
        private AliasName $aliasName,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->mapperLocator->count();
        if ($count === 0) {
            $io->warning('No mappers found. Declare at least one service implementing '.MapperInterface::class);

            return Command::SUCCESS;
        }

        $progress = new ProgressBar($output, 0);

        foreach ($this->mapperLocator->getMappers() as $mapper) {
            $name = $this->aliasName->getName($mapper->getMapping()->getName());

            $io->writeln('Creating collection '.$name);
            $this->populateService->createCollection($name, $mapper);

            try {
                $io->writeln('Filling collection '.$name);
                $progress->start($mapper->getDataCount());
                foreach ($this->populateService->fillCollection($name, $mapper) as $_) {
                    $progress->advance();
                }
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                $this->populateService->deleteCollection($name);
            } finally {
                $progress->finish();
            }
            $progress->clear();

            if ($this->aliasName->isAliasEnabled()) {
                $io->writeln(sprintf('Aliasing collection %s to %s', $name, $mapper->getMapping()->getName()));
                $this->aliasName->switch($mapper->getMapping(), $name);
            }

            $progress->clear();
        }
        $progress->finish();

        $io->success('Finished');

        return Command::SUCCESS;
    }
}
