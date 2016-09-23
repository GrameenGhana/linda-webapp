<?php


class LogTablesSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        DB::table('log_otime')->delete();

        $beds = Bed::all();

        /* OTime */
        $i = 1;
        $cdate = '2015-11-01 00:00:00';
        $edate = date('Y-m-d 23:00:00');

        while(strtotime($cdate) <= strtotime($edate)) {
            foreach ($beds as $bed) {
                    $go = rand(0,1);
                    $alive = rand(0,1);
                    if ($go) {
                        $bed->toggleStatus($cdate, $edate, $alive);
                    }
            }
            $cdate = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($cdate)));
        }
	}
}
