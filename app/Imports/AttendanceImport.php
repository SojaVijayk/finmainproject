<?php

namespace App\Imports;

use App\Models\AttendanceLog;
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

      $employee = Employee::where('employees.punchId',$row[2])->first();
      if($employee && $row[3] != '1900-01-01 00:00:00'){


        $time = strtotime($row[3]);

        $date = date('Y-m-d',$time);
        if($date >= '2024-01-01'){
          AttendanceLog::where('user_id',$employee->user_id)->where('date',$date)->delete();
          // $date = explode('/', $row[1]);
          // $new_date = $date[2].'-'.$date[1].'-'.$date[0];
          // $date = date('Y-m-d', strtotime(str_replace('-', '/', $row[3])));
          return new AttendanceLog([
            //
            'user_id'     => $employee->user_id,
            'AttendanceLogId'    => $row[0],
            'AttendanceDate'    => $row[1],
            'date' => $date,
            'EmployeeId'    => $row[2],
            'InTime'    => $row[3],
            'InDeviceId'    => $row[4],
            'OutTime'    => $row[5],
            'OutDeviceId'    => $row[6],
            'Duration'    => $row[7],
            'LateBy'    => $row[8],
            'EarlyBy'    => $row[9],
            'IsOnLeave'    => $row[10],
            'LeaveType'    => $row[11],
            'LeaveDuration'    => $row[12],
            'WeeklyOff'    => $row[13],
            'Holiday'    => $row[14],
            'LeaveRemarks'    => $row[15],
            'PunchRecords'    => $row[16],
            'ShiftId'    => $row[17],
            'Present'    => $row[18],
            'Absent'    => $row[19],
            'Status'    => $row[20],
            'StatusCode'    => $row[21],
            'P1Status'    => $row[22],
            'P2Status'    => $row[23],
            'P3Status'    => $row[24],
            'IsonSpecialOff'    => $row[25],
            'SpecialOffType'    => $row[26],
            'SpecialOffRemark'    => $row[27],
            'SpecialOffDuration'    => $row[28],
            'OverTime'    => $row[29],
            'OverTimeE'    => $row[30],
            'MissedOutPunch'    => $row[31],
            'MissedInPunch'    => $row[32],
            'C1'    => $row[33],
            'C2'    => $row[34],
            'C3'    => $row[35],
            'C4'    => $row[36],
            'C5'    => $row[37],
            'C6'    => $row[38],
            'C7'    => $row[39],
            'Remarks'    => $row[40],
            'LeaveTypeId'    => $row[41],
            'LossOfHours'    => $row[42],
            // 'in_time'    => (($row[2] == '') || ($row[2] == null) ? null : $row[2]),
            // 'out_time'    => (($row[3] == '') || ($row[3] == null) ? null : $row[3]),
        ]);
        }

      }

    }
}