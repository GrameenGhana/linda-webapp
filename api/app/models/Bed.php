<?php
use Illuminate\Database\Eloquent\SoftDeletingTrait;


class Bed extends Eloquent {

    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

    public function hospital()
    {
        return $this->BelongsTo('Hospital');
    }

    public function ward()
    {
        return $this->BelongsTo('Ward');
    }

    public function isAvailable()
    {
        return ($this->status == 'Available'); 
    }

    public function isRegular()
    {
        return ($this->type == 'Regular'); 
    }

    public function scopeAvailable($query)
    {
        return $query->whereRaw('status="Available"');
    }

    public function scopeOccupied($query)
    {
        return $query->whereRaw('status="Occupied"');
    }

    public function scopeRegular($query)
    {
        return $query->whereRaw('type="Regular"');
    }

    public function scopeVerandah($query)
    {
        return $query->whereRaw('type="Verandah"');
    }

    public function toggleStatus($s=null, $e=null, $alive=1)
    {
        $this->status =  ($this->status=='Occupied') ? 'Available' : 'Occupied';
        $this->save();
        $this->updateUsageLog($s, $e, $alive);
    }

    public function updateUsageLog($s=null, $e=null, $alive=1)
    {
        $o = LindaLog::getLastBedUsageId($this->id); 

        if ($this->status=='Occupied') {
            if ($o==null || $o->duration > 0) {
                $ot = new Object;
                $ot->hospital_id = $this->hospital_id;
                $ot->ward_id = $this->ward_id;
                $ot->bed_id = $this->id;
                $ot->start = ($s == null) ? date('Y-m-d H:i:s') : $s;
                $ot->alive = 1;
                $ot->modified_by = 1;
                $ot->created_at = ($s == null) ? date('Y-m-d H:i:s') : $s;
            }
        } else {
            // Get last entry and mark it closed
            if($o != null) {
                $o->end = ($e==null) ? date('Y-m-d H:i:s') : $e;
                $o->alive = $alive;
                $o->duration = floor((strtotime($o->end) - strtotime($o->start))/(60));
            }
        }
    }

    public static function hospitalBedComplement($ids, $month, $year)  {
           $sql = LindaLog::dateSQL('created_at', $month, $year, 31, 'LESS');
           return Bed::whereRaw($sql . " AND hospital_id IN (".$ids.")")->count();
    }

    public static function wardBedComplement($ids, $month, $year)  {
           $sql = LindaLog::dateSQL('created_at', $month, $year, 31, 'LESS');
           return Bed::whereRaw($sql . " AND ward_id IN (".$ids.")")->count();
    }
}

