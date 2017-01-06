<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 04-Jan-17
 * Time: 01:55
 */

namespace App\Console;
use Instante\Helpers\FileSystem;
use Nette;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteTempCommand extends Command
{
	protected function configure()
	{
		$this->setName('app:delete-temp')
			->setDescription('Deletes temp files')
			->setHelp('Deletes all files and subfolders of temp/cache/');
	}



	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try{
			Nette\Utils\FileSystem::delete('temp\cache\\');
			Nette\Utils\FileSystem::createDir('temp\cache\\');
		} catch (Nette\IOException $e) {
			$output->writeln(['Cannot delete files:']);
			$output->writeln($e->getMessage());
			return 1;
		}
		$output->writeln(['Deleting files ...']);
		return 0;
	}



}