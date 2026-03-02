<div style="page-break-after: never;">
  <div align="center" style="width:100%;">
      <table width="100%" align="center">
          <tr>

              {{--  <td  width="70px" align="center" style="vertical-align: middle;"><img width="60px" height="60px" src="./assets/img/branding/cmdlogo.png"></td>
              <td style="vertical-align: top;">
                --}}
                <td  width="70px" align="center" >
                  <img width="60px" height="60px" src="{{ asset('assets/img//branding/cmlogo.png') }}">




                <div >
                          <h4 class="text-center head">Centre for Management Development</h4>
                          <h5 class="text-center head">Thycaud, Thiruvananthapuram, Kerala 695014</h5>
                          <h4 class="text-center head">Attendance Register</h4>

                  </div>

              </td>

          </tr>
      </table>

  </div>
<hr>

@foreach ($employeedetails as $emp)
<div align="center" style="width:100%;">
<table width="100%" align="center">
    <tr>
      <td align="center">{{$emp->name}} - {{$emp->designation}}  <h4 class="text-center head">Attendance Register {{ date("d-m-Y", strtotime($from))}} - {{ date("d-m-Y", strtotime($to))}}</h4>

       </td>
    </tr>
</table>
</div>
<hr>
<div>


      <table border="1" width="100%">
          <thead>
            <th>#</th>
            <th>Date</th>
            <th>In</th>
            <th>Out</th>
            <th>Remark</th>
          </thead>
          <tbody>
            @foreach ($dateRange as $dateArray)

            @php
                $DATE =date("Y-m-d", strtotime($dateArray));
            @endphp

            <tr style="border-bottom:1px solid black;">
              <td>{{ $loop->iteration}}</td>

              <td>{{  date("d-m-Y", strtotime($dateArray)) }}  </td>
              {{--  <td>{{ $dateArray }}  </td>  --}}
              @php
                  $punch_flag=0;
              @endphp
              @foreach ($attendance as $item)

                @if(($item->date == $DATE) && ($item->user_id == $emp->user_id))
                @php
                  $punch_flag++;
              @endphp
                <td>{{ $item->InTime }}</td>
                <td>@if($item->InTime != $item->OutTime){{ $item->OutTime }} @else No Records @endif</td>
                @endif

                @endforeach
              @if($punch_flag== 0) <td>No records</td><td>No records</td>@endif

                <td>

                  @foreach ($holidays as $holiday)
                    @if(($holiday->date == $DATE) )
                    (<span style="color: red;">{{ $holiday->description }}</span>)<br>
                    @endif
                  @endforeach


                  @foreach ($leaves as $leave)

                    @if(($leave->leave_date == $DATE) && ($leave->leave_user_id == $emp->user_id))
                    Leave Details <br>
                   (
                    Leave Type -{{ $leave->leave_type }} <br>
                    Duration - {{ ($leave->leave_day_type == 1 ? "Full Day" : ($leave->leave_day_type == 2 ? "FN" : "AN")) }} -

                    status - {{ ($leave->leave_status == 0 ? 'Pending' : ($leave->leave_status == 1 ? 'Approved' : 'Rejected'))}} -
                    {{ $leave->leave_action_by_name }}
                   )

                    @endif
                    @endforeach


                    @foreach ($movements as $movement)
                    @php

                    $start = date('Y-m-d', strtotime($movement->start_date));
                    $end = date('Y-m-d', strtotime($movement->end_date));


                    @endphp
                    @if((($DATE >= $start) && ($DATE <= $end)) && ($movement->mov_user_id == $emp->user_id))
                    {{--  @if((($movement->start_date == $DATE) || ($movement->end_date == $DATE)) && ($movement->mov_user_id == $emp->user_id))  --}}
                    Movement Details <br>
                   (
                    Start {{ $movement->start_date }} - {{ $movement->start_time }} End {{ $movement->end_date }} - {{ $movement->end_time }} <br>
                    {{ $movement->title }} <br>
                    Type - {{ $movement->type }} <br>
                    Location - {{ $movement->location }} <br>
                    Desc - {{ $movement->description }} <br>
                    Status - {{ ($movement->movement_status == 0 ? 'Pending' : ($movement->movement_status == 1 ? 'Approved' : 'Rejected'))}} -
                    {{ $movement->movement_action_by_name }} - {{ $movement->remark }}
                   )

                    @endif
                    @endforeach


                    @foreach ($missedpunches as $punch)

                    @if(($punch->miss_date == $DATE ) && ($punch->miss_user_id == $emp->user_id))
                    Miss Punch Details <br>
                   (
                    Type - {{ ($punch->miss_type == 1 ? 'Checkin' : ($punch->miss_type == 2 ? 'Checkout' : 'Checkin & Checkout'))}} <br>
                    checkinTime - {{ $punch->checkinTime }} - checkoutTime {{ $punch->checkoutTime }} <br>
                    Desc - {{ $punch->description }} <br>
                    Status - {{ ($punch->miss_status == 0 ? 'Pending' : ($punch->miss_status == 1 ? 'Approved' : 'Rejected'))}} -
                    {{ $punch->misspunch_action_by_name }}
                   )

                    @endif
                    @endforeach

                </td>


            </tr>


        @endforeach

          </tbody>

      </table>
  </div>
  <div class="page-break"></div>
  @endforeach




</div>
