<?php namespace Owlgrin\Cashew\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Config;

/**
 * Command to generate the required migration
 */
class CashewTableCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cashew:table';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a migration for the Cashew database table';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$path = $this->createBaseMigration();

		file_put_contents($path, $this->getMigrationStub());

		$this->info('Migration created successfully!');

		$this->call('dump-autoload');
	}

	/**
	 * Creates the base file for migration o reside into
	 * @return Migration
	 */
	protected function createBaseMigration()
	{
		$name = 'create_cashew_table';

		$path = $this->laravel['path'].'/database/migrations';

		return $this->laravel['migration.creator']->create($name, $path);
	}

	/**
	 * Get the contents of the reminder migration stub.
	 *
	 * @return string
	 */
	protected function getMigrationStub()
	{
		$stub = file_get_contents(__DIR__.'/../../../stubs/migration.stub');

		$stub = str_replace('_cashew_subscriptions', Config::get('cashew::tables.subscriptions'), $stub);
		$stud = str_replace('_cashew_invoices', Config::get('cashew::tables.invoices'), $stub);

		return $stub;
	}

}