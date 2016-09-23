<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('UserTableSeeder');
		$this->call('LocationTableSeeder');
        //$this->call('HospitalTableSeeder');
        //$this->call('WardTableSeeder');
        //$this->call('BedTableSeeder');
        //$this->call('LogTablesSeeder');
	}

}
