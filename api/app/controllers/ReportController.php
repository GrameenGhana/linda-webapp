<?php

class ReportController extends BaseController {

	public function index()
    {
        return $this->bedUtilization();
    }

    public function bedUtilization()
    {
        $sample = array();

        $region = Input::get('region',0);
        $hospital =  Input::get('hospital',0);
        $month = Input::get('month', date('m'));
        $year = Input::get('year', date('Y'));

        $hospitals = Location::wards($region, $hospital);

        if (sizeof($hospitals)==1) { $hospitals = array($hospitals); }

        foreach($hospitals as $h) {
            foreach($h->wards as $w) {
                array_push($sample, $w->report($h->name, $month, $year));
            }
        }

        return Response::json($sample);
    }

	public function spline()
	{
			$data = array();

			$region = Input::get('region',0);
			$hospital =  Input::get('hospital',0);
			$year = Input::get('year', date('Y'));

			$hospitals = Location::wards($region, $hospital);

			if (sizeof($hospitals)==1) { $hospitals = array($hospitals); }

			$hs = array();
			foreach($hospitals as $h) { array_push($hs, $h->id); }
            $h = implode(",", $hs);

			for ($i=0; $i<=11; $i++) {
			 	$data[$i] = Hospital::report($h, $i+1, $year);
			}

			return Response::json($data);
	}
}
