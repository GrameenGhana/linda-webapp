<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

    protected function createToken($user)
    {
        $payload = array(
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + (2 * 7 * 24 * 60 * 60) // 14 days
        );

        return JWT::encode($payload, Config::get('secrets.TOKEN_SECRET'));
    }

    protected function payload($user)
    {
        $user = User::find($user->id);

        unset($user->password);
        unset($user->google);
        unset($user->facebook);
        unset($user->updated_at);
        unset($user->created_at);
        unset($user->modified_by);

        return Response::json(array('token' => $this->createToken($user), 'user'  => $user));
    }
}
