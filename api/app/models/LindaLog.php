<?php

class LindaLog extends Eloquent {

    public static function dayRateData() {
        $cols = "HOUR(created_at)+1 as tu, SUM(occupied) as oc, COUNT(*) as t, SUM(occupied) / COUNT(*) * 100 as r";
        $groupby = "tu";

        $data = DB::table('log_orate')
            ->select(DB::raw($cols))
            ->groupBy($groupby)
            ->get();

        return $data;
    }

    public static function monthRateData()
    {
        $cols = "DAYOFMONTH(created_at) as tu, SUM(occupied) as oc, COUNT(*) as t, SUM(occupied) / COUNT(*) * 100 as r";
        $groupby = "tu";

        $data = DB::table('log_orate')
            ->select(DB::raw($cols))
            ->groupBy($groupby)
            ->get();

        return $data;
    }

    public static function yearRateData()
    {
        $cols = "MONTH(created_at) as tu, SUM(occupied) as oc, COUNT(*) as t, SUM(occupied) / COUNT(*) * 100 as r";
        $groupby = "tu";

        $data = DB::table('log_orate')
            ->select(DB::raw($cols))
            ->groupBy($groupby)
            ->get();

        return $data;
    }
    
    public static function bedAdmissions($id, $month, $year) {
        $sql = LindaLog::dateSQL('start', $month, $year, null, 'INMONTH');
        $sql .= ' AND bed_id='.$id; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function wardAdmissions($id, $month, $year) {
        $sql = LindaLog::dateSQL('start', $month, $year, null, 'INMONTH');
        $sql .= ' AND ward_id='.$id; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function hospitalAdmissions($id, $month, $year) {
        $sql = LindaLog::dateSQL('start', $month, $year, null, 'INMONTH');
        $sql .= ' AND hospital_id IN ('.$id.')'; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function bedDischarges($id, $month, $year) {
        $sql = LindaLog::dateSQL('end', $month, $year, null, 'INMONTH');
        $sql .= ' AND bed_id='.$id; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function wardDischarges($id, $month, $year) {
        $sql = LindaLog::dateSQL('end', $month, $year, null, 'INMONTH');
        $sql .= ' AND ward_id='.$id; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function hospitalDischarges($id, $month, $year) {
        $sql = LindaLog::dateSQL('end', $month, $year, null, 'INMONTH');
        $sql .= ' AND hospital_id IN ('.$id.')'; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function bedDeaths($id, $month, $year) {
        $sql = LindaLog::dateSQL('end', $month, $year, null, 'INMONTH');
        $sql .= ' AND alive=0 AND bed_id='.$id; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function wardDeaths($id, $month, $year) {
        $sql = LindaLog::dateSQL('end', $month, $year, null, 'INMONTH');
        $sql .= ' AND alive=0 AND ward_id='.$id; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function hospitalDeaths($id, $month, $year) {
        $sql = LindaLog::dateSQL('end', $month, $year, null, 'INMONTH');
        $sql .= ' AND hospital_id IN ('.$id.')'; 
        return DB::table('log_bed_usage')->whereRaw($sql)->count();
    }

    public static function bedPatientDays($id, $month, $year) {
        $sql = LindaLog::dateSQL('start', $month, $year, null, 'INMONTH');
        $sql .= ' AND bed_id='.$id; 
        return LindaLog::patientDays($sql, 'bed_id');
    }

    public static function wardPatientDays($id, $month, $year) {
        $sql = LindaLog::dateSQL('start', $month, $year, null, 'INMONTH');
        $sql .= ' AND ward_id='.$id; 
        return LindaLog::patientDays($sql, 'ward_id');
    }

    public static function hospitalPatientDays($id, $month, $year) {
        $sql = LindaLog::dateSQL('start', $month, $year, null, 'INMONTH');
        $sql .= ' AND hospital_id IN ('.$id.')'; 
        return LindaLog::patientDays($sql, 'hospital_id');
    }

    public static function saveRaw($user_id, $log) {
        DB::table('log_raw')->insert( 
                [
                    'log' => $log,
                    'user_id' => $user_id, 
                    'modified_by' => $user_id, 
                    'created_at' => date('Y-m-d h:i:s')
                ]);
    }

    public static function getLastBedUsageId($bed_id) {
        $last = DB::table('log_bed_usage')
                    ->select(DB::raw('max(id) as id'))
                    ->whereRaw("bed_id=$bed_id AND duration <=0")
                    ->groupBy('bed_id')
                    ->get();

        return ($last != null) ? $last[0]->id : null;
    } 

    public static function saveAppUsage($user_id, $log) {
        DB::table('log_app_usage')->insert( 
                    [
                        'module' => $log->module, 
                        'action' => $log->data, 
                        'user_id' => $user_id, 
                        'start' => $log->start_time,
                        'end' => $log->end_time,
                        'duration' => $log->timetaken,
                        'created_at'=> $log->created_at,
                        'modified_by' => $user_id 
                    ]);
    }

    public static function saveBedUsage($user_id, $log) {
        $data = json_decode($log->data);

        $id = LindaLog::getLastBedUsageId($data->bed_id);
        $bed = Bed::where('id',$data->bed_id)->first();

        //Log::info("Saving for id $id ".$bed->name." ".$bed->ward->name." ".$bed->hospital->name);

        if ($data->status=='Occupied') {
            if ($id==null) {
                // Add entry to table
                DB::table('log_bed_usage')->insert( 
                    [
                        'bed_id' => $data->bed_id, 
                        'ward_id' => $data->ward_id, 
                        'hospital_id' => $data->hospital_id, 
                        'start' => $log->start_time,
                        'duration' => 0, 
                        'alive' => $data->alive, 
                        'created_at'=> $log->created_at,
                        'modified_by' => $user_id 
                    ]);
               $bed->status = $data->status;
               $bed->save();
            }
        } else {
            // Get last entry and mark it closed
            if($id != null) {
                $r = DB::table('log_bed_usage')->where('id', $id)->first();
                DB::table('log_bed_usage')
                    ->where('id',$id)
                    ->update(
                        [
                            'end' => $log->end_time,
                            'alive' => $data->alive,
                            'duration' => floor((strtotime($log->end_time) - strtotime($r->start))/(60))
                        ]
                      );
               $bed->status = $data->status;
               $bed->save();
            }
        }
    }

    private static function patientDays($sql, $type) {
        $data = DB::table('log_bed_usage')
            ->select(DB::raw("CEILING(SUM(duration) / 1440) as c"))
            ->whereRaw($sql)
            ->groupBy($type)
            ->get();

        return (isset($data[0])) ? $data[0]->c : 0;
    }

    public static function dateSQL($field, $month, $year, $last_day=null, $type="BETWEEN") {
        $sql = "";
        switch(strtolower($type))
        {
            case "less":
                $sql = "DATE($field) <= '$year-$month-$last_day'"; break;
            case "inmonth":
                $sql = "MONTH($field) = $month AND YEAR($field) = $year"; break;
            default:
                $sql = "(DATE($field) BETWEEN '2015-11-01' AND '$year-$month-$last_day')";
        }

        return $sql;
    }
}
