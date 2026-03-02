@extends('layouts/layoutMaster')

@section('title', 'User List - Pages')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />


<link rel="stylesheet" href="{{ asset('assets/vendor/libs/jkanban/jkanban.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/typography.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/katex.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/editor.css') }}" />

<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />


@endsection
@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-kanban.css') }}" />
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-faq.css')}}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>


<script src="{{ asset('assets/vendor//libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor//libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor//libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor//libs/jkanban/jkanban.js') }}"></script>
<script src="{{ asset('assets/vendor//libs/quill/katex.js') }}"></script>
<script src="{{ asset('assets/vendor//libs/quill/quill.js') }}"></script>

<script src="{{ asset('assets/vendor/libs/sortablejs/sortable.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/block-ui/block-ui.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/form-wizard-icons.js') }}"></script>3
<script src="{{ asset('assets/js/forms-file-upload.js') }}"></script>
<script src="{{ asset('assets/js/app-kanban.js') }}"></script>
<script src="{{ asset('assets/js/cards-actions.js') }}"></script>
<script src="{{ asset('assets/js/dashboards-crm.js') }}"></script>
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
<script>



</script>
@endsection

@section('content')

<div class="faq-header d-flex flex-column justify-content-center align-items-center rounded ">
  <h3 class="text-center text-white"> Project : {{ $project_details->project_name }} - Project ID : <span
      class="text-bold"> #{{ $project_details->id.'/'.date('Y') }} </span></h3>
  <div class="client-info">
    @foreach($project_details->clients as $key => $value)
    <span class="text-muted  ">
      <div class="badge bg-label-primary me-3 rounded p-2">
        <i class="ti ti-user ti-sm"></i> {{$value->client_name}}
      </div>

    </span>
    @endforeach
  </div>
  <p class="text-center text-white mb-0 px-3">
  <h6 class="mb-0 p-2 text-white">Start Date: <span
      class="text-white fw-normal">{{$project_details->expected_start_date}}</span>
    Deadline: <span class="text-white fw-normal">{{$project_details->expected_end_date}}
  </h6>

  </p>
  <div class="d-flex  align-items-center pt-1 text-white">
    <div class="d-flex align-items-center p-2">
      <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2 mt-1">
        @foreach($project_details->leads as $key => $value)
        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{$value->name}}"
          class="avatar avatar-sm pull-up">

          <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar" class="rounded-circle" />
        </li>
        @endforeach

        <li><small class="text-white p-1"> Leads : {{count($project_details->leads)}}</small></li>
      </ul>
    </div>
    {{-- <div class="ms-auto">
      <a href="javascript:void(0);" class="text-white"><i class="ti ti-message-dots ti-sm"></i>
        {{count($project_details->leads)}}</a>
    </div> --}}
    <div class="d-flex align-items-center p-2">
      <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2 mt-1">
        @foreach($project_details->members as $key => $value)
        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{$value->name}}"
          class="avatar avatar-sm pull-up">

          <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar" class="rounded-circle" />
        </li>
        @endforeach

        <li><small class="text-white p-1"> Members : {{count($project_details->members)}}</small></li>
      </ul>
    </div>

  </div>

</div>



<div class="card text-center">

  {{-- <div class="col-md-12">
    <div class="card text-white bg-secondary">
      <div class="card-header">
        <div class="divider">
          <div class="divider-text">
            <h5 class="card-title text-white"><small>Project</small> : {{ $project_details->project_name }} -
              <small>Project ID</small> : <span class="text-bold"> #{{ $project_details->id.'/'.date('Y') }} </span>
            </h5>
          </div>
          <input type="hidden" id="project_id" name="project_id" value={{ $project_details->id }} />
        </div>
      </div>

    </div>
  </div> --}}


  <div class="card-header">
    <ul class="nav nav-pills card-header-pills nav-fill" role="tablist">
      <li class="nav-item">
        <button type="button" class="nav-link active" data-bs-toggle="tab"
          data-bs-target="#navs-pills-within-card-active" role="tab">Overview</button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#navs-pills-within-card-link"
          role="tab">Baseline/Milestone</button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link task-btn" data-bs-toggle="tab" data-bs-target="#navs-pills-task"
          role="tab">Task</button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#navs-pills-file"
          role="tab">Files</button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#navs-pills-financial"
          role="tab">Financial</button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#navs-pills-more"
          role="tab">Moe</button>
      </li>

    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content p-0">


      <div class="tab-pane fade show active" id="navs-pills-within-card-active" role="tabpanel">


        <div class="row">
          <!-- Project Status -->
          <div class="col-4 col-xl-4 mb-4 col-md-4">
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0 card-title">Project Status</h5>
                <div class="dropdown">
                  <button class="btn p-0" type="button" id="projectStatusId" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="projectStatusId">
                    <a class="dropdown-item" href="javascript:void(0);">View More</a>
                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="d-flex align-items-start">
                  <div class="badge rounded bg-label-warning p-2 me-3 rounded"><i
                      class="ti ti-currency-dollar ti-sm"></i></div>
                  <div class="d-flex justify-content-between w-100 gap-2 align-items-center">
                    <div class="me-2">
                      <h6 class="mb-0">$4,3742</h6>
                      <small class="text-muted">Your Earnings</small>
                    </div>
                    <p class="mb-0 text-success">+10.2%</p>
                  </div>
                </div>
                <div id="projectStatusChart"></div>
                <div class="d-flex justify-content-between mb-3">
                  <h6 class="mb-0">Donates</h6>
                  <div class="d-flex">
                    <p class="mb-0 me-3">$756.26</p>
                    <p class="mb-0 text-danger">-139.34</p>
                  </div>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-1">
                  <h6 class="mb-0">Podcasts</h6>
                  <div class="d-flex">
                    <p class="mb-0 me-3">$2,207.03</p>
                    <p class="mb-0 text-success">+576.24</p>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- Projects table -->
          <div class="col-8 col-xl-8 col-sm-8 order-1 order-lg-8 mb-6 mb-lg-0">
            <div class="card">
              <div class="card-datatable table-responsive">
                <table class="datatables-projects table border-top">
                  <thead>
                    <tr>
                      <th></th>
                      <th></th>
                      <th>Name</th>
                      <th>Leader</th>
                      <th>Team</th>
                      <th class="w-px-200">Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
          <!--/ Projects table -->

        </div>
      </div>


      <div class="tab-pane fade" id="navs-pills-within-card-link" role="tabpanel">
        @can('project-create')
        <div class="  text-center mb-2">
          <button data-bs-target="#addMilestoneModal" data-bs-toggle="modal"
            class="btn btn-dark mb-2 text-nowrap add-new-client">Add New Milestone</button>
          {{-- e<p class="mb-0 mt-1">Add new Phase,</p> --}}
        </div>
        @endcan

        <div class="row" id="sortable-4">
          @php
          $index = 0;
          @endphp
          @foreach ($project_details->milestone as $item)
          @php
          $array = ['text-white bg-primary', 'text-white bg-success', 'text-white bg-secondary', 'text-white bg-danger',
          'text-white bg-warning'];
          // $array=[' btn-primary',' btn-outline-success',' btn-outline-secondary',' btn-outline-danger','
          btn-outline-warning'];

          $index_old = $index;
          $index = array_rand($array);
          if ($index_old == $index) {
          $index = array_rand($array);
          }

          @endphp



          <div class="col-xl-3 col-sm-6 mb-4 ">
            <div class="card h-100">
              <div class="card-header d-flex align-items-start justify-content-between pb-2">
                <h5 class="card-title mb-0 milestone-task">{{ $item->milestone }}</h5>
                <div class="dropdown">
                  <button class="btn p-0" type="button" id="progressStat" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="progressStat">
                    {{-- <a class="dropdown-item" href="javascript:void(0);">View More</a>
                    <a class="dropdown-item" href="javascript:void(0);">Delete</a> --}}
                  </div>
                </div>
              </div>
              <div class="card-body pt-1">
                <div class="d-flex justify-content-between align-items-center mb-2 gap-3 pt-1">
                  <h6 class="mb-0">Task</h6>
                  <div class="badge bg-label-success">+92k</div>
                </div>
                <div class="d-flex justify-content-between gap-3">
                  <p class="mb-0">10/25 Completed</p>
                  <span class="text-muted">85%</span>
                </div>
                <div class="d-flex align-items-center mt-1">
                  <div class="progress w-100" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: 85%" role="progressbar" aria-valuenow="85"
                      aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4 mb-2 gap-3 pt-1">
                  <h6 class="mb-0">{{ $item->due_date }}</h6>
                  <div class="badge bg-label-danger">+38 Days Left</div>
                </div>

                <div class="text-center align-items-center  mb-2">

                  <button type="button" class=" btn-sm btn btn-success milestone-task" data-id="{{ $item->id }}">View
                    Task</button>


                </div>

              </div>
            </div>
          </div>
          @endforeach


        </div>
      </div>

      <div class="tab-pane fade " id="navs-pills-task" role="tabpanel">
        <div class="col-sm-12 mb-5 align-items-center">
          {{-- <label class="form-label" for="milestone_filter">Milestone</label> --}}
          <select id="milestone_filter" name="milestone_filter" class="select2 form-select">
            @foreach ($project_details->milestone as $item)
            <option value={{$item->id}}>{{$item->milestone}}</option>
            @endforeach

          </select>
        </div>
        <div class="app-kanban">

          <!-- Add new board -->
          {{-- <div class="row">
            <div class="col-12">
              <form class="kanban-add-new-board">
                <label class="kanban-add-board-btn" for="kanban-add-board-input">
                  <i class="ti ti-plus ti-xs"></i>
                  <span class="align-middle">Add new</span>
                </label>
                <input type="text" class="form-control w-px-250 kanban-add-board-input mb-2 d-none"
                  placeholder="Add Board Title" id="kanban-add-board-input" required />
                <div class="mb-3 kanban-add-board-input d-none">
                  <button class="btn btn-primary btn-sm me-2">Add</button>
                  <button type="button"
                    class="btn btn-label-secondary btn-sm kanban-add-board-cancel-btn">Cancel</button>
                </div>
              </form>
            </div>
          </div> --}}

          <!-- Kanban Wrapper -->
          <div class="kanban-wrapper"></div>

          <!-- Edit Task & Activities -->
          <div class="offcanvas offcanvas-end kanban-update-item-sidebar">
            <div class="offcanvas-header border-bottom">
              <h5 class="offcanvas-title">Edit Task</h5>
              <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
              <ul class="nav nav-tabs tabs-line">
                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-update">
                    <i class="ti ti-edit me-2"></i>
                    <span class="align-middle">Edit</span>
                  </button>
                </li>
                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity">
                    <i class="ti ti-trending-up me-2"></i>
                    <span class="align-middle">Activity</span>
                  </button>
                </li>
              </ul>
              <div class="tab-content px-0 pb-0">
                <!-- Update item/tasks -->
                <div class="tab-pane fade show active" id="tab-update" role="tabpanel">
                  <form>
                    <div class="mb-3">
                      <label class="form-label" for="title">Title</label>
                      <input type="text" id="title" class="form-control" placeholder="Enter Title" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="due-date">Due Date</label>
                      <input type="text" id="due-date" class="form-control" placeholder="Enter Due Date" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="label"> Label</label>
                      <select class="select2 select2-label form-select" id="label">
                        <option data-color="bg-label-success" value="UX">UX</option>
                        <option data-color="bg-label-warning" value="Images">
                          Images
                        </option>
                        <option data-color="bg-label-info" value="Info">Info</option>
                        <option data-color="bg-label-danger" value="Code Review">
                          Code Review
                        </option>
                        <option data-color="bg-label-secondary" value="App">
                          App
                        </option>
                        <option data-color="bg-label-primary" value="Charts & Maps">
                          Charts & Maps
                        </option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Assigned</label>
                      <div class="assigned d-flex flex-wrap"></div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="attachments">Attachments</label>
                      <input type="file" class="form-control" id="attachments" />
                    </div>
                    <div class="mb-4">
                      <label class="form-label">Comment</label>
                      <div class="comment-editor border-bottom-0"></div>
                      <div class="d-flex justify-content-end">
                        <div class="comment-toolbar">
                          <span class="ql-formats me-0">
                            <button class="ql-bold"></button>
                            <button class="ql-italic"></button>
                            <button class="ql-underline"></button>
                            <button class="ql-link"></button>
                            <button class="ql-image"></button>
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="d-flex flex-wrap">
                      <button type="button" class="btn btn-primary me-3" data-bs-dismiss="offcanvas">
                        Update
                      </button>
                      <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas">
                        Delete
                      </button>
                    </div>
                  </form>
                </div>
                <!-- Activities -->
                <div class="tab-pane fade" id="tab-activity" role="tabpanel">
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <span class="avatar-initial bg-label-success rounded-circle">HJ</span>
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Jordan</span> Left the board.
                      </p>
                      <small class="text-muted">Today 11:00 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <img src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Dianna</span> mentioned
                        <span class="text-primary">@bruce</span> in
                        a comment.
                      </p>
                      <small class="text-muted">Today 10:20 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Martian</span> added moved
                        Charts & Maps task to the done board.
                      </p>
                      <small class="text-muted">Today 10:00 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Barry</span> Commented on App
                        review task.
                      </p>
                      <small class="text-muted">Today 8:32 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <span class="avatar-initial bg-label-secondary rounded-circle">BW</span>
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Bruce</span> was assigned
                        task of code review.
                      </p>
                      <small class="text-muted">Today 8:30 PM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <span class="avatar-initial bg-label-danger rounded-circle">CK</span>
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Clark</span> assigned task UX
                        Research to
                        <span class="text-primary">@martian</span>
                      </p>
                      <small class="text-muted">Today 8:00 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <img src="{{ asset('assets/img/avatars/4.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Ray</span> Added moved
                        <span class="fw-semibold">Forms & Tables</span> task
                        from in progress to done.
                      </p>
                      <small class="text-muted">Today 7:45 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Barry</span> Complete all the
                        tasks assigned to him.
                      </p>
                      <small class="text-muted">Today 7:17 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <span class="avatar-initial bg-label-success rounded-circle">HJ</span>
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Jordan</span> added task to
                        update new images.
                      </p>
                      <small class="text-muted">Today 7:00 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <img src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Dianna</span> moved task
                        <span class="fw-semibold">FAQ UX</span> from in
                        progress to done board.
                      </p>
                      <small class="text-muted">Today 7:00 AM</small>
                    </div>
                  </div>
                  <div class="media mb-4 d-flex align-items-start">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <span class="avatar-initial bg-label-danger rounded-circle">CK</span>
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Clark</span> added new board
                        with name <span class="fw-semibold">Done</span>.
                      </p>
                      <small class="text-muted">Yesterday 3:00 PM</small>
                    </div>
                  </div>
                  <div class="media d-flex align-items-center">
                    <div class="avatar me-2 flex-shrink-0 mt-1">
                      <span class="avatar-initial bg-label-secondary rounded-circle">BW</span>
                    </div>
                    <div class="media-body">
                      <p class="mb-0">
                        <span class="fw-semibold">Bruce</span> added new task
                        in progress board.
                      </p>
                      <small class="text-muted">Yesterday 12:00 PM</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-pane fade " id="navs-pills-file" role="tabpanel">
      </div>
      <div class="tab-pane fade " id="navs-pills-financial" role="tabpanel">
      </div>
      <div class="tab-pane fade " id="navs-pills-more" role="tabpanel">
      </div>

    </div>
  </div>
</div>


@include('_partials/_modals/modal-add-milestone')
@endsection