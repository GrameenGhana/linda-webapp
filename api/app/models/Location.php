<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;


class Location extends Eloquent { 

    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

    protected $with = array('hospitals');

    public function hospitals()
    {
        return $this->hasMany('Hospital');
    }

    public static function wards($region, $hospital)
    {
        $hospitals = ($hospital==0) ? (($region==0) ? Hospital::all() 
                                    : Hospital::whereRaw('location_id='.$region)->get())
                                    : Hospital::find($hospital);

        return $hospitals;
    }

    public static function ByUser($id)
    {
        $user = User::find($id);

        if ($user != null) {
            if ($user->hospital_id==null) {
                return ($user->location_id == null || $user->location_id==0)
                    ? Location::all()
                    : Location::where('id', '=', $user->location_id)->get();
            } else {
                $hosp = Hospital::find($user->hospital_id);
                return array($hosp->location);
            }
        }

        return Location::all();
    }


}

