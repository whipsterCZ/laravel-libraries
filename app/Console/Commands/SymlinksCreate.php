<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * This Command creates symlinks
 * Class SymlinksCreate
 * @package App\Console\Commands
 */

class SymlinksCreate extends Command
{

	/**
	 * @return list of symlink paths [$src => $desc]
	 */
	protected function symlinks()
	{
		return [
			storage_path('app/images/someDir') => public_path('someDir'),
		];
	}

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'symlinks:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Creates predefined symlinks';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
//		$this->comment('Creating SymLinks');

		foreach ($this->symlinks() as $src => $dest) {
			$this->createSymlink($src, $dest);
			$this->comment(sprintf("%s > %s ", $src, $dest) );
		}

	}

	protected function createSymlink($src, $link) {
		return @symlink($src, $link);
	}

}