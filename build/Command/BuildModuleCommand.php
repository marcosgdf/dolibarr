<?php

/**
 * Copyright (C) 2005-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2015 Marcos García <marcosgdf@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Dolibarr\Build\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class BuildModuleCommand extends Command
{

	protected function configure()
	{
		$this
			->setName('build:module')
			->setDescription('Package builder for modules')
		;
	}

	/**
	 * Looks for current Dolibarr version
	 *
	 * @return string|false
	 */
	private function getDolibarrVersion()
	{
		//Look for current Dolibarr version
		$filefunc_dir = __DIR__.'/../../htdocs/filefunc.inc.php';

		if (!file_exists($filefunc_dir) || !is_readable($filefunc_dir)) {
			return false;
		}

		$filefd = fopen($filefunc_dir, 'r');

		if (!$filefd) {
			return false;
		}

		while ($line = fread($filefd, 200)) {
			if (preg_match("/define\('DOL_VERSION','(.*)'\);$/m", $line, $matches) == 1) {
				fclose($filefd);
				return $matches[1];
			}
		}

		fclose($filefd);
		return false;
	}

	/**
	 * Returns the directory of the module
	 *
	 * @param string $module Module name
	 * @return string
	 */
	private function getModuleDirectory($module)
	{
		return __DIR__.'/../../htdocs/'.$module;
	}

	/**
	 * Returns all the files contained in the module directory
	 *
	 * @param string $directory Module directory path
	 * @return array
	 */
	private function getDirectoryFiles($directory)
	{
		$files = array();

		$excluded = array(
			'.',
			'..',
			'.DS_Store',
			'Thumbs.db'
		);

		foreach (scandir($directory) as $file) {

			if (in_array($file, $excluded)) {
				continue;
			}

			if (is_dir($directory.'/'.$file)) {
				$files = array_merge($files, $this->getDirectoryFiles($directory.'/'.$file));
			} else {
				$files[] = $directory.'/'.$file;
			}
		}

		return $files;
	}

	/**
	 * Returns the module descriptor file path
	 *
	 * @param string $module Module name
	 * @return string Module descriptor file path
	 */
	private function getModuleDescriptorFile($module)
	{
		return $this->getModuleDirectory($module).'/core/modules/mod'.$module.'.class.php';
	}

	/**
	 * Returns the module version
	 * @param string $module Module name
	 * @return string|false
	 */
	private function getModuleVersion($module)
	{
		//Look for the module descriptor file
		$filefunc_dir = $this->getModuleDescriptorFile($module);

		if (!file_exists($filefunc_dir) || !is_readable($filefunc_dir)) {
			return false;
		}


		$filefd = fopen($filefunc_dir, 'r');

		if (!$filefd) {
			return false;
		}

		while ($line = fread($filefd, 200)) {
			if (preg_match('/\$this->version\s*=\s*\'(.*)\';/m', $line, $matches) == 1) {
				fclose($filefd);
				return $matches[1];
			}
		}

		fclose($filefd);
		return false;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			$sourcedir = __DIR__.'/../../';
			$destdir = __DIR__.'/../modules';

			if (!file_exists($destdir)) {
				mkdir($destdir);
			}

			$version = $this->getDolibarrVersion();

			$output->writeln(array(
				'',
				'<info>Makepack for modules version '.$version.'</info>',
//				'==========================================',
				'Source directory: '.realpath($sourcedir),
				'Target directory: '.realpath($destdir),
				''
			));

			$projectlist = array();

			$helper = $this->getHelper('question');

			$question = new Question("<question>Enter the name of your module (mymodule, myawesomemodule):</question> ");
			$modulename = $helper->ask($input, $output, $question);

			$projectlist[] = $modulename;

			foreach ($projectlist as $project) {

				if (!file_exists($this->getModuleDirectory($project))) {
					throw new Exception('Module not found in directory '.realpath($sourcedir));
				}

				//Now we look for the version of the module
				$minorVersion = $this->getModuleVersion($project);

				if (!$minorVersion) {

					while (true) {
						$question = new Question("<question>Enter value for module version:</question> ");
						$minorVersion = $helper->ask($input, $output, $question);

						if (!preg_match('/[0-9]+\.?[0-9]*\.?[0-9]*/', $minorVersion)) {
							$output->writeln('<error>Invalid version number. Format must be: MAJOR.MINOR(.BUILD)</error>');
						} else {
							break;
						}
					}

				} else {
					$output->writeln('<info>Module version: '.$minorVersion.'</info>');
				}

				//Array that contains the files the .zip file is going to store
				$files = array();

				$makepack = 'makepack-'.$project.'.conf';

				if (!file_exists($makepack) || !is_readable($makepack)) {

					$output->writeln('<comment>Could not open the config file '.$makepack.'</comment>');

					$question = new Question("<question>Would you like to add all files present in the directory? (y/n):</question> ");
					$answer = $helper->ask($input, $output, $question);

					if ($answer != 'y') {
						throw new Exception(
							"Can't open conf file $makepack. For help on building a module package,
see web page http://wiki.dolibarr.org/index.php/Module_development#Create_a_package_to_distribute_and_install_your_module"
						);
					}

					$files = $this->getDirectoryFiles($this->getModuleDirectory($project));
				} else {
					$makepack_fhandler = fopen($makepack, 'r');

					if (!$makepack_fhandler) {
						throw new Exception('Could not open '.$makepack);
					}

					while ($line = fgets($makepack_fhandler, 1024)) {
						if ($line[0] == '#') {
							continue;
						}

						$files[] = $sourcedir.'/'.$line;
					}

					fclose($makepack_fhandler);
				}

				$split = explode('.', $minorVersion);

				if (!isset($split[1])) {
					$split[1] = '0';
				}

				$filename = 'module_'.$project.'-'.$split[0].'.'.$split[1].(isset($split[2]) ? '.'.$split[2] : '').'.zip';
				$destfile = $destdir.'/'.$filename;

				if (file_exists($destfile)) {

					if ($output->isVerbose()) {
						$output->writeln('Removed existing file '.$destfile.'.');
					}

					if (!unlink($destfile)) {
						throw new Exception('Unable to remove the file '.$destfile);
					}
				}

				$zip = new \ZipArchive();

				if ($zip->open($destfile, \ZipArchive::CREATE) !== true) {
					throw new Exception('Could not create '.$filename.' file');
				}

				$filecounter = 0;

				foreach ($files as $file) {
					$copyfile = trim($file);

					if (!file_exists($copyfile)) {
						$output->writeln('<comment>File '.$copyfile.' not found</comment>');
						continue;
					}

					if ($output->isVerbose()) {
						$output->writeln('Copying '.realpath($copyfile));
					}

					preg_match('/(htdocs\/.*)/', $copyfile, $basename);

					if (!$zip->addFile($copyfile, $basename[1])) {
						throw new Exception('Error al añadir el archivo '.$copyfile.' a '.$destfile);
					}

					$filecounter++;
				}

				if (!$zip->close()) {
					throw new Exception('Error al crear el archivo .zip');
				}

				$output->writeln('<info>Created '.realpath($destfile).' with '.$filecounter.' files</info>');
			}
		} catch (Exception $e) {
			$formatter = $this->getHelper('formatter');

			$formattedBlock = $formatter->formatBlock(array(
				'ERROR',
				$e->getMessage()
			), 'error');
			return $output->writeln($formattedBlock);
		}

		return true;
	}
}