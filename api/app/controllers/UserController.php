<?php

class UserController extends \BaseController {

    public function index()
    {
        return Response::json(User::with('hospital')->get());
    }

    public function show($id)
    {
        return Response::json(User::find($id));
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
                $h = new User();
                $h->name = $data['name'];
                $h->title = $data['title'];
                $h->type = $data['type'];
                $h->email = $data['email'];
                $h->password = Hash::make(md5($data['password']));
                $h->location_id = ($data['location_id']==0) ? null: $data['location_id'];
                $h->hospital_id = ($data['hospital_id']==0) ? null: $data['hospital_id'];
                $h->active = 1;
                $h->created_at = date('Y-m-d h:m:s');
                $h->modified_by = Input::get('user_id',1);
                $h->save();
            } else {
                $error = true;
                $responseCode = 400;
                $data['messages'] = 'Could not save user information.';
            }
        }

        return Response::json(array('error' => $error, 'data' => $data), $responseCode);
    }

	public function getUser()
	{
        $h = getallheaders();
        $token = explode(' ', $h['Authorization'])[1];
        $payloadObject = JWT::decode($token, Config::get('secrets.TOKEN_SECRET'));
        $payload = json_decode(json_encode($payloadObject), true);

        $user = User::find($payload['sub']);

        return $this->payload($user);
	}

	public function updateUser()
	{
        $h = getallheaders();
        $token = explode(' ', $h['Authorization'])[1];
        $payloadObject = JWT::decode($token, Config::get('secrets.TOKEN_SECRET'));
        $payload = json_decode(json_encode($payloadObject), true);

        $user = User::find($payload['sub']);
        $user->name = Input::get('name', $user->name);
        $user->email = Input::get('email', $user->email);
        $user->save();

        return $this->payload($user);
	}

    public function updateUserSimple()
    {
        $user = User::where('email', Input::get('email'))->first();

        if ($user)
        {
            $user->password = Hash::make(Input::get('password'));
            $user->save();
            return $this->payload($user);
            
        } else {
            return Response::json(array('error'=>'Could not find the user to update.'));
        }
    }

    public function verify()
    {
        $params = Input::all();
        $result = array('allow'=>false, 'reason'=>'');

        if ($params['action']=='add') {
            $exists = User::isUser($params['email']);
            $result['allow'] = ! $exists;
            $result['reason'] = ($exists) ? 'This user already exists.' 
                                          : 'We have space for this user';
        } else {
            $pass = false; 
            $user = User::where('email', '=', $params['email'])->first();

            if (!$user) {
            } else {
                $pass = (Hash::check($params['password'], $user->password)) ? true : $pass;
            }

            if ($pass) {
                $result['allow'] = true;
                $result['reason'] = 'User exists and can login';
            } else {
                $result['allow'] = false;
                $result['reason'] = 'Cannot login. Invalid email or password.';
            }
        }

        return Response::json($result);
    }

    public function destroy($id)
    {
        $item = User::find($id);

        if ($item !=null)
        {
            $item->delete();
            return Response::json(array('error' => false, 'message' => 'Deleted',
                'params' => array('id' => $id), 'user' => $item), 200);

        } else {
            return Response::json(array('error' => true, 'message' => 'User not found',
                'params' => array('id' => $id)), 200);
        }
    }
}
