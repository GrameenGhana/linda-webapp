<?php

class MobileApiController extends BaseController {

    public function __construct() {
        $this->error = false;
        $this->responseCode = 200;
        $this->ts = $this->microtime_float();
        $this->version = $this->microtime_float();
    }

    
    public function saveData($id, $resource)
    {
        $data = array();

        $user = User::whereRaw('email=?',array($id))->first();

        if (is_null($user)) {
            $this->error = true;
            $this->responseCode = 401;
            $data['message'] = "User $id not found";
        } else {
            switch(strtolower($resource)) {
                case 'log':
                    $data = $this->process_logs($user);
                    break;
                default:
                    $this->error = true;
                    $this->responseCode = 405;
                    $data['message'] = "Requested method '$resource' unknown"; 
            }
        }

    	return $this->respond($data);
    }


    public function getData($id, $resource, $time)
    {
        $data = array();
        $time = (preg_match('/^\d+$/',$time)) ? $time : 1325376000; 
        $this->version = $time;

        $user = User::whereRaw('email=?',array($id))->first();

        if (is_null($user)) {
            $this->error = true;
            $this->responseCode = 401;
            $data['message'] = "User $id not found";
        } else {
            switch(strtolower($resource)) {
                case 'region':
                    $data = $this->regions($user, $time);
                    break;
                case 'hospital':
                    $data = $this->hospitals($user, $time);
                    break;
                case 'ward':
                    $data = $this->wards($user, $time);
                    break;
                case 'bed':
                    $data = $this->beds($user, $time);
                    break;
                case 'report':
                    $data = $this->report($user);
                    break;
                case 'freebeds':
                    $data = $this->freebeds($user);
                    break;
                case 'detail':
                    $data = $this->details($user);
                    break;
                default:
                    $this->error = true;
                    $this->responseCode = 405;
                    $data['message'] = "Requested method '$resource' unknown"; 
            }
        }

    	return $this->respond($data);
    }

    protected function respond($data) {
        $te = $this->microtime_float();
        $tt = (($te-$this->ts)/1000).'s';
    	return Response::json(array('error' => $this->error, 
                                    'time_taken'=>$tt, 
                                    'version'=> $this->version, 
                                    'data' => $data), 
                              $this->responseCode);
    }

    protected function process_logs($user) {
        $rules = array('data' => 'required');

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->messages();
            $this->error = true;
            $this->responseCode = 400; 
            $data['messages'] = $errors->toArray();
        } else {
            $input = Input::all();
            $data = json_decode($input['data']);

            if (sizeof($data->logs)) {
                LindaLog::saveRaw($user->id, $input['data']);

                foreach ($data->logs as $l) {
                    $log = new stdClass();
                    $log->username = $l->user_id;
                    $log->module = $l->module;
                    $log->data = $l->data;
                    $log->start_time = date('Y-m-d h:i:s', $l->start_time / 1000);
                    $log->end_time = date('Y-m-d h:i:s', $l->end_time / 1000);
                    $log->timetaken = (($l->end_time - $l->start_time) / 1000);
                    $log->created_at = date('Y-m-d h:m:s');
                    $log->modified_by = $user->id;

                    if (trim(preg_replace('/\s+/', ' ', $l->module)) == 'Status Update') { 
                        LindaLog::saveBedUsage($user->id, $log);
                    } else {
                        LindaLog::saveAppUsage($user->id, $log);
                    }
                }
                $data = 'success';
            } else {
                $this->error = true;
                $this->responseCode = 400; 
                $data['messages'] = 'Missing log data'; 
            }
        }

        return $data; 
    }

    protected function freebeds($user)
    {
        $data = array();

        $data['regions'] = Location::all();  
        $data['hospitals'] = Hospital::withNoWards();  
        $data['beds'] = array();

        foreach(Bed::where('status','Available')->get() as $r) {
                    array_push($data['beds'], array('id'=>$r->id,
                                            'name'=> $r->name,
                                            'status'=> $r->status,
                                            'type'=> $r->type,
                                            'ward_name'=> $r->ward->name,
                                            'phone'=> $r->hospital->phone,
                                            'latitude'=> $r->hospital->lat,
                                            'longitude'=> $r->hospital->long,
                                            'hospital_name'=> $r->hospital->name,
                                            'hospital_id'=> $r->hospital_id,
                                            'region_id'=> $r->hospital->location_id,
                                            'updated_at'=>$r->updated_at));
                     
        }

        $s = array('name'=>$user->name, 'username'=>$user->email, 'results'=>$data);

        return $s;
    }

    protected function report($user) 
    {
        $data = array();
        $current_year = date('Y');

        for($year=$current_year-1; $year<=$current_year; $year++) {
            foreach($user->hospitals() as $h) { 
                for ($month=0; $month<=11; $month++) {
                    array_push($data, array('hospital_id'=>$h->id,
                                            'region_id'=>$h->location_id,
                                            'year'=>$year,
                                            'month'=>$month+1,
                                            'report'=> Hospital::report($h->id, $month+1, $year)));
                }
            }
        }

        return array('name'=>$user->name, 'username'=>$user->email, 'spline'=>$data);
    }

    protected function regions($user, $time)
    {
        $data = array('new'=> array(), 'updated'=>array(), 'deleted'=>array());
        $seen = array();

        foreach($user->regions() as $r)
        {
                if (!in_array($r->id, $seen)) {
                    $idx = $this->getIndex($r->created_at, $r->updated_at, $r->deleted_at, $time);
                    if($idx!='unchanged') {
                        if ($idx=='deleted') { array_push($data[$idx], $r->id); } else {
                        array_push($data[$idx], array('id'=>$r->id,
                                                      'name'=> $r->region,
                                                      'created_at'=>$r->created_at,
                                                      'updated_at'=>$r->updated_at,
                                                      'deleted_at'=>$r->deleted_at));
                         }
                        array_push($seen, $r->id);
                    }
                }   
        }

        $s = array('name'=>$user->name, 'username'=>$user->email, 'regions'=>$data);

        return $s;
    }

    protected function hospitals($user, $time)
    {
        $data = array('new'=> array(), 'updated'=>array(), 'deleted'=>array());
        $seen = array();

        foreach($user->hospitals() as $r)
        {
                if (!in_array($r->id, $seen)) {
                    $idx = $this->getIndex($r->created_at, $r->updated_at, $r->deleted_at, $time);
                    if($idx!='unchanged') {
                        if ($idx=='deleted') { array_push($data[$idx], $r->id); } else {
                        array_push($data[$idx], array('id'=>$r->id,
                                                      'name'=> $r->name,
                                                      'latitude'=> $r->lat,
                                                      'longitude'=> $r->long,
                                                      'phone'=> $r->phone,
                                                      'type'=> $r->type,
                                                      'region_id'=>$r->location_id,
                                                      'created_at'=>$r->created_at,
                                                      'updated_at'=>$r->updated_at,
                                                      'deleted_at'=>$r->deleted_at));
                         }
                        array_push($seen, $r->id);
                    }
                }
        }

        $s = array('name'=>$user->name,
                   'username'=>$user->email,
                   'hospitals'=>$data);

        return $s;
    }

    protected function wards($user, $time)
    {
        $data = array('new'=> array(), 'updated'=>array(), 'deleted'=>array());
        $seen = array();

        foreach($user->wards() as $r)
        {
                if (!in_array($r->id, $seen)) {
                    $idx = $this->getIndex($r->created_at, $r->updated_at, $r->deleted_at, $time);
                    if($idx!='unchanged') {
                        if ($idx=='deleted') { array_push($data[$idx], $r->id); } else {
                        array_push($data[$idx], array('id'=>$r->id,
                                                      'name'=> $r->name,
                                                      'type'=> $r->type,
                                                      'hospital_id'=> $r->hospital_id,
                                                      'region_id'=>$r->hospital->location_id,
                                                      'created_at'=>$r->created_at,
                                                      'updated_at'=>$r->updated_at,
                                                      'deleted_at'=>$r->deleted_at));
                         }
                        array_push($seen, $r->id);
                    }
                }
        }

        $s = array('name'=>$user->name,
                   'username'=>$user->email,
                   'wards'=>$data);

        return $s;
    }

    protected function beds($user, $time)
    {
        $data = array('new'=> array(), 'updated'=>array(), 'deleted'=>array());
        $seen = array();

        foreach($user->beds() as $r)
        {
                if (!in_array($r->id, $seen)) {
                    $idx = $this->getIndex($r->created_at, $r->updated_at, $r->deleted_at, $time);
                    if($idx!='unchanged') {
                        if ($idx=='deleted') { array_push($data[$idx], $r->id); } else {
                        array_push($data[$idx], array('id'=>$r->id,
                                                      'name'=> $r->name,
                                                      'status'=> $r->status,
                                                      'type'=> $r->type,
                                                      'ward_name'=> $r->ward->name,
                                                      'phone'=> $r->hospital->phone,
                                                      'ward_id'=> $r->ward_id,
                                                      'hospital_id'=> $r->hospital_id,
                                                      'region_id'=>$r->hospital->location_id,
                                                      'created_at'=>$r->created_at,
                                                      'updated_at'=>$r->updated_at,
                                                      'deleted_at'=>$r->deleted_at));
                         }
                        array_push($seen, $r->id);
                    }
                }
        }

        $s = array('name'=>$user->name,
                   'username'=>$user->email,
                   'beds'=>$data);

        return $s;
    }

    protected function details($user)
    {
        $x = date('U',strtotime('1970-01-01'));

        $data = $this->regions($user, $x);
        $regions = $data['regions'];

        $data = $this->hospitals($user, $x);
        $hospitals = $data['hospitals'];

        $data = $this->wards($user, $x);
        $wards = $data['wards'];

        $data = $this->beds($user, $x);
        $beds = $data['beds'];

        $s = array('name'=>$user->name,
                   'username'=>$user->email,
                   'beds'=>$beds,
                   'wards'=>$wards,
                   'hospitals'=>$hospitals,
                   'regions'=>$regions);

        return $s;
    }

    private function getIndex($c_at,$u_at,$d_at,$time)
    {
        $idx = '';
        if (($d_at!=null) && $this->dateAfter($d_at, $time)) { $idx= 'deleted'; } 
        else if ($this->dateAfter($c_at, $time)) { $idx= 'new'; } 
        else if ($this->dateAfter($u_at, $time)) { $idx= 'updated'; } 
        else { $idx = 'unchanged'; }
        return $idx;
    }

    private function dateAfter($date1, $date2)
    {
         return (strtotime($date1) >= strtotime(date('r',$date2)));
    }

    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
    }
}
