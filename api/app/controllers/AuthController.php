<?php

use GuzzleHttp\Subscriber\Oauth\Oauth1;

class AuthController extends \BaseController {


    public function unlink($provider)
    {
        $token = explode(' ', Request::header('Authorization'))[1];
        $payloadObject = JWT::decode($token, Config::get('secrets.TOKEN_SECRET'));
        $payload = json_decode(json_encode($payloadObject), true);
        
        $user = User::find($payload['sub']);

        if (!$user)
        {
            Response::json(array('message' => 'User not found'));
        }

        $user->$provider = '';
        $user->save();
        return $this->payload($user);
    }

    public function login()
    {
        $email = Input::get('email');
        $password = Input::get('password');

        //Log::info("Email: ".$email);
        //Log::info("Password: ".$password);

        $user = User::where('email', '=', $email)->first();

        if (!$user)
        {
            return Response::json(array('message' => 'Wrong email and/or password'), 401);
        }

        if ($user->active==0)
        {
            return Response::json(array('message' => 'User disabled'), 401);
        }


        if (Hash::check($password, $user->password))
        {
            return $this->payload($user);
        }
        else
        {
            return Response::json(array('message' => 'Wrong email and/or password'), 401);
        }
    }

    public function createuser($profile=null, $provider='')
    {
            $email = ($provider=='') ? Input::get('email') : $profile['email'];
            $user = User::where('email', '=', $email)->first();

            if ($user==null)
            {
                $user = new User;
                $user->type = Input::get('usertype','seeker');
            }

            if ($provider=='facebook') 
            {
                $user->facebook = $profile['id'];
                $user->email = $profile['email'];
                $user->name = $profile['name'];
            } elseif ($provider=='google') {
                $user->email = $profile['email'];
                $user->google = $profile['sub'];
                $user->name = $profile['name'];
            } else {
                $user->name = Input::get('name');
                $user->email = Input::get('email');
                $user->password = Hash::make(md5(Input::get('password')));
            }    
            $user->save();

            return $user;
    }

    public function signup()
    {
        $input['name'] = Input::get('name');
        $input['email'] = Input::get('email');
        $input['password'] = Input::get('password');

        $rules = array('name' => 'required',
                       'email' => 'required|email|unique:users,email',
                       'password' => 'required');

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return Response::json(array('message' => $validator->messages()), 400);
        }
        else
        {
            return $this->payload($this->createuser());
        }
    }

    public function facebook()
    {
        $accessTokenUrl = 'https://graph.facebook.com/v2.3/oauth/access_token';
        $graphApiUrl = 'https://graph.facebook.com/v2.3/me';

        $params = array(
            'code' => Input::get('code'),
            'client_id' => Input::get('clientId'),
            'redirect_uri' => Input::get('redirectUri'),
            'client_secret' => Config::get('secrets.FACEBOOK_SECRET')
        );

        $client = new GuzzleHttp\Client();

        // Step 1. Exchange authorization code for access token.
        $accessTokenResponse = $client->get($accessTokenUrl, ['query' => $params]);

        $accessToken = array();
        parse_str($accessTokenResponse->getBody(), $accessToken);

        // Step 2. Retrieve profile information about the current user.
        $graphiApiResponse = $client->get($graphApiUrl, ['query' => $accessToken]);
        $profile = $graphiApiResponse->json();

        // Step 3a. If user is already signed in then link accounts.
        if (Request::header('Authorization'))
        {
            $user = User::where('facebook', '=', $profile['id']);

            if ($user->first())
            {
                return Response::json(array('message' => 'There is already a Facebook account that belongs to you'), 409);
            }

            $token = explode(' ', Request::header('Authorization'))[1];
            $payloadObject = JWT::decode($token, Config::get('secrets.TOKEN_SECRET'));
            $payload = json_decode(json_encode($payloadObject), true);

            $user = User::find($payload['sub']);
            $user->facebook = $profile['id'];
            $user->name = $user->name || $profile['name'];
            $user->save();

            return $this->payload($user);
        }
        // Step 3b. Create a new user account or return an existing one.
        else
        {
            $user = User::where('facebook', '=', $profile['id']);

            if ($user->first())
            {
                return $this->payload($user->first());
            }

            return $this->payload($this->createuser($profile, 'facebook'));
        }
    }

    public function google()
    {
        $accessTokenUrl = 'https://accounts.google.com/o/oauth2/token';
        $peopleApiUrl = 'https://www.googleapis.com/plus/v1/people/me/openIdConnect';

        $params = array(
            'code' => Input::get('code'),
            'client_id' => Input::get('clientId'),
            'redirect_uri' => Input::get('redirectUri'),
            'grant_type' => 'authorization_code',
            'client_secret' => Config::get('secrets.GOOGLE_SECRET')
        );

        $client = new GuzzleHttp\Client();

        // Step 1. Exchange authorization code for access token.
        $accessTokenResponse = $client->post($accessTokenUrl, ['body' => $params]);
        $accessToken = $accessTokenResponse->json()['access_token'];

        $headers = array('Authorization' => 'Bearer ' . $accessToken);

        // Step 2. Retrieve profile information about the current user.
        $profileResponse = $client->get($peopleApiUrl, ['headers' => $headers]);

        $profile = $profileResponse->json();

        // Step 3a. If user is already signed in then link accounts.
        if (Request::header('Authorization'))
        {
            $user = User::where('google', '=', $profile['sub']);
            if ($user->first())
            {
                return Response::json(array('message' => 'There is already a Google account that belongs to you'), 409);
            }

            $token = explode(' ', Request::header('Authorization'))[1];
            $payloadObject = JWT::decode($token, Config::get('secrets.TOKEN_SECRET'));
            $payload = json_decode(json_encode($payloadObject), true);

            $user = User::find($payload['sub']);
            $user->google = $profile['sub'];
            $user->name = $user->name || $profile['name'];
            $user->save();

            return $this->payload($user);
        }
        // Step 3b. Create a new user account or return an existing one.
        else
        {
            $user = User::where('google', '=', $profile['sub']);

            if ($user->first())
            {
                return $this->payload($user->first());
            }

            return $this->payload($this->createuser($profile, 'google'));
        }
    }
}
