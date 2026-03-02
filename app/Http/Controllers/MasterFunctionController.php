<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\EmploymentType;
use App\Models\Designation;

use App\Models\Employee;
use App\Models\User;
use App\Models\Leave;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetails;
use App\Models\LeaveAssign;

class MasterFunctionController extends Controller
{
    //
    function GenerateLeavePeriod ($employment_type,$joiningDate = False){
      $employment_types = EmploymentType::select('id','employment_type','leave_period','status','created_at')->where('id',$employment_type)->first();
      $leave_period = $employment_types->leave_period;
      if($leave_period == 3){
         //Joining Date
         $result = $this->calculateJobPeriod($joiningDate);
        return $result;
      }
     else if($leave_period == 2){
       //Financial Year
        $result = $this->getFinancialYearDates();
        return $result;

     }
     else if($leave_period == 1){
      //calendar Year
      $result = $this->getCalendarYearDates();
      return $result;
    }




    }

    function GetLeavePeriod ($user_id,$leave_type){

      $employee = Employee::where('user_id',$user_id)->first();
      $employment_types = EmploymentType::select('id','employment_type','leave_period','status','created_at')->where('id',$employee->employment_type)->first();
      $leave_period = $employment_types->leave_period;
      if($leave_period == 4){
        //special annual Joining Date
        $result = $this->calculateContractPeriodLeave($user_id,$employee->contract_start_date,$employee->contract_end_date,$employee->employment_type,$leave_type);
       return $result;
     }
      if($leave_period == 3){
         //Joining Date
         $result = $this->calculateJobPeriod($employee->doj);
        return $result;
      }
     else if($leave_period == 2){
       //Financial Year
        $result = $this->getFinancialYearDates();
        return $result;

     }
     else if($leave_period == 1){
      //calendar Year
      $result = $this->getCalendarYearDates();
      return $result;
    }




    }

    public function getFinancialYearDates()
    {
        // Set the time zone to match your requirements
        date_default_timezone_set('Asia/Kolkata');

        // Get the current date
        $currentDate = now();

        // Set the start date of the financial year (April 1 of the current year)
        $financialYearStart = now()->month(4)->day(1)->startOfDay();

        // If the current date is before the financial year start date, adjust the start date to the previous year
        if ($currentDate->lt($financialYearStart)) {
            $financialYearStart->subYear();
        }

        // Set the end date of the financial year (March 31 of the next year)
        $financialYearEnd = $financialYearStart->copy()->addYear()->subDay();

        // Format dates in MySQL date format (Y-m-d)
        $startDateFormatted = $financialYearStart->toDateString();
        $endDateFormatted = $financialYearEnd->toDateString();

        // Return the results
        return [
            'start_date' => $startDateFormatted,
            'end_date' => $endDateFormatted,
        ];
    }

    public function getCalendarYearDates()
    {
      // Get the current year
      $currentYear = now()->year;

      // Set the start date to January 1st of the current year
      $startDate = Carbon::createFromDate($currentYear, 1, 1);

      // Set the end date to December 31st of the current year
      $endDate = Carbon::createFromDate($currentYear, 12, 31);

      // Format dates in MySQL date format (Y-m-d)
      $startDateFormatted = $startDate->toDateString();
      $endDateFormatted = $endDate->toDateString();

      // Return the current calendar year start and end dates
      return [
          'start_date' => $startDateFormatted,
          'end_date' => $endDateFormatted,
      ];
  }

  // public function calculateJobPeriod($joiningDate)
  //   {
  //       // Convert the provided joining date to a Carbon instance
  //       $joiningDate = Carbon::parse($joiningDate);

  //       // Calculate the end date of the initial one-year contract
  //       $initialContractEndDate = $joiningDate->copy()->addYear()->subDay();

  //       // If the current date is beyond the end date of the initial contract, renew the job period
  //       if (now()->gt($initialContractEndDate)) {
  //           // Renew the job period starting from the next day
  //           $startDate = $initialContractEndDate->copy()->addDay();
  //           // End the renewed job period one year later
  //           $endDate = $startDate->copy()->addYear()->subDay();
  //       } else {
  //           // If the initial contract is still valid, set the start and end dates accordingly
  //           $startDate = $joiningDate;
  //           $endDate = $initialContractEndDate;
  //       }

  //       return [
  //           'job_period_start_date' => $startDate->toDateString(),
  //           'job_period_end_date' => $endDate->toDateString(),
  //       ];
  //   }
  public function calculateJobPeriod($joiningDate)
    {
        // Convert the provided joining date to a Carbon instance
        $joiningDate = Carbon::parse($joiningDate);

        // Calculate the end date of the initial one-year contract
        $initialContractEndDate = $joiningDate->copy()->addYear()->subDay();

        // Check if the current date is beyond the end date of the initial contract
        if (now()->gt($initialContractEndDate)) {
            // Renew the job period starting from the next day
            $startDate = $initialContractEndDate->copy()->addDay();
            // End the renewed job period one year later
            $endDate = $startDate->copy()->addYear()->subDay();

            // If the renewed job period end date is over, extend the period by one year from the current date
            if (now()->gt($endDate)) {
                $startDate = $endDate->addDay();
                $endDate = $startDate->copy()->addYear()->subDay();
            }
        } else {
            // If the initial contract is still valid, set the start and end dates accordingly
            $startDate = $joiningDate;
            $endDate = $initialContractEndDate;
        }

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }


    public function getEmploymenttypeDesignations($employment_type)
    {

        $designations = Designation::select('designations.id','designation','designations.status','designations.created_at','employment_types.employment_type')
        ->leftjoin("employment_types","employment_types.id","=","designations.employment_type")
        ->where('designations.employment_type',$employment_type)
        ->where('designations.status',1)
        ->orderBy('designations.id','DESC')->get();
        return response()->json(['data'=> $designations]);

        // return view('content.apps.app-access-permission'.compact('permissions'))
        //     ->with('i', ($request->input('page', 1) - 1) * 5);


    }
    function getLeaveBalance ($employment_type,$user_id,$joiningDate = False){

      $employee_details=Employee::where('user_id',$user_id)->first();
    }




    //    $from = date('2023-04-01');
    //    $to = date('2024-03-31');
    //    $subscriptionDate = $employee_details->doj;
    // $dateArray = (explode("-", $subscriptionDate));
    // if (date("Y") ==  $dateArray[0]) {
    //   // Convert the subscription date to a Carbon instance
    //   $subscriptionDateTime = Carbon::parse($subscriptionDate);

    //   // Calculate the expiration date by adding one year to the subscription date
    //   $expirationDateTime = $subscriptionDateTime->addYear();

    //   // Format the expiration date as YYYY-MM-DD
    //   $date_end = $expirationDateTime->format("Y-m-d");
    //   $date_start = $dateArray[2] . '-' . $dateArray[1] . '-' . date("Y");
    // } else {
    //   $doj = $employee_details->doj;
    //   $dateArray = (explode("-", $doj));
    //   $subscriptionDate = date("Y") . '-' . $dateArray[1] . '-' . $dateArray[2];
    //   // Convert the subscription date to a Carbon instance
    //   $subscriptionDateTime = Carbon::parse($subscriptionDate);

    //   // Calculate the expiration date by adding one year to the subscription date
    //   $expirationDateTime = $subscriptionDateTime->addYear();

    //   // Format the expiration date as YYYY-MM-DD
    //   $date_end = $expirationDateTime->format("Y-m-d");
    //   // $date_start = $dateArray[2] . '-' . $dateArray[1] . '-' . date("Y");
    //   $date_start = date("Y") . '-' .$dateArray[1] . '-' . $dateArray[2];
    // }
    public function calculateContractPeriodLeave($user_id,$start_date,$end_date,$employment_type,$leave_type)
    {

      $start_date_array = explode('-', $start_date);
      $start_month = $start_date_array[1];
      $start_day   = $start_date_array[2];
      $start_year  = $start_date_array[0];

      $end_date_array = explode('-', $end_date);
      $end_month = $end_date_array[1];
      $end_day   = $end_date_array[2];
      $end_year  = $end_date_array[0];

      $today = date('Y-m-d');
      $today_array = explode('-', $today);
      $today_month = $today_array[1];
      $today_day   = $today_array[2];
      $today_year  = $today_array[0];

      $eligible_start_month_leave=0;

      if($end_year ==  $start_year){

        $period_start_date= $start_date;
        $period_end_date= $end_date;

        if($start_day <= 15 ){
          $eligible_start_month_leave=1;
        }

      }


      else if($today_year ==   $start_year && $end_year !=   $start_year){
        $period_start_date= $start_date;
        $period_end_date= $today_year.'-12-31';

        if($start_day <= 15 ){
          $eligible_start_month_leave=1;
        }

      }
      else{
        $period_start_date = $today_year.'-01-01';
        $period_end_date = $end_date;

        if( $end_day >=15){
          $eligible_start_month_leave=1;
        }
      }


      $period_start_date_array = explode('-', $period_start_date);
      $period_start_month = $period_start_date_array[1];
      $period_start_day   = $period_start_date_array[2];
      $period_start_year  = $period_start_date_array[0];

      $period_end_date_array = explode('-', $period_end_date);
      $period_end_month = $period_end_date_array[1];
      $period_end_day   = $period_end_date_array[2];
      $period_end_year  = $period_end_date_array[0];




      $leave_detail = LeaveAssign::where('leave_type',$leave_type)->where('employment_type',$employment_type)->first();
      if($leave_type == 1){
        $days= $period_end_month -  $period_start_month;
        if($eligible_start_month_leave ==1){
          $days++;
        }
        $total_leave= $days;
      }
      else if($leave_type==5){
        $total_leave= 0;
      }
      else {
        $total_leave= $leave_detail->total_credit;
      }

      $employee = Employee::where('user_id',$user_id)->first();
      $result1 = $this->calculateJobPeriod($employee->doj);


    //   return [
    //     'cl_start_date' => $period_start_date,
    //     'cl_end_date' => $period_end_date,
    //     'start_date' => $result1['start_date'],
    //     'end_date' => $result1['end_date'],
    //     'total_leave'=>   $total_leave
    // ];
    return [
      'cl_start_date' => $period_start_date,
      'cl_end_date' => $period_end_date,
      'start_date' =>  $employee->contract_start_date,
      'end_date' => $employee->contract_end_date,
      'total_leave'=>   $total_leave
  ];

    }



    public function findWeekendDays($year) {
      $weekends = array();

      // Loop through each month
      for ($month = 1; $month <= 12; $month++) {
          // Get the number of days in the month
          $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

          // Loop through each day of the month
          for ($day = 1; $day <= $num_days; $day++) {
              $date = mktime(0, 0, 0, $month, $day, $year);
              $dayOfWeek = date('N', $date); // Get day of week (1 = Monday, 7 = Sunday)

              // Check if it's Saturday or Sunday
              if ($dayOfWeek == 6 || $dayOfWeek == 7) {
                  $weekends[$month][] = date('Y-m-d', $date); // Add weekend day to array
              }
          }
      }

      return $weekends;
  }





  }