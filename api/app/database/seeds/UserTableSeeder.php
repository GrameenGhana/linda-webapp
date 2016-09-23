<?php

class UserTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        DB::table('users')->delete();
        User::create(
            array(
                'id' => 1,
                'type'    => 'Super Admin',
			    'name'     => 'David Hutchful',
                'title'    => 'Director of Technology Innovations',
			    'email'    => 'kojo',
			    'password' => Hash::make(md5('kojo')),
                'location_id' => null,
		    )
        );

        User::create(
            array(
                'id'=>2,
                'type'    => 'GF User',
                'name'     => 'Akuba Dolphyne',
                'title'    => 'mHealth Project Manager',
                'email'    => 'adolphyne',
                'password' => Hash::make(md5('cat')),
                'location_id' => null,
            )
        );

        User::create(
            array(
                'id'=>3,
                'type'    => 'GHS Supervisor',
                'name'     => 'GHS Supervisor',
                'title'    => 'Greater Accra Regional Director',
                'email'    => 'rd',
                'password' => Hash::make(md5('cat')),
                'location_id' => 1,
            )
        );

        User::create(
            array(
                'id'=>4,
                'type'    => 'GHS Call Center',
                'name'     => 'GHS Call Center',
                'title'    => 'GHS Call Center Agent',
                'email'    => 'callcenter',
                'password' => Hash::make(md5('cat')),
                'location_id' => 1,
            )
        );

        User::create(
            array(
                'id'=>5,
                'type'    => 'GHS Local Management Team',
                'name'     => 'GHS LMT',
                'title'    => 'Principal Nurse Officer',
                'hospital_id' => 1,
                'email'    => 'lmt',
                'password' => Hash::make(md5('cat')),
                'location_id' => 1,
            )
        );

        User::create(
            array(
                'id'=>6,
                'type'    => 'GHS User',
                'name'     => 'GHS Nurse',
                'title'    => 'Principal Nurse Officer',
                'hospital_id' => 1,
                'email'    => 'nurse',
                'password' => Hash::make(md5('cat')),
                'location_id' => 1,
            )
        );
	}

}
