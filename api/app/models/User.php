<?php
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class User extends Eloquent {

    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

    protected $hidden = array('password','google','facebook');

    public function hospital() {
        return $this->belongsTo('Hospital');
    }

    public function regions() {
        $regions = array();
        if ($this->location_id==null) {
            foreach(Location::withTrashed()->get() as $loc) {
                   array_push($regions, $loc);
            }
        } else {
           array_push($regions, Location::where('id',$this->location_id)->withTrashed()->first());
        }
        return $regions;
    }

    public function hospitals() {
        $data = array();

        if ($this->hospital_id != null) {
           array_push($data, Hospital::where('id',$this->hospital_id)->withTrashed()->first());

        } else if ($this->hospital_id==null && $this->location_id==null) {
            foreach(Hospital::withTrashed()->get() as $v) { array_push($data, $v); }

        } else { 
            $loc = Location::withTrashed()->where('id',$this->location_id)->first();
            foreach($loc->hospitals() as $v) { array_push($data, $v); }
        }
        return $data;
    }

    public function wards() {
        $data = array();

        if ($this->hospital_id != null) {
            foreach(Ward::withTrashed()->where('hospital_id',$this->hospital_id)->get() as $v) { 
                    array_push($data, $v); 
            }

        } else if ($this->hospital_id==null && $this->location_id==null) {
            foreach(Ward::withTrashed()->get() as $v) { array_push($data, $v); }

        } else { 
            $loc = Location::withTrashed()->where('id',$this->location_id)->first();
            foreach($loc->hospitals as $v) { 
                foreach(Ward::withTrashed()->where('hospital_id',$v->id)->get() as $v) { 
                    array_push($data, $v); 
                }
            }
        }
        return $data;
    }

    public function beds() {
        $data = array();

        if ($this->hospital_id != null) {
            foreach(Bed::withTrashed()->where('hospital_id',$this->hospital_id)->get() as $v) { 
                    array_push($data, $v); 
            }

        } else if ($this->hospital_id==null && $this->location_id==null) {
            foreach(Bed::withTrashed()->get() as $v) { array_push($data, $v); }

        } else { 
            $loc = Location::withTrashed()->where('id',$this->location_id)->first();
            foreach($loc->hospitals as $v) { 
                foreach(Bed::withTrashed()->where('hospital_id',$v->id)->get() as $v) { 
                    array_push($data, $v); 
                }
            }
        }
        return $data;
    }


    public static function isUser($email) {
         $user = DB::table('users')->where('email', $email)->first();
         return (is_null($user)) ? false : true;
    }
}

