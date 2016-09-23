<?php

class WardTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        DB::table('wards')->delete();

        $info = array(
            '1'=> array(
                'Surgical'=>32,
                'Female'=>37,
                'Maternity'=>37,
                'VIP'=>9,
                'MOBIL'=>28,
                'Children'=>19,
                'Recovery'=>6,
                'NICU'=>12,
                'MED Emergency'=>5,
                'Tent'=>0,
                'Theatre Recovery'=>6,
                'Obs Theatre Rec'=>3),

            '2'=> array(
                'Ward 1'=>15,
                'Ward 2'=>16,
                'Ward 3'=>17,
                'Ward 3'=>15,
                'Ward 5'=>7,
                'Maternity'=>12),

            '3'=>array (
                'Children (Paediatric)'=>20,
                'Medical (Male medical)'=>10,
                'Lying In - OBS (Female isolation)'=>25,
                'Surgical (Female surgical)'=>28,
                'Lying-In GYNAE (Gynaecological)'=>18,
                'Labour (Maternity)'=>17,
                'Theatre (Male Orthopedic)'=>10,
                'NICU (Intensive care unit)'=>8,
                'OPD Emergency (Male emergency/accident)'=>9,
                'MVA (Female Psychiatry)'=>10,
                'Cholera'=>6),

            '4'=>array(
                'GEN FEMALE'=>32,
                'KIDS MAIN'=>27,
                'KIDS ANNEX'=>13,
                'GEN MALE MED'=>30,
                'GEN MALE SUR'=>30,
                'SNR STAFF FEMALE'=>19,
                'SNR STAFF MALE'=>6,
                'SICK BABIES'=>27,
                'FEVERS'=>15,
                'OPD RECOVERY'=>14,
                'GYNAE'=>24,
                'LYING -IN'=>53),

            '5'=>array(
                'Male Medical'=>7,
                'Female Medical'=>10,
                'Meternity'=>38,
                'Paediatric'=>16),

            '6'=>array(
                'RCW'=>116,
                'SOA'=>189,
                'CWW'=>117,
                'CIW'=>117,
                'Emergency'=>198),

            '7'=>array(
                'General'=>42,
                'Maternity'=>18),

        '8'=>array(
        'Male'=>6,
        'Female'=>7,
        'Maternity'=>0,
        'Cholera'=>9),

'9'=>array(
        'Buruli'=>34,
        'General'=>20,
        'OBS&GYNAE'=>23,
        'Labour'=>12,
        'Cholera'=>12),

'10'=>array(
        'Ward 3'=>50,
        'Ward 5'=>30,
        'Ward 7'=>50,
        'Ward 8'=>30,
        'Ward 9'=>31,
        'Ward 10'=>20,
        'Ward 11'=>50,
        'Ward 12'=>50,
        'Ward 13'=>50,
        'Rehabilitation'=>30),

'11'=>array(
        'Male Medical'=>13,
        'Female Medical'=>10,
        'Paediatrics'=>26,
        'Male Surgical'=>14,
        'Female Surgical'=>14,
        'GYNAE'=>12,
        'Labour'=>18),

'12'=>array(
        'Male'=>6,
        'Children'=>13,
        'Female'=>13,
        'Materntity'=>18,
        'Emergency'=>9)
);
        $wid = 1;

        foreach($info as $hid => $wards) {
            foreach($wards as $w => $bn) {
                $type = (stripos('a'.$w, 'male')) ? 'Male' : 'General';
                $type = (stripos('a'.$w, 'female')) ? 'Female' : $type;

                Ward::create(
                    array(
                        'id' => $wid,
                        'hospital_id' => $hid,
                        'name' => $w,
                        'type' => $type,
                        'modified_by' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    )
                );
                $wid++;
            }
        }
	}
}
