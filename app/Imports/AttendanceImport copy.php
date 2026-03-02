<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;

class AttendanceImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

      $employee = Employee::where('employees.empId',$row[0])->first();
      if($employee){
        $time = strtotime($row[1]);

        $date = date('Y-m-d',$time);
        // $date = date('Y-m-d', strtotime(str_replace('-', '/', $row[1])));
        return new Attendance([
          //
          'user_id'     => $employee->user_id,
          'date'    => $date,
          'in_time'    => (($row[2] == '') || ($row[2] == null) ? null : $row[2]),
          'out_time'    => (($row[3] == '') || ($row[3] == null) ? null : $row[3]),
      ]);
      }

    }
}