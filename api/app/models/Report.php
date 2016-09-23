<?php

class Report { 

    public static function hospitalReport($hospitals, $month, $year) {
        $i = array(
            'num_days' => cal_days_in_month(CAL_GREGORIAN, $month, $year),
            'bed_compliment' => Bed::hospitalBedComplement($hospitals, $month, $year),
            'discharges' => LindaLog::hospitalDischarges($hospitals, $month, $year),
            'deaths' => LindaLog::hospitalDeaths($hospitals, $month, $year),
            'patient_days' => LindaLog::hospitalPatientDays($hospitals, $month, $year),
        );

        return Report::finishCalc($i);
    }

    public static function wardReport($id,$ward, $hospital, $month, $year) {
        $i = array(
            'ward' => $ward,
            'hospital' => $hospital,
            'num_days' => cal_days_in_month(CAL_GREGORIAN, $month, $year),
            'bed_compliment' => Bed::wardBedComplement($id, $month, $year),
            'admissions' => LindaLog::wardAdmissions($id,$month, $year),
            'discharges' => LindaLog::wardDischarges($id,$month, $year),
            'deaths' => LindaLog::wardDeaths($id,$month, $year),
            'patient_days' => LindaLog::wardPatientDays($id,$month, $year),
        );

        return Report::finishCalc($i);
    }

    private static function finishCalc($i) {
        $i['avail_bed_days'] = @($i['num_days'] * $i['bed_compliment']);
        $i['alos'] = @round($i['patient_days'] / ($i['discharges']+$i['deaths']),2);
        $i['toi'] = @round(($i['avail_bed_days']-$i['patient_days']) / ($i['discharges']+$i['deaths']),2);
        $i['turnover_per_bed'] = @round(($i['discharges']+$i['deaths']) / $i['bed_compliment'],2);
        $i['avg_death_rate'] = @round($i['deaths'] / ($i['discharges']+$i['deaths'])*100,2);
        $i['avg_daily_oc'] = @round($i['patient_days'] / $i['num_days'],2);
        $i['avg_bed_oc'] = @round($i['patient_days'] / $i['avail_bed_days']*100,2);
        return $i;
    }
}
