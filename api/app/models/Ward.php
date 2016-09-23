<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;


class Ward extends Eloquent {

    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

    public function hospital() {
        return $this->belongsTo('Hospital');
    }

    public function beds() {
        return $this->hasMany('Bed');
    }

    public function report($hospital, $month, $year) {
        return Report::wardReport($this->id, $this->name, $hospital, $month, $year);
    }
}
