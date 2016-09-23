<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;


class Hospital extends Eloquent {

    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

    protected $with = array('wards');

    public function location()
    {
        return $this->belongsTo('Location');
    }

    public function wards()
    {
        return $this->hasMany('Ward');
    }

    public function beds()
    {
        return $this->hasMany('Bed');
    }

    public static function withNoWards($id=null)
    {
        return DB::table('hospitals')->get();
    }

    public static function withBeds($id=null)
    {
        $hospitals = ($id==null) ? Hospital::all() : array(Hospital::find($id));

        foreach($hospitals as $h)
        {
            foreach($h->wards as $key => $w)
            {
                $h->wards[$key]->beds = $w->beds;
            }
        }
        return $hospitals;
    }

    public static function ByLocation($userid=null, $region=null, $district=null)
    {
        $resp = array();

        $locations = Location::ByUser($userid);

        foreach($locations as $loc)
        {
            $i = Hospital::indexOf($resp, 'region', $loc->region);

            if ($i > -1) {
                $j = Hospital::indexOf($resp[$i]['districts'],'district',$loc->district);
                if ($j == -1) {
                    array_push($resp[$i]['districts'],
                               array('district'=>$loc->district, 'location_id' => $loc->id,
                                     'updated_at' => $loc->updated_at,
                                     'hospitals' => $loc->hospitals)
                    );
                }
            } else { // Save region
                array_push($resp, array('region'=>$loc->region,
                                        'location_id'=>$loc->id,
                                        'districts'=>array(
                                            array(
                                                'district' => $loc->district,
                                                'location_id' => $loc->id,
                                                'updated_at' => $loc->updated_at,
                                                'hospitals' => $loc->hospitals
                                            )
                                        )
                ));
            }
        }

        return $resp;
    }

    public function occupancyInfo()    {
        if (sizeof($this->beds) <=0)
            return array(0,0,100,date('Y-m-d h:m:s'));

        $numbeds = sizeof($this->beds);
        $numavailable = 0;
        $lastupdate = null;
        foreach($this->beds as $bed) {
            if ($bed->status=='Available') {
                $numavailable++;
            }

            $lastupdate = ($lastupdate == null || (strtotime($lastupdate) < strtotime($bed->updated_at)))
                ? $bed->updated_at : $lastupdate;
        }

        $or = 100 - (($numavailable / $numbeds) * 100);

        $cc = $this->colorCode(($numavailable / $numbeds));

        return array($numbeds, $numavailable, round($or,2), $lastupdate, $cc);
    }

    public static function report($hospitals, $month, $year) {
        return Report::hospitalReport($hospitals, $month, $year);
    }

    public function colorCode($score=1)  {
        $colors = array('#FF0000', '#FF1000', '#FF2000', '#FF3000', '#FF4000', '#FF5000', '#FF6000', '#FF7000',
            '#FF8000', '#FF9000', '#FFA000', '#FFB000', '#FFC000', '#FFD000', '#FFE000', '#FFF000',
            '#FFFF00', '#F0FF00', '#E0FF00', '#D0FF00', '#C0FF00', '#B0FF00', '#A0FF00', '#90FF00',
            '#80FF00', '#70FF00', '#60FF00', '#50FF00', '#40FF00', '#30FF00', '#20FF00', '#10FF00');

        if ($score==0)
        {
            $x = 0;
        } else {
            $x = round(($score*100/100) * 31);
        }

        return $colors[$x];
    }

    private static function indexOf($obj, $key, $val)    {
        foreach($obj as $i => $o)
        {
            if (! array_key_exists($key, $o)) continue;

            if (is_array($o[$key])) {
                return Location::in_array_by_value($o[$key], $key, $val);
            } else if ($o[$key] == $val) {
                return $i;
            }
        }

        return -1;
    }
}
