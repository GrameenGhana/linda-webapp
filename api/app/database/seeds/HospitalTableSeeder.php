<?php

class HospitalTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        DB::table('hospitals')->delete();
        
        Hospital::create(
                array(
                    'id'=>1,
                    'name'    => 'Ridge Hospital',
                    'lat' => '5.5626843',
                    'long' => '-0.2011614',
                    'location_id'    => 1,
                    'modified_by' => 1,
			        'created_at' => date('Y-m-d')
		        )
            );

         Hospital::create(
                array(
                    'id'=>2,
                    'name'    => 'Achimota Hospital',
                    'lat' => '5.629496',
                    'long' => '-0.2192158',
                    'location_id'    => 1,
                    'modified_by' => 1,
			        'created_at' => date('Y-m-d')
		        )
            );

           Hospital::create(
                array(
                    'id'=>3,
                    'name'    => 'La General Hospital',
                    'lat' => '5.5557465',
                    'long' => '-0.1687861',
                    'location_id'    => 1,
                    'modified_by' => 1,
			        'created_at' => date('Y-m-d')
		        )
            );

            Hospital::create(
                array(
                    'id'=> 4,
                    'name'    => 'Tema General Hospital',
                    'lat' => '5.6760408',
                    'long' => '-0.0266759',
                    'location_id'    => 1,
                    'modified_by' => 1,
			        'created_at' => date('Y-m-d')
		        )
            );

        Hospital::create(
            array(
                'id'=> 5,
                'name'    => 'Maamobi General Hospital',
                'lat' => '5.5933035',
                'long' => '-0.1986117',
                'location_id'    => 1,
                'modified_by' => 1,
                'created_at' => date('Y-m-d')
            )
        );

        Hospital::create(
            array(
                'id'=> 6,
                'name'    => 'PML Children Hospital',
                'lat' => '5.5374116',
                'long' => '-0.2295862',
                'location_id'    => 1,
                'modified_by' => 1,
                'created_at' => date('Y-m-d')
            )
        );

        Hospital::create(
            array(
                'id'=> 7,
                'name'    => 'Ada East District Hospital',
                'lat' => '5.8356185',
                'long' => '0.5750512',
                'location_id'    => 1,
                'modified_by' => 1,
                'created_at' => date('Y-m-d')
            )
        );

        Hospital::create(
            array(
                'id'=> 8,
                'name'    => 'Ga South Municipal Hospital',
                'lat' => '5.5657299',
                'long' => '-0.2918809',
                'location_id'    => 1,
                'modified_by' => 1,
                'created_at' => date('Y-m-d')
            )
        );

        Hospital::create(
            array(
                'id'=> 9,
                'name'    => 'Ga West Municipal Hospital',
                'lat' => '5.7067254',
                'long' => '-0.3053642,16',
                'location_id'    => 1,
                'modified_by' => 1,
                'created_at' => date('Y-m-d')
            )
        );

        Hospital::create(
            array(
                'id'=> 10,
                'name'    => 'Pantang Mental Hospital',
                'lat' => '5.7181108',
                'long' => '-0.1903245',
                'location_id'    => 1,
                'modified_by' => 1,
                'created_at' => date('Y-m-d')
            )
        );

        Hospital::create(
            array(
                'id'=> 11,
                'name'    => 'Lekma Hospital',
                'lat' => '5.6034112',
                'long' => '-0.1221505',
                'location_id'    => 1,
                'modified_by' => 1,
                'created_at' => date('Y-m-d')
            )
        );


        Hospital::create(
            array(
                'id'=> 12,
                'name'    => 'Shai Osudoku District Hospital',
                'lat' => '5.8854492',
                'long' => '-0.0940259',
                'location_id'    => 1,
                'modified_by' => 1,
                'created_at' => date('Y-m-d')
            )
        );

	}
}
