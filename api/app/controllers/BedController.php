<?php

class BedController extends BaseController {

	public function index()
    {
        return Response::json($this->getBeds());
    }

	public function show($id)
	{
        return Response::json($this->getBeds($id));
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
                $b = new Bed();
                $b->name = $data['name'];
                $b->status = 'Available'; 
                $b->type = $data['type'];
                $b->ward_id = $data['ward'];
                $b->hospital_id = $data['hospital'];
                $b->created_at = date('Y-m-d h:m:s');
                $b->modified_by = Input::get('user_id',1);
                $b->save();
            } else {
                $error = true;
                $responseCode = 400;
                $data['messages'] = 'Could not save bed information.';
            }
        }

        return Response::json(array('error' => $error, 'data' => $data), $responseCode);
    }

    public function update()
    {
        $b = Input::get('bed',null);

        if ($b != null)
        {
            $bed = $this->getBeds($b['id']);
            $bed->status = $b['status'];
            $bed->save();
            return Response::json(array('error'=>false, 'message'=>'Updated', 
                                        'params'=>Input::all(), 'bed'=>$bed),200);
        }

        return Response::json(array('error'=>true,'message'=>'Bed not found', 'params'=>Input::all()), 200);
    }

    public function destroy($id)
    {
        $item = $this->getBed($id);
        if ($item !=null)
        {
            $item->delete();
            return Response::json(array('error'=>false, 'message'=>'Deleted', 'params'=>array('id'=>$id), 
                                        'bed'=>$item), 200);
        } else {
            return Response::json(array('error' => true, 'message' => 'Bed not found', 
                                        'params' => array('id' => $id)), 200);
        }
    }

    public function getBedsByLocation($hid, $wid)
    {
        $locs = array();

        if ($wid!=0 && $hid!=0) {
            $locs = Bed::with('ward.hospital')
                ->whereRaw('hospital_id = ? and ward_id = ?',array($hid,$wid))->get();
        } else if ($wid==0 && $hid !=0)   {
            $locs = Bed::with('ward.hospital')->where('hospital_id','=', $hid)->get();
        } else if ($hid==0 && $wid !=0) {
            $locs = Bed::with('ward.hospital')
                   ->whereRaw('ward_id = ?',array($wid))->get();
        } else {
            $locs = Bed::with('ward.hospital')->get();
        }

        return Response::json($locs);
    }

    public function updateStatus()
    {
        $ids = Input::get('ids',null);
        $status = Input::get('status',null);
        //$ids = array(0,1,2,4);
        //$status='Occupied';
        if(in_array($status, array('Available','Occupied')) && $ids == null && sizeof($ids) > 0) {
           Bed::whereIn("id", $ids)->update(array('status' => $status));
           return Response::json(array('error'=>false, 'message'=>'Updated', 'params'=>Input::all(),
                                       'beds'=>$this->getBeds($ids)),200);
        }

        return Response::json(array('error'=>true,'message'=>'Check not update bed. Check parameters.',
                                    'params'=>Input::all()), 200);
    }

    public function toggleStatus()
    {
        $id = Input::get('id',null);
        $bed = null;

        if ($id != null)
        {
            $bed = Bed::find($id);
            $bed->status = ($bed->status=='Available') ? 'Occupied' : 'Available';
            $bed->save();
            $bed->updateUsageLog();
            return Response::json(array('error'=>false, 'message'=>'Updated', 'params'=>Input::all(), 'bed'=>$bed),200);
        }

        return Response::json(array('error'=>true,'message'=>'Bed not found', 'params'=>Input::all()), 200);
    }

    private function getBeds($ids=null)
    {
        if (is_array($ids)) {
            return Bed::with('ward.hospital')->whereIn("id", $ids)->get();
        } else if ($ids == null) {
            return Bed::with('ward.hospital')->get() ;
        } else {
            $bed = Bed::with('ward.hospital')->where('id','=',$ids)->get();
            return ($bed==null) ? $bed : $bed[0];
        }
    }
}
