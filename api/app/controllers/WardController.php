<?php

class WardController extends BaseController {

	public function index()
	{
        return Response::json(Ward::all());
	}

	public function show($id)
	{
        return Response::json(Ward::find($id));
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
                $h = new Ward();
                $h->name = $data['name'];
                $h->type = $data['type'];
                $h->hospital_id = $data['hospital'];
                $h->modified_by = Input::get('user_id',1); 
                $h->created_at = date('Y-m-d h:m:s');
                $h->save();
            } else {
                $error = true;
                $responseCode = 400;
                $data['messages'] = 'Could not save ward information.'; 
            }
        }

        return Response::json(array('error' => $error, 'data' => $data), $responseCode);
    }

    public function destroy($id)
    {
        $item = Ward::find($id);
        if ($item !=null) {
            if (sizeof($item->beds) > 0) {
                return Response::json(array('error' => true, 'message' => 'Ward has beds',
                                            'params' => array('id' => $id)), 200);
            } else {
                $item->delete();
                return Response::json(array('error' => false, 'message' => 'Deleted',
                                            'params' => array('id' => $id), 'ward' => $item), 200);
            }
        } else {
            return Response::json(array('error' => true, 'message' => 'Ward not found',
                                        'params' => array('id' => $id)), 200);
        }
    }
}
