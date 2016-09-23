<?php

class LocationController extends BaseController {

	public function index()
	{
        return Response::json(Location::all());
	}

	public function show($id)
	{
        return Response::json(Location::find($id));
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
                $h = new Location();
                $h->country = $data['country'];
                $h->retion = $data['region'];
                $h->created_at = date('Y-m-d h:m:s');
                $h->modified_by = Input::get('user_id',1);
                $h->save();
            } else {
                $error = true;
                $responseCode = 400;
                $data['messages'] = 'Could not save location information.';
            }
        }

        return Response::json(array('error' => $error, 'data' => $data), $responseCode);
    }

    public function destroy($id)
    {
        $item = Location::find($id);
        if ($item !=null)
        {
            $hosps = $item->hospitals();

            if (sizeof($hosps) > 0)
            {
                return Response::json(array('error' => true, 'message' => 'Location has hospitals',
                    'params' => array('id' => $id)), 200);
            } else {
                //$item->delete();
                return Response::json(array('error' => false, 'message' => 'Deleted',
                    'params' => array('id' => $id), 'location' => $item), 200);
            }
        } else {
            return Response::json(array('error' => true, 'message' => 'Location not found',
                'params' => array('id' => $id)), 200);
        }
    }
}
