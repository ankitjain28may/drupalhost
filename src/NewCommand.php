<?php

namespace Ankitjain28may\DrupalHost\Console;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Exception;

class NewCommand extends Command
{

    /**
     * commands to be executed
     *
     * @var array
     */
    private $commands;

    /**
     * constructor
     *
     * @param array $commands
     */
    public function __construct(array $commands) {
        $this->commands = $commands;
        parent::__construct();
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Drupal application')
            ->addArgument('name', InputArgument::OPTIONAL)
            ->addArgument('version', InputArgument::OPTIONAL, 'Installs the entered release, pass composer for drupal-composer release')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! extension_loaded('zip')) {
            throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
        }
        $version = '8.8.5';
        if ($input->getArgument('version') && preg_match("/^8[0-9.]*$/", $input->getArgument('version'))) {
            $version = $input->getArgument('version');
        } else if ($input->getArgument('version') && $input->getArgument('version') == 'composer') {
            $version = 'project-8.x';
        } else if (!preg_match("/^[8]*$/", $input->getArgument('version'))) {
            throw new RuntimeException('Invalid Version, valid only for Drupal 8.* versions');
        }

        $directory = ($input->getArgument('name')) ? getcwd().'/'.$input->getArgument('name') : getcwd();

        if (! $input->getOption('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        $output->writeln('<info>Crafting application...</info>');

        $this->download($zipFile = $this->makeFilename(), $version)
             ->extract($zipFile, $directory)
             ->cleanUp($zipFile);

        $composer = $this->findComposer();
        array_push($this->commands, $version, $composer);
        $process = new Process($this->commands, $directory, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
        return 0;
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd().'/drupal_'.md5(time().uniqid()).'.zip';
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function download($zipFile, $version)
    {
        $url = 'https://ftp.drupal.org/files/projects/drupal-' . $version . '.zip';

        if ($version == "project-8.x") {
            $url = 'https://github.com/drupal-composer/drupal-project/archive/8.x.zip';
        }

        try {
            $response = (new Client)->get($url);
        }
        catch (RequestException $e) {
            throw new RuntimeException(Psr7\str($e->getResponse()));
        }
        catch (Exception $e) {
            throw new RuntimeException(Psr7\str($e->getResponse()));
        }

        file_put_contents($zipFile, $response->getBody());

        return $this;
    }

    /**
     * Extract the Zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;

        $archive->open($zipFile);

        $archive->extractTo($directory);

        $archive->close();

        return $this;
    }

    /**
     * Clean-up the Zip file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);

        @unlink($zipFile);

        return $this;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPath = getcwd().'/composer.phar';

        if (file_exists($composerPath)) {
            return '"'.PHP_BINARY.'" '.$composerPath;
        }

        return 'composer';
    }
}