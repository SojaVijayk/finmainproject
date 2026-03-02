

    <div class="row ">



      <div class="col-md mb-4 mb-md-2">
        {{--  <small class="text-light fw-semibold">Accordion With Icon (Always Open)</small>  --}}
        <div class="accordion mt-3" id="accordionWithIcon">
          <div class="card accordion-item">
            <h2 class="accordion-header d-flex align-items-center">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-1" aria-expanded="true">
                <i class="ti ti-calendar-event ti-xs me-2"></i>
               Attendance Detailed Vew sss
              </button>
            </h2>

            <div id="accordionWithIcon-1" class="accordion-collapse collapse ">
              <div class="accordion-body">


                <div class="alert alert-warning alert-dismissible d-flex align-items-baseline" role="alert">
                  <span class="alert-icon alert-icon-lg text-primary me-2">
                      <i class="ti ti-calendar ti-sm"></i>
                  </span>
                  <div class="d-flex flex-column ps-1">
                      <p class="mb-0 text-dark"> Attendance Statistics for the period of {{ $from }} TO
                          {{ $to }}</p>
                      <h5 class="alert-heading mb-2"></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                      </button>
                  </div>
              </div>


              <div class="col-lg-12 col-12 mb-4">

                <div class="card mb-3">
                    {{--  <h5 class="card-header">Responsive Table</h5>  --}}
                    <div class="table-responsive  table-hover">
                        <table class="table">
                            <thead>
                                <tr class="text-nowrap">
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>

                                    <th>Duration</th>

                                    <th>Late By</th>

                                    <th>Early By</th>

                                    <th>OT</th>
                                    <th>Remark</th>

                                </tr>
                            </thead>
                            <tbody>

                                @php

                                    $array_excemption_dates=[];


                                    $array_excemption_dates=[];
                                    $numberof_days= count($date_range_array);
                                    $number_of_holdays = count($holidays);
                                    $number_of_leaves = count($leaves);

                                    $total_working_days = $numberof_days-$number_of_holdays;
                                    $total_working_days_by_employee = $numberof_days-($number_of_holdays+$number_of_leaves);



                                @endphp
                                @foreach ($date_range_array as $item)
                                    @php
                                        $date_data = explode('-', $item['date']);
                                        $flag = 0;
                                        $late_flag = 0;

                                    @endphp


                                    <tr class="text-nowrap">
                                        <td> <span
                                                class="badge badge-center rounded-pill bg-dark bg-glow">{{ $date_data[2] }}</span>
                                            <br>
                                            <span class="font-weight-bold text-bold">
                                                <strong>{{ $item['weekday'] }}</strong></span>
                                        </td>


                                        @foreach ($attendance_data as $data)

                                            @if ($item['date'] == $data['date'])
                                                @php
                                                    $flag++;
                                                    $overTime=0;
                                                    $minutes=0;
                                                    $startTime = new DateTime($data['InTime']);
                                                    $endTime = new DateTime($data['OutTime']);

                                                    // Calculate the difference
                                                    $interval = $startTime->diff($endTime);

                                                    // Convert the difference to minutes
                                                    $minutes = ($interval->h * 60) + $interval->i;
                                                    $minutes += ($interval->d * 24 * 60);

                                                    if($minutes >= 480){
                                                        $overTime = ( $minutes - 480);
                                                    }
                                                @endphp
                                                <td>
                                                    <span class="badge bg-label-success bg-glow text-sm">
                                                        {{ $data['StatusCode'] }}
                                                    </span>
                                                </td>
                                                <td>

                                                    {{ $data['InTime'] }}
                                                </td>
                                                <td>
                                                    {{ $data['OutTime'] }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-primary">{{ $minutes }} </span>

                                                </td>
                                                <td>
                                                    <span class="badge bg-label-danger"> {{ $data['LateBy'] }}</span>

                                                </td>
                                                <td>
                                                    <span class="badge bg-label-danger"> {{ $data['EarlyBy'] }}</span>

                                                </td>
                                                <td>
                                                    <span class="badge bg-label-secondary">{{ $overTime }} </span>

                                                </td>
                                            @endif

                                        @endforeach
                                        @if ($flag == 0)
                                            <td colspan="7" class="text-nowrap text-center">
                                               <span class="text-center badge bg-label-secondary"> No records </span>
                                            </td>
                                        @endif
                                        <td>
                                          @foreach ($holidays as $holiday)
                                              @if ($holiday->date == $item['date'])
                                                  @php
                                                      $late_flag++;
                                                      array_push($array_excemption_dates, $item['date']);
                                                  @endphp
                                                  <span
                                                      class="badge bg-danger p-2 mb-1">{{ $holiday->description }}</span>
                                                  <br>
                                              @endif
                                          @endforeach

                                          @foreach ($leaves as $leave)
                                              @if ($leave->leave_date == $item['date'] && $leave->leave_user_id == $data['user_id'])
                                                  @php
                                                      $late_flag++;
                                                      array_push($array_excemption_dates, $item['date']);
                                                  @endphp
                                                  <span class="badge bg-label-warning  p-2 mb-1">
                                                      {{ $leave->leave_type }} -
                                                      {{ $leave->leave_day_type == 1 ? 'Full Day' : ($leave->leave_day_type == 2 ? 'FN' : 'AN') }}
                                                      -

                                                      {{ $leave->leave_status == 0 ? 'Pending' : ($leave->leave_status == 1 ? 'Approved' : 'Rejected') }}
                                                      {{--  {{ $leave->leave_action_by_name }}  --}}
                                                  </span><br>
                                              @endif
                                          @endforeach

                                          @foreach ($movements as $movement)
                                              @php
                                                  $start = date('Y-m-d', strtotime($movement->start_date));
                                                  $end = date('Y-m-d', strtotime($movement->end_date));

                                              @endphp
                                              @if ($item['date'] >= $start && $item['date'] <= $end && $movement->mov_user_id == $data['user_id'])
                                                  @php
                                                      $late_flag++;
                                                      array_push($array_excemption_dates, $item['date']);
                                                  @endphp
                                                  <span class="badge bg-label-dark p-2 mb-1">
                                                      {{ $movement->title }} -
                                                      {{--  {{ $movement->type }} -  --}}

                                                      {{ $movement->movement_status == 0 ? 'Pending' : ($movement->movement_status == 1 ? 'Approved' : 'Rejected') }}
                                                  </span><br>
                                              @endif
                                          @endforeach

                                      </td>
                                      @php
                                      $uniqueData = array_unique($array_excemption_dates);
                                          // if ($late_flag == 0) {
                                         //     $lateby = $lateby + $data['LateBy'];
                                         //     $earlyby = $earlyby + $data['EarlyBy'];
                                        //  }
                                      @endphp



                                    </tr>
                                @endforeach
                                {{--  <tr>
                                    <td>lateby {{ print_r( $uniqueData ) }}</td>
                                    <td>lateby {{ $earlyby }}</td>
                                </tr>  --}}
                                @php
                                $total_duration = 0;
                                $lateby = 0;
                                $earlyby = 0;
                                $remaining_grace = 0;
                                $OT=0;
                                $totalDuration=0;
                                @endphp
                                @foreach ($attendance_data as $data)
                                  @php
                                  $lateby = $lateby + $data['LateBy'];
                                  $earlyby = $earlyby + $data['EarlyBy'];
                                  $startTime = new DateTime($data['InTime']);
                                  $endTime = new DateTime($data['OutTime']);

                                  // Calculate the difference
                                  $interval = $startTime->diff($endTime);

                                  // Convert the difference to minutes
                                  $minutes = ($interval->h * 60) + $interval->i;
                                  $minutes += ($interval->d * 24 * 60);

                                  if($minutes >= 480){
                                    $OT = $OT+( $minutes - 480);
                                }
                                $totalDuration =   $totalDuration + ($minutes)
                                  @endphp
                                @foreach ($uniqueData as $item)
                                            @if ($item == $data['date'])

                                            @php
                                            $lateby = $lateby -$data['LateBy'];
                                            $earlyby = $earlyby -$data['EarlyBy'];
                                            @endphp

                                            @endif
                                @endforeach
                                @endforeach
                                @php
                                    $LateByHours = floor($lateby / 60) . ' hours ' . $lateby % 60 . ' minutes';
                                    $EarlyByHours = floor($earlyby / 60) . ' hours ' . $earlyby % 60 . ' minutes';
                                    $remaining = $grace - ($lateby + $earlyby);
                                @endphp
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


              </div>
            </div>
          </div>

          <div class="accordion-item card">
            <h2 class="accordion-header d-flex align-items-center">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-2" aria-expanded="false">
                <i class="me-2 ti ti-sun ti-xs"></i>
              Grace Period Statistics
              </button>
            </h2>
            <div id="accordionWithIcon-2" class="accordion-collapse collapse">
              <div class="accordion-body">

                {{--  <div class="row">


                  <!-- Orders -->
                  <div class="col-lg-3 col-6 mb-4">
                      <div class="card bg-gradient-primary">
                          <div class="card-body text-center">
                              <div class="badge rounded-pill p-2 bg-label-success mb-2"><i class="ti ti-chart-pie-2 ti-sm"></i></div>
                              <h5 class="card-title mb-2"><small>Minutes<br></small>{{ $DurationMinutes }}</h5>
                              <h5 class="card-title mb-2"><small>Hours<br></small>{{ $durationHours }}</h5>
                              <small class="badge rounded-pill p-2 bg-label-danger mb-2">Total Duration</small>
                          </div>
                      </div>
                  </div>

                  <!-- Reviews -->
                  <div class="col-lg-3 col-6 mb-4">
                      <div class="card">
                          <div class="card-body text-center">
                              <div class="badge rounded-pill p-2 bg-label-danger mb-2"><i class="ti ti ti-clock ti-sm"></i></div>

                              <h5 class="card-title mb-2"><small>Minutes<br></small>{{ $lateby }}</h5>
                              <h5 class="card-title mb-2"><small>Hours<br></small>{{ $LateByHours }}</h5>
                              <small class="badge rounded-pill p-2 bg-label-danger mb-2">Late By</small>
                          </div>
                      </div>
                  </div>
                  <div class="col-lg-3 col-6 mb-4">
                      <div class="card">
                          <div class="card-body text-center">
                              <div class="badge rounded-pill p-2 bg-label-warning mb-2"><i class="ti ti ti-clock ti-sm"></i></div>

                              <h5 class="card-title mb-2"><small>Minutes<br></small>{{ $earlyby }}</h5>
                              <h5 class="card-title mb-2"><small>Hours<br></small>{{ $EarlyByHours }}</h5>
                              <small class="badge rounded-pill p-2 bg-label-danger mb-2">Early Exit By</small>
                          </div>
                      </div>
                  </div>
                  <div class="col-lg-3 col-6 mb-4">
                      <div class="card">
                          <div class="card-body text-center">
                              <div class="badge rounded-pill p-2 bg-label-primary mb-2"><i class="ti ti-alert-triangle ti-sm"></i>
                              </div>

                              <h5 class="card-title mb-2"><small>Total Minutes<br></small>{{ $grace }}</h5>
                              <h5 class="card-title mb-2"><small>Remaining Minutes<br></small>{{ $remaining }}</h5>
                              <small class="badge rounded-pill p-2 bg-label-danger mb-2">Grace Period</small>
                          </div>
                      </div>
                  </div>
              </div>  --}}


              <div class="col-xl-12 col-md-12 order-2 order-lg-1">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-0">Consolidation Report</h5>
                            <small class="text-muted">Attendance</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" id="sourceVisits" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                            </button>

                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 pb-1">
                                <div class="d-flex align-items-start">
                                    <div class="badge bg-label-primary p-2 me-3 rounded"><i class="ti ti-shadow ti-sm"></i>
                                    </div>
                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Total Number of Days</h6>
                                            <small class="text-muted">In Days</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0">{{ $numberof_days}}</p>

                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3 pb-1">
                                <div class="d-flex align-items-start">
                                    <div class="badge bg-label-secondary p-2 me-3 rounded"><i class="ti ti-globe ti-sm"></i>
                                    </div>
                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Total Working Days</h6>
                                            <small class="text-muted">In Days</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0">{{$total_working_days}}</p>

                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3 pb-1">
                                <div class="d-flex align-items-start">
                                    <div class="badge bg-label-warning p-2 me-3 rounded"><i class="ti ti-mail ti-sm"></i>
                                    </div>
                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Number of Leaves</h6>
                                            <small class="text-muted">In Days</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0">{{$number_of_leaves}}</p>

                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3 pb-1">
                                <div class="d-flex align-items-start">
                                    <div class="badge bg-label-primary p-2 me-3 rounded"><i
                                            class="ti ti-external-link ti-sm"></i></div>
                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Net Working Days by the Employee</h6>
                                            <small class="text-muted">In Days</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0">{{$total_working_days_by_employee}}</p>

                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3 pb-1">
                                <div class="d-flex align-items-start">
                                    <div class="badge bg-label-success p-2 me-3 rounded"><i class="ti ti ti-clock ti-sm"></i>
                                    </div>
                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Total Work Hours</h6>
                                            <small class="text-muted">In Minutes</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0">{{$DurationMinutes}}</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-2">
                                <div class="d-flex align-items-start">
                                    <div class="badge bg-label-primary p-2 me-3 rounded"><i class="ti ti ti-clock ti-sm"></i>
                                    </div>
                                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Average Work Hours/Day</h6>
                                            <small class="text-muted">In Minutes</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0">{{round($DurationMinutes/$total_working_days_by_employee)}}</p>

                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li class="mb-2">
                              <div class="d-flex align-items-start">
                                  <div class="badge bg-label-danger p-2 me-3 rounded"><i class="ti ti-run ti-sm"></i>
                                  </div>
                                  <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                      <div class="me-2">
                                          <h6 class="mb-0">Total Late Minutes</h6>
                                          <small class="text-muted">In Minutes</small>
                                      </div>
                                      <div class="d-flex align-items-center">
                                          <p class="mb-0">{{ $lateby }}</p>

                                      </div>
                                  </div>
                              </div>
                          </li>

                          <li class="mb-2">
                            <div class="d-flex align-items-start">
                                <div class="badge bg-label-danger p-2 me-3 rounded"><i class="ti ti-run ti-sm"></i>
                                </div>
                                <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">Total Early Exit</h6>
                                        <small class="text-muted">In Minutes</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <p class="mb-0">{{ $earlyby }}</p>

                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="mb-2">
                          <div class="d-flex align-items-start">
                              <div class="badge bg-label-success p-2 me-3 rounded"><i class="ti ti-star ti-sm"></i>
                              </div>
                              <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                  <div class="me-2">
                                      <h6 class="mb-0">Total Grace Period Avialable</h6>
                                      <small class="text-muted">In Minutes</small>
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <p class="mb-0">{{ $grace }}</p>

                                  </div>
                              </div>
                          </div>
                      </li>

                      <li class="mb-2">
                        <div class="d-flex align-items-start">
                            <div class="badge bg-label-warning p-2 me-3 rounded"><i class="ti ti-alert-triangle ti-sm"></i>
                            </div>
                            <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Availed Grace Period</h6>
                                    <small class="text-muted">In Minutes</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <p class="mb-0">{{ $earlyby+$lateby }}</p>

                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="mb-2">
                        <div class="d-flex align-items-start">
                            <div class="badge bg-label-warning p-2 me-3 rounded"><i class="ti ti-alert-triangle ti-sm"></i>
                            </div>
                            <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Balance Grace Period</h6>
                                    <small class="text-muted">In Minutes</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <p class="mb-0">{{ $remaining }}</p>

                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="mb-2">
                      <div class="d-flex align-items-start">
                          <div class="badge bg-label-warning p-2 me-3 rounded"><i class="ti ti ti-clock ti-sm"></i>
                          </div>
                          <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                              <div class="me-2">
                                  <h6 class="mb-0">Total Over Time</h6>
                                  <small class="text-muted">In Minutes</small>
                              </div>
                              <div class="d-flex align-items-center">
                                  <p class="mb-0">{{$OT}}</p>

                              </div>
                          </div>
                      </div>
                  </li>

                        </ul>
                    </div>
                </div>
            </div>


              </div>
            </div>
          </div>
          <div class="accordion-item card">
            <h2 class="accordion-header d-flex align-items-center">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-3" aria-expanded="false">
                <i class="me-2 ti ti-replace ti-xs"></i>
               Leave Balance
              </button>
            </h2>
            <div id="accordionWithIcon-3" class="accordion-collapse collapse">
              <div class="accordion-body">


                <div class="row g-4 mb-4">


                  <div class="alert alert-warning alert-dismissible d-flex align-items-baseline" role="alert">
                    <span class="alert-icon alert-icon-lg text-primary me-2">
                      <i class="ti ti-calendar ti-sm"></i>
                    </span>
                    <div class="d-flex flex-column ps-1">
                      <input type="hidden" id="date_start" value="{{$date_start}}" />
                      <input type="hidden" id="date_end" value="{{$date_end}}" />
                      <p class="mb-0"> Leave Statistics for the period of {{$date_start}} TO {{$date_end}}</p>
                      <h5 class="alert-heading mb-2"></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                      </button>
                    </div>
                  </div>

                    @foreach ($leaves_total_credit_details as $leave)
                    <div class="col-sm-6 col-xl-3">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                              <span>{{$leave['leave_type']}}</span>
                              <div class="d-flex align-items-center my-1">
                                <small>Available - </small><h4 class="mb-0 me-2">  @if($leave['leave_type_id'] <= 3 ) {{$leave['balance_credit']}} @endif</h4>
                                <input type="hidden"  id="typeBalance{{$leave['leave_type_id']}}" value={{$leave['balance_credit']}} />
                                <input type="hidden"  id="typeTotal{{$leave['leave_type_id']}}" value={{$leave['total_leaves_credit']}} />
                                <input type="hidden"  id="typeRequested{{$leave['leave_type_id']}}" value={{$leave['pending_leave']}} />
                                <input type="hidden"  id="typeAvailed{{$leave['leave_type_id']}}" value={{$leave['pending_leave']}} />
                              </div>
                              <span>Total -  @if($leave['leave_type_id'] <= 3 ) {{$leave['total_leaves_credit']}} @endif</span>
                            </div>
                            <span class="badge bg-label-primary rounded p-2">
                              <i class="ti ti-user ti-sm"></i>
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                    @endforeach

                  </div>


              </div>
            </div>
          </div>


        </div>
      </div>








    </div>
