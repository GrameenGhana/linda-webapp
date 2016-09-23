<?php

class LocationTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        DB::table('locations')->delete();
     
        $regions = array('Ashanti Region', 
                         'Brong Ahafo Region',
                         'Central Region',
                         'Eastern Region',
                         'Greater Accra',
                         'Northern Region',
                         'Upper East Region',
                         'Upper West Region',
                         'Volta Region',
                         'Western Region');

        $id = 1;
        foreach($regions as $r) {
            Location::create( array('id'=> $id, 'country' => 'Ghana', 'region' => $r, 
                                    'modified_by' => 1, 'created_at' => date('Y-m-d')));
            $id++;
        }
	}
}
