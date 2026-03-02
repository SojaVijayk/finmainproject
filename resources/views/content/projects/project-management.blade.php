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
  .show-read-more .more-text{
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
<h4 class="fw-semibold mb-4">Project List</h4>
<div class="row g-4">
{{--  <p class="mb-4 float-end"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">Add Project</button></p>  --}}
</div>
<!-- Role cards -->
<div class="row g-4">
  @foreach ($projects as $project)
  @php
     //  $array=['text-white bg-primary','text-white bg-success','text-white bg-info','text-white bg-danger','text-white bg-warning'];
      //$index = array_rand($array);
      //$array[$index]
  @endphp

  <div class="col-xl-4 col-lg-6 col-md-6">
    <div class="card ">
      <div class="card-header">
        <div class="d-flex align-items-start">
          <div class="d-flex align-items-start">
            <div class="avatar me-2">
              <img src="{{ asset('assets/img/icons/brands/social-label.png') }}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="me-2 ms-1">
              <h5  class="mb-0"><a href="/project/dashboard/{{$project->id}}" class="stretched-link text-body">{{$project->project_name}}</a></h5>
              <div class="client-info"><strong>Clients: </strong><br>
                @foreach($project->clients as $key => $value)
                <span class="text-muted  ">
                  <div class="badge bg-label-primary me-3 rounded p-2">
                    <i class="ti ti-user ti-sm"></i>   {{$value->client_name}}
                  </div>

                </span>
                @endforeach
              </div>
            </div>
          </div>
          <div class="ms-auto">
            <div class="dropdown zindex-2">
              <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="javascript:void(0);">Rename project</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">View details</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Add to favorites</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="javascript:void(0);">Leave Project</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body ">
        <div class="d-flex align-items-center flex-wrap">
          <div class="bg-lighter px-3 py-2 rounded me-auto mb-3">
            <h6 class="mb-0">{{$project->total_cost}} <span class="text-body fw-normal">/ $18.2k</span></h6>
            <span>Total Budget</span>
          </div>
          <div class="text-end mb-3">
            <h6 class="mb-0">Start Date: <span class="text-body fw-normal">{{$project->expected_start_date}}</span></h6>
            <h6 class="mb-1">Deadline: <span class="text-body fw-normal">{{$project->expected_end_date}}</span></h6>
          </div>
        </div>
        <p class="mb-0 show-read-more "> {{$project->description}}</p>
      </div>
      <div class="card-body border-top">
        <div class="d-flex align-items-center mb-3">
          <h6 class="mb-1">All Hours: <span class="text-body fw-normal">380/244</span></h6>
          <span class="badge bg-label-success ms-auto">28 Days left</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2 pb-1">
          <small>Task: 290/344</small>
          <small>95% Completed</small>
        </div>
        <div class="progress mb-2" style="height: 8px;">
          <div class="progress-bar" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex align-items-center pt-1">
          <div class="d-flex align-items-center">
            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2 mt-1">
              @foreach($project->members as $key => $value)
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{$value->name}}" class="avatar avatar-sm pull-up">

                <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar" class="rounded-circle" />
              </li>
              @endforeach

              <li><small class="text-muted p-1">{{$project->members_count}} Members</small></li>
            </ul>
          </div>
          <div class="ms-auto">
            <a href="javascript:void(0);" class="text-body"><i class="ti ti-message-dots ti-sm"></i> 15</a>
          </div>
        </div>
      </div>
    </div>
  </div>




  @endforeach



</div>
<!--/ Client cards -->

<!-- Add Client Modal -->
{{--  @include('_partials/_modals/modal-add-client')  --}}
@include('_partials/_modals/modal-add-project',['clients' => $clients,'projectTypes' => $projectTypes,'leads' => $leads,'members' => $members,])
<!-- / Add Client Modal -->
@endsection
