<?php

class HospitalController extends BaseController {

	public function index()
	{
        return Response::json(Hospital::all());
	}

	public function show($id)
	{
        return Response::json(Hospital::find($id));
	}

    public function store()
    {
        $error = false;
        $responseCode = 200;
        $data = array('messages'=>'Ok');

        $rules = array('data' => 'required');
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->messages();
            $error = true;
            $responseCode = 400;
            $data['messages'] = $errors->toArray();
        } else {
            $data = Input::get('data',null);
            if ($data != null) {
                $h = new Hospital();
                $h->name = $data['name'];
                $h->phone = (isset($data['phone'])) ? $data['phone'] : ''; 
                $h->lat = (isset($data['lat'])) ? $data['lat'] : ''; 
                $h->long = (isset($data['long'])) ? $data['long'] : ''; 
                $h->type = $data['type'];
                $h->location_id = $data['location'];
                $h->created_at = date('Y-m-d h:m:s');
                $h->modified_by = Input::get('user_id',1); 
                $h->save();
            } else {
                $error = true;
                $responseCode = 400;
                $data['messages'] = 'Could not save hospital information.'; 
            }
        }

        return Response::json(array('error' => $error, 'data' => $data), $responseCode);
    }

    public function destroy($id)
    {
        $item = Hospital::find($id);
        if ($item !=null) {
            if (sizeof($item->beds) > 0) {
                return Response::json(array('error' => true, 'message' => 'Hospital has beds',
                                            'params' => array('id' => $id)), 200);
            } else {
                $item->delete();
                return Response::json(array('error' => false, 'message' => 'Deleted',
                                            'params' => array('id' => $id), 'hospital' => $item), 200);
            }
        } else {
            return Response::json(array('error' => true, 'message' => 'Hospital not found',
                                        'params' => array('id' => $id)), 200);
        }
    }

    public function getByUser()
    {
        $userid = Input::get('userid',null);

        if ($userid != null) {
            return Response::json(Hospital::ByLocation($userid));
        }
        return Response::json(array('error'=>true,'message'=>'Cannot find user.'), 200);
    }

    public function getByLocation()
    {
        $region = Input::get('region',null);
        $district = Input::get('district',null);
        $userid = Input::get('userid',null);
        return Response::json(Hospital::ByLocation($userid, $region, $district));
    }

    public function periodInfo($p)
    {
        $d = array();
        $data = null;

        if ($p=='day') { $data = LindaLog::dayRateData(); };
        if ($p=='month') { $data = LindaLog::monthRateData(); };
        if ($p=='year') { $data = LindaLog::yearRateData(); };

        foreach($data as $y) {
            array_push($d, array($y->tu, round($y->r*1,0)));
        }
        //$d = array( array(0,7),array(1,6.5),array(2,12.5),array(3,7),array(4,9),array(5,6),array(6,11),array(7,6.5),array(8,8),array(9,7) );
        return Response::json($d);
    }

    public function nurseInfo($nurseId)
    {
        $error = '';
        $user = User::find($nurseId);
        if ($user != null) {
            if ($user->hospital_id != null)
            {
                $hos = Hospital::find($user->hospital_id);
                $hos = $this->addStats($hos);

                return Response::json(array('error' => false, 'message' => 'Updated',
                    'params' => array('nurse_id' => $nurseId),
                    'hospital' => $hos), 200);
            } else {
                $error = 'User is not assigned to a specific hospital';
            }

        } else {
            $error = 'User not found';
        }

        return Response::json(array('error'=>true,'message'=>$error,
            'params'=>array('nurse_id' => $nurseId)), 200);
    }

    public function treeInfo()
    {
        $t = array('name'=>'Hospitals', 'children'=>array(), 'color'=>7);
        $hs = Hospital::with('wards.beds')->get();
        //return Response::json($hs);

        foreach($hs as $h)
        {
                $hcolor = 0;
                $hcount = 0;
                $i = array('name' => $h->name, 'children' => array(), 'color' => $h->colorCode($hcolor));

                foreach ($h->wards as $w)
                {

                        $wcolor = 0;
                        $bcount = 0;
                        $j = array('name' => $w->name, 'children' => array(), 'color' => $h->colorCode($wcolor));

                        foreach ($w->beds as $b) {
                            $bcount++;
                            if ($b->status == 'Available') {
                                $wcolor++;
                                array_push($j['children'], array('name' => $b->name,
                                    'value' => 1,
                                    'color' => '#00ff00'));
                            } else {
                                array_push($j['children'], array('name' => $b->name,
                                    'value' => 1,
                                    'color' => '#ff0000'));
                            }
                        }

                        $hcolor += $wcolor;
                        $hcount += $bcount;

                        $j['color'] = ($bcount > 0) ? $h->colorCode($wcolor / $bcount) : $h->colorCode();

                        array_push($i['children'], $j);

                }

                $i['color'] = ($hcount > 0) ? $h->colorCode($hcolor / $hcount) : $h>colorCode();

                array_push($t['children'], $i);
        }

        return Response::json($t);

    }

    public function mapInfo()
    {
        $h = array();

        foreach(Hospital::all() as $hos)
        {
            $hos = $this->addStats($hos);
            array_push($h, $hos);
        }

        return Response::json($h);
    }

    private function addStats($hos)
    {
        list($beds, $open, $or, $lu, $cc) = $hos->occupancyInfo();
        $hos->current_or = $or;
        $hos->numbeds = $beds;
        $hos->openbeds = $open;
        $hos->lastupdate = $lu;
        $hos->cc = $cc;
        return $hos;
    }
}
