@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Clients')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<style>
  .show-read-more .more-text {
    display: none;
  }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/forms-selects.js')}}"></script>

<script>
  $(document).ready(function(){
    var maxLength = 300;
    $(".show-read-more").each(function(){
        var myStr = $(this).text();
        if($.trim(myStr).length > maxLength){
            var newStr = myStr.substring(0, maxLength);
            var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
            $(this).empty().html(newStr);
            $(this).append(' <a href="javascript:void(0);" class="read-more">read more...</a>');
            $(this).append('<span class="more-text">' + removedStr + '</span>');
        }
    });
    $(document).on('click', '.container .show-read-more', function(e) {
      alert();
    });
    $(document).on('click', '.read-more', function(e) {
    {{--  $(".read-more").click(function(){  --}}
      alert();
        $(this).siblings(".more-text").contents().unwrap();
        $(this).remove();
    });
});


</script>
@endsection


@section('content')
@php
$user = Auth::user();
@endphp
{{-- <h4 class="fw-semibold mb-4">Project List</h4> --}}
<div class="row g-4 mt-2">
  {{-- <p class="mb-4 float-end"><button type="button" class="btn btn-primary" data-bs-toggle="modal"
      data-bs-target="#addProjectModal">Add Project</button></p> --}}
</div>
<!-- Role cards -->
<div class="card text-center">
  <div class="card-header">
    <ul class="nav nav-pills" role="tablist">
      <li class="nav-item">
        <button type="button" class="nav-link " role="tab" data-bs-toggle="tab"
          data-bs-target="#navs-pills-within-card-active" aria-controls="navs-pills-within-card-active"
          aria-selected="true">Initiated</button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
          data-bs-target="#navs-pills-within-card-ongoing" aria-controls="navs-pills-within-card-link"
          aria-selected="false">Ongoing</button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link " role="tab" data-bs-toggle="tab"
          data-bs-target="#navs-pills-within-card-completed" aria-selected="false">Completed</button>
      </li>

      <li class="nav-item">
        <button type="button" class="nav-link " role="tab" data-bs-toggle="tab"
          data-bs-target="#navs-pills-within-card-archived" aria-selected="false">Archived</button>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content p-0">
      <div class="tab-pane fade " id="navs-pills-within-card-active" role="tabpanel">
        <h4 class="card-title">Initiated Project List</h4>
        <p class="card-text">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="card h-100">
              <div class="card-body text-center">
                <i class="fas fa-file-alt fa-3x mb-3 text-primary"></i>
                <h6>Planning Stage</h6>
                <a href="{{ route('pms.requirements.index') }}" class="stretched-link"></a>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="card h-100">
              <div class="card-body text-center">
                <i class="fas fa-file-alt fa-3x mb-3 text-warning"></i>
                <h6>Proposal Stage </h6>
                <a href="{{ route('pms.proposals.index') }}" class="stretched-link"></a>
              </div>
            </div>
          </div>
        </div>

        </p>
        <div class="row g-4">
          @forelse($projects->where('status',0) as $project)
          @php
          $userIsTeamLead = $project->teamMembers()->where('user_id',
          auth()->id())->whereIn('role',['lead','leadMember'])->exists();
          @endphp
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card ">
              <div class="card-header">
                <h4 class="text-primary me-2 ms-1">{{$project->project_code}}</h4>
                <div class="d-flex align-items-start">
                  <div class="d-flex align-items-start">
                    {{-- <div class="avatar me-2">
                      <img src="{{ asset('assets/img/icons/brands/social-label.png') }}" alt="Avatar"
                        class="rounded-circle" />
                    </div> --}}
                    <div class="me-2 ms-1">
                      <h5 class="mb-0"><a href="{{ route('pms.projects.show', $project->id) }}"
                          class="stretched-link text-body">{{$project->title}}</a></h5>
                      <div class="client-info mt-2"><strong>Client: </strong><br>

                        <span class="text-muted  ">
                          <div class="badge bg-label-primary me-3 rounded p-2">
                            <i class="ti ti-user ti-sm"></i> {{$project->requirement->client->client_name}}
                          </div>

                        </span>

                      </div>
                    </div>
                  </div>
                  <div class="ms-auto">
                    <div class="dropdown zindex-2">
                      <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown"
                        aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('pms.projects.show', $project->id) }}">View
                            details</a></li>

                        @if($project->status == \App\Models\PMS\Project::STATUS_INITIATED || $project->status ==
                        \App\Models\PMS\Project::STATUS_ONGOING && ($user->hasRole('Project Investigator') ||
                        $userIsTeamLead ))
                        <li> <a href="{{ route('pms.projects.edit', $project->id) }}" class="dropdown-item">
                            {{-- <i class="fas fa-edit"></i> --}} Edit
                          </a> </li>
                        @endif

                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body ">
                <div class="d-flex align-items-center flex-wrap">
                  <div class="bg-lighter px-3 py-2 rounded me-auto mb-3">
                    <h6 class="mb-0">{{$project->total_cost}} <span
                        class="text-body fw-normal">{{$project->budget}}</span></h6>
                    <span>Total Budget</span>
                  </div>
                  <div class="text-end mb-3">
                    <h6 class="mb-0">Start Date: <span class="text-body fw-normal">{{ $project->start_date->format('d M
                        Y')
                        }}</span></h6>
                    <h6 class="mb-1">Deadline: <span class="text-body fw-normal">{{ $project->end_date->format('d M Y')
                        }}</span></h6>
                  </div>
                </div>
                <p class="mb-0 show-read-more "> <span class="badge bg-{{ $project->status_badge_color }}">
                    {{ $project->status_name }}
                  </span></p>
              </div>
              <div class="card-body border-top">
                <div class="d-flex align-items-center mb-3">
                  <h6 class="mb-1">All Hours: <span class="text-body fw-normal">380/244</span></h6>
                  <span class="badge bg-label-success ms-auto">28 Days left</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1">
                  {{-- <small>Task: 290/344</small>
                  <small>95% Completed</small> --}}
                </div>
                {{-- <div class="progress mb-2" style="height: 8px;">
                  <div class="progress-bar" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0"
                    aria-valuemax="100"></div>
                </div> --}}
                <div class="progress mb-2" style="height: 20px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                    aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $project->completion_percentage }}%
                  </div>
                </div>
                {{-- <div class="d-flex align-items-center pt-1">
                  <div class="d-flex align-items-center">
                    <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2 mt-1">
                      @foreach($project->members as $key => $value)
                      <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                        title="{{$value->name}}" class="avatar avatar-sm pull-up">

                        <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar"
                          class="rounded-circle" />
                      </li>
                      @endforeach

                      <li><small class="text-muted p-1">{{$project->members_count}} Members</small></li>
                    </ul>
                  </div>
                  <div class="ms-auto">
                    <a href="javascript:void(0);" class="text-body"><i class="ti ti-message-dots ti-sm"></i> 15</a>
                  </div>
                </div> --}}
              </div>
            </div>
          </div>

          @empty
          <div class="row justify-content-center">
            <div class="col-md-4 d-flex justify-content-center align-items-center mt-2">
              <dotlottie-player src="https://lottie.host/417f062a-fdd1-49d7-81bf-c7941ce501ca/5jjeRHD9e3.lottie"
                background="transparent" speed="1" style="width: 350px; height: 350px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>

          @endforelse
          {{ $projects->links('pagination::bootstrap-5') }}

        </div>
      </div>
      <div class="tab-pane fade show active" id="navs-pills-within-card-ongoing" role="tabpanel">
        <h4 class="card-title">Ongoing Project List</h4>
        {{-- <p class="card-text">With supporting text below as a natural lead-in to additional content.</p> --}}
        <div class="row g-4">
          @forelse($projects_ongoing as $project)
          @php
          $userIsTeamLead = $project->teamMembers()->where('user_id',
          auth()->id())->whereIn('role',['lead','leadMember'])->exists();
          @endphp
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card ">
              <div class="card-header">
                <h5 class="text-primary me-2 ms-1">{{$project->project_code}}</h5>

                <div class="d-flex align-items-start">
                  <div class="d-flex align-items-start">
                    {{-- <div class="avatar me-2">
                      <img src="{{ asset('assets/img/icons/brands/social-label.png') }}" alt="Avatar"
                        class="rounded-circle" />
                    </div> --}}
                    <div class="me-2 ms-1">
                      <h6 class="mb-0"><a href="{{ route('pms.projects.show', $project->id) }}"
                          class="stretched-link text-body">{{$project->title}}</a></h6>
                      <div class="client-info mt-2"><strong>Client: </strong>

                        <span class="text-muted  ">
                          <div class="badge bg-label-primary me-3 rounded p-2">
                            <i class="ti ti-user ti-sm"></i> {{$project->requirement->client->client_name}}
                          </div>

                        </span>

                      </div>
                    </div>
                  </div>
                  <div class="ms-auto">
                    <div class="dropdown zindex-2">
                      <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown"
                        aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('pms.projects.show', $project->id) }}">View
                            details</a></li>

                        @if($project->status == \App\Models\PMS\Project::STATUS_INITIATED || $project->status ==
                        \App\Models\PMS\Project::STATUS_ONGOING && ($user->hasRole('Project Investigator') ||
                        $userIsTeamLead))
                        <li> <a href="{{ route('pms.projects.edit', $project->id) }}" class="dropdown-item">
                            {{-- <i class="fas fa-edit"></i> --}} Edit
                          </a> </li>
                        @endif
                        <li>
                          <a type="button" class="btn btn-label-danger dropdown-item"
                            href="{{ route('pms.projects.kanban.index',$project->id) }}">Tasks</a>
                        </li>

                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body ">
                <div class="d-flex align-items-center flex-wrap">
                  <div class="bg-lighter px-3 py-2 rounded me-auto mb-3">
                    <h6 class="mb-0">{{$project->total_cost}} <span
                        class="text-body fw-normal">{{$project->budget}}</span></h6>
                    <span>Total Budget</span>
                  </div>
                  <div class="text-end mb-3">
                    <h6 class="mb-0">Start Date: <span class="text-body fw-normal">{{ $project->start_date->format('d M
                        Y')
                        }}</span></h6>
                    <h6 class="mb-1">Deadline: <span class="text-body fw-normal">{{ $project->end_date->format('d M Y')
                        }}</span></h6>
                  </div>
                </div>
                <p class="mb-0 show-read-more "> <span class="badge bg-{{ $project->status_badge_color }}">
                    {{ $project->status_name }}
                  </span></p>
              </div>
              <div class="card-body border-top">
                <div class="d-flex align-items-center mb-3">
                  @php

                  $start = \Carbon\Carbon::parse($project->start_date);
                  $end = \Carbon\Carbon::parse($project->end_date);
                  $now = \Carbon\Carbon::now();

                  // Total duration
                  $totalDays = $start->diffInDays($end);
                  $totalHours = $start->diffInHours($end);

                  // Remaining balance
                  $remainingDays = $now->lessThan($end) ? $now->diffInDays($end) : 0;
                  $remainingHours = $now->lessThan($end) ? $now->diffInHours($end) : 0;
                  @endphp
                  <h6 class="mb-1">All Hours: <span
                      class="text-body fw-normal">{{$totalHours}}/{{$remainingHours}}</span></h6>
                  <span class="badge bg-label-success ms-auto">{{ $remainingDays}} Days left</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1">
                  {{-- <small>Task: 290/344</small>
                  <small>95% Completed</small> --}}
                </div>
                {{-- <div class="progress mb-2" style="height: 8px;">
                  <div class="progress-bar" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0"
                    aria-valuemax="100"></div>
                </div> --}}
                <div class="progress mb-2" style="height: 20px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                    aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $project->completion_percentage }}%
                  </div>
                </div>
                {{-- <div class="d-flex align-items-center pt-1">
                  <div class="d-flex align-items-center">
                    <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2 mt-1">
                      @foreach($project->members as $key => $value)
                      <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                        title="{{$value->name}}" class="avatar avatar-sm pull-up">

                        <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar"
                          class="rounded-circle" />
                      </li>
                      @endforeach

                      <li><small class="text-muted p-1">{{$project->members_count}} Members</small></li>
                    </ul>
                  </div>
                  <div class="ms-auto">
                    <a href="javascript:void(0);" class="text-body"><i class="ti ti-message-dots ti-sm"></i> 15</a>
                  </div>
                </div> --}}
              </div>
            </div>
          </div>




          @empty
          <div class="row justify-content-center">
            <div class="col-md-4 d-flex justify-content-center align-items-center mt-2">
              <dotlottie-player src="https://lottie.host/417f062a-fdd1-49d7-81bf-c7941ce501ca/5jjeRHD9e3.lottie"
                background="transparent" speed="1" style="width: 350px; height: 350px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>

          @endforelse

          {{-- {{ $projects_ongoing->links('pagination::bootstrap-5') }} --}}
        </div>
      </div>
      <div class="tab-pane fade" id="navs-pills-within-card-completed" role="tabpanel">
        <h4 class="card-title">Completed Project List</h4>
        {{-- <p class="card-text">With supporting text below as a natural lead-in to additional content.</p> --}}
        <div class="row g-4">
          @forelse($projects_completed as $project)
          @php
          $userIsTeamLead = $project->teamMembers()->where('user_id',
          auth()->id())->whereIn('role',['lead','leadMember'])->exists();
          @endphp
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card ">
              <div class="card-header">
                <h5 class="text-primary me-2 ms-1">{{$project->project_code}}</h5>

                <div class="d-flex align-items-start">
                  <div class="d-flex align-items-start">
                    {{-- <div class="avatar me-2">
                      <img src="{{ asset('assets/img/icons/brands/social-label.png') }}" alt="Avatar"
                        class="rounded-circle" />
                    </div> --}}
                    <div class="me-2 ms-1">
                      <h6 class="mb-0"><a href="{{ route('pms.projects.show', $project->id) }}"
                          class="stretched-link text-body">{{$project->title}}</a></h6>
                      <div class="client-info mt-2"><strong>Client: </strong>

                        <span class="text-muted  ">
                          <div class="badge bg-label-primary me-3 rounded p-2">
                            <i class="ti ti-user ti-sm"></i> {{$project->requirement->client->client_name}}
                          </div>

                        </span>

                      </div>
                    </div>
                  </div>
                  <div class="ms-auto">
                    <div class="dropdown zindex-2">
                      <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown"
                        aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('pms.projects.show', $project->id) }}">View
                            details</a></li>
                        @if($project->status == \App\Models\PMS\Project::STATUS_INITIATED || $project->status ==
                        \App\Models\PMS\Project::STATUS_COMPLETED || $project->status ==
                        \App\Models\PMS\Project::STATUS_ONGOING && ($user->hasRole('Project
                        Investigator')||$userIsTeamLead ))
                        <li> <a href="{{ route('pms.projects.edit', $project->id) }}" class="dropdown-item">
                            {{-- <i class="fas fa-edit"></i> --}} Edit
                          </a> </li>
                        @endif
                        <li class="dropdown-item">

                          @if( $user->hasRole('Project Investigator'))
                          <form action="{{ route('pms.projects.archive', $project->id) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-label-primary w-100 ">
                              <i class="fas fa-check-circle"></i> Mark as Archived
                            </button>
                          </form>
                          @endif
                        </li>



                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body ">
                <div class="d-flex align-items-center flex-wrap">
                  <div class="bg-lighter px-3 py-2 rounded me-auto mb-3">
                    <h6 class="mb-0">{{$project->total_cost}} <span
                        class="text-body fw-normal">{{$project->budget}}</span></h6>
                    <span>Total Budget</span>
                  </div>
                  <div class="text-end mb-3">
                    <h6 class="mb-0">Start Date: <span class="text-body fw-normal">{{ $project->start_date->format('d M
                        Y')
                        }}</span></h6>
                    <h6 class="mb-1">Deadline: <span class="text-body fw-normal">{{ $project->end_date->format('d M Y')
                        }}</span></h6>
                  </div>
                </div>
                <p class="mb-0 show-read-more "> <span class="badge bg-{{ $project->status_badge_color }}">
                    {{ $project->status_name }}
                  </span></p>
              </div>
              <div class="card-body border-top">
                <div class="d-flex align-items-center mb-3">
                  {{-- <h6 class="mb-1">All Hours: <span class="text-body fw-normal">380/244</span></h6>
                  <span class="badge bg-label-success ms-auto">28 Days left</span> --}}
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1">

                  {{-- <small>Task: 290/344</small>
                  <small>95% Completed</small> --}}
                </div>
                {{-- <div class="progress mb-2" style="height: 8px;">
                  <div class="progress-bar" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0"
                    aria-valuemax="100"></div>
                </div> --}}
                <div class="progress mb-2" style="height: 20px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                    aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $project->completion_percentage }}%
                  </div>
                </div>
                {{-- <div class="d-flex align-items-center pt-1">
                  <div class="d-flex align-items-center">
                    <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2 mt-1">
                      @foreach($project->members as $key => $value)
                      <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                        title="{{$value->name}}" class="avatar avatar-sm pull-up">

                        <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar"
                          class="rounded-circle" />
                      </li>
                      @endforeach

                      <li><small class="text-muted p-1">{{$project->members_count}} Members</small></li>
                    </ul>
                  </div>
                  <div class="ms-auto">
                    <a href="javascript:void(0);" class="text-body"><i class="ti ti-message-dots ti-sm"></i> 15</a>
                  </div>
                </div> --}}
              </div>
            </div>
          </div>




          @empty
          <div class="row justify-content-center">
            <div class="col-md-4 d-flex justify-content-center align-items-center mt-2">
              <dotlottie-player src="https://lottie.host/417f062a-fdd1-49d7-81bf-c7941ce501ca/5jjeRHD9e3.lottie"
                background="transparent" speed="1" style="width: 350px; height: 350px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>

          @endforelse

          {{ $projects_completed->links('pagination::bootstrap-5') }}
        </div>
      </div>

      <div class="tab-pane fade" id="navs-pills-within-card-archived" role="tabpanel">
        <h4 class="card-title">Archived Project List</h4>
        {{-- <p class="card-text">With supporting text below as a natural lead-in to additional content.</p> --}}
        <div class="row g-4">
          @forelse($projects_archived as $project)

          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card ">
              <div class="card-header">
                <h5 class="text-primary me-2 ms-1">{{$project->project_code}}</h5>

                <div class="d-flex align-items-start">
                  <div class="d-flex align-items-start">
                    {{-- <div class="avatar me-2">
                      <img src="{{ asset('assets/img/icons/brands/social-label.png') }}" alt="Avatar"
                        class="rounded-circle" />
                    </div> --}}
                    <div class="me-2 ms-1">
                      <h6 class="mb-0"><a href="{{ route('pms.projects.show', $project->id) }}"
                          class="stretched-link text-body">{{$project->title}}</a></h6>
                      <div class="client-info mt-2"><strong>Client: </strong>

                        <span class="text-muted  ">
                          <div class="badge bg-label-primary me-3 rounded p-2">
                            <i class="ti ti-user ti-sm"></i> {{$project->requirement->client->client_name}}
                          </div>

                        </span>

                      </div>
                    </div>
                  </div>
                  <div class="ms-auto">
                    <div class="dropdown zindex-2">
                      <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown"
                        aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('pms.projects.show', $project->id) }}">View
                            details</a></li>

                        @if($project->status == \App\Models\PMS\Project::STATUS_INITIATED || $project->status ==
                        \App\Models\PMS\Project::STATUS_ONGOING && $user->hasRole('Project Investigator'))
                        <li> <a href="{{ route('pms.projects.edit', $project->id) }}" class="dropdown-item">
                            {{-- <i class="fas fa-edit"></i> --}} Edit
                          </a> </li>
                        @endif

                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body ">
                <div class="d-flex align-items-center flex-wrap">
                  <div class="bg-lighter px-3 py-2 rounded me-auto mb-3">
                    <h6 class="mb-0">{{$project->total_cost}} <span
                        class="text-body fw-normal">{{$project->budget}}</span></h6>
                    <span>Total Budget</span>
                  </div>
                  <div class="text-end mb-3">
                    <h6 class="mb-0">Start Date: <span class="text-body fw-normal">{{ $project->start_date->format('d M
                        Y')
                        }}</span></h6>
                    <h6 class="mb-1">Deadline: <span class="text-body fw-normal">{{ $project->end_date->format('d M Y')
                        }}</span></h6>
                  </div>
                </div>
                <p class="mb-0 show-read-more "> <span class="badge bg-{{ $project->status_badge_color }}">
                    {{ $project->status_name }}
                  </span></p>
              </div>
              <div class="card-body border-top">
                <div class="d-flex align-items-center mb-3">
                  {{-- <h6 class="mb-1">All Hours: <span class="text-body fw-normal">380/244</span></h6>
                  <span class="badge bg-label-success ms-auto">28 Days left</span> --}}
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1">
                  {{-- <small>Task: 290/344</small>
                  <small>95% Completed</small> --}}
                </div>
                {{-- <div class="progress mb-2" style="height: 8px;">
                  <div class="progress-bar" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0"
                    aria-valuemax="100"></div>
                </div> --}}
                <div class="progress mb-2" style="height: 20px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                    aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $project->completion_percentage }}%
                  </div>
                </div>
                {{-- <div class="d-flex align-items-center pt-1">
                  <div class="d-flex align-items-center">
                    <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2 mt-1">
                      @foreach($project->members as $key => $value)
                      <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                        title="{{$value->name}}" class="avatar avatar-sm pull-up">

                        <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar"
                          class="rounded-circle" />
                      </li>
                      @endforeach

                      <li><small class="text-muted p-1">{{$project->members_count}} Members</small></li>
                    </ul>
                  </div>
                  <div class="ms-auto">
                    <a href="javascript:void(0);" class="text-body"><i class="ti ti-message-dots ti-sm"></i> 15</a>
                  </div>
                </div> --}}
              </div>
            </div>
          </div>




          @empty
          <div class="row justify-content-center">
            <div class="col-md-4 d-flex justify-content-center align-items-center mt-2">
              <dotlottie-player src="https://lottie.host/417f062a-fdd1-49d7-81bf-c7941ce501ca/5jjeRHD9e3.lottie"
                background="transparent" speed="1" style="width: 350px; height: 350px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>

          @endforelse

          {{ $projects_archived->links('pagination::bootstrap-5') }}
        </div>
      </div>
    </div>

  </div>
</div>


@endsection