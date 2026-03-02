@extends('layouts/layoutMaster')

@section('title', 'Blank layout - Layouts')
@section('page-script')
<script src="{{asset('assets/js/pages-auth.js')}}"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>


@endsection

@php
$user = Auth::user();
use App\Models\PMS\Requirement;
use App\Models\PMS\Proposal;
use App\Models\Tapal;
@endphp
@section('content')

{{-- <lottie-player style="align-items: center; width: 800px; height: 400px;" src='{{config(' variables.login3')}}'
  background="transparent" speed="1" style="width: 800px; height: 400px;" loop autoplay></lottie-player> --}}
<div class="row">


  <div class="col-lg-12 mb-4">

    <!-- NOIFICATN -->
    @can('leave-request-approve')
    <h5 class="pb-1 mb-4">Notification</h5>
    <div class="row mb-5">

      <div class="col-md">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Leave Request</span>
                <div class="card-header-elements">
                  <span
                    class="badge rounded-pill {{$pending_leave > 0 ? 'bg-danger' : 'bg-secondary'}}">{{$pending_leave}}</span>
                </div>

              </div>
              <div class="card-body">
                <a href="/leave/approve-list" class="btn btn-primary">Take Action</a>
                <p class="card-text pt-4"><small class="text-muted">Last updated 1 mins ago</small></p>
              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/calendar-lottie.json')}}" background="transparent"
                speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>

          </div>
        </div>
        <div class="col-md">
          <div class="card mb-3">
            <div class="row g-0">
              <div class="col-md-8">
                <div class="card-header header-elements">
                  <span class=" me-2">Leave Cencel Request</span>
                  <div class="card-header-elements">
                    <span
                      class="badge  rounded-pill {{$pending_leave_cancel_ro > 0 ? 'bg-danger' : 'bg-secondary'}}">{{$pending_leave_cancel_ro}}</span>

                  </div>

                </div>
                <div class="card-body">
                  <a href="/leave/ro/cancel-requests" class="btn btn-primary">Take Action</a>
                  <p class="card-text pt-4"><small class="text-muted">Last updated 1 mins ago</small></p>
                </div>
              </div>
              <div class="col-md-4">
                <dotlottie-player style=" " src="{{asset('assets/json/calendar-lottie.json')}}" background="transparent"
                  speed="1" style="width: 150px; height: 150px;" loop autoplay>
                </dotlottie-player>
              </div>

            </div>
          </div>
        </div>


      </div>
      <div class="col-md">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Movement Request</span>
                <div class="card-header-elements">
                  <span
                    class="badge  rounded-pill {{$pending_movement > 0 ? 'bg-danger' : 'bg-secondary'}}">{{$pending_movement}}</span>
                </div>
              </div>
              <div class="card-body">
                <a href="/movement/approve-list" class="btn btn-primary">Take Action</a>
                <p class="card-text pt-4"><small class="text-muted">Last updated 1 mins ago</small></p>
              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/movement-lottie.json')}}" background="transparent"
                speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Miss Punch Request</span>
                <div class="card-header-elements">
                  <span
                    class="badge  rounded-pill {{$pending_misspunch > 0 ? 'bg-danger' : 'bg-secondary'}}">{{$pending_misspunch}}</span>
                </div>
              </div>
              <div class="card-body">
                <a href="/misspunch/approve-list" class="btn btn-primary">Take Action</a>
                <p class="card-text pt-4"><small class="text-muted">Last updated 1 mins ago</small></p>
              </div>
            </div>
            <div class="col-md-4 p-2">
              <dotlottie-player style=" " src="{{asset('assets/json/fingerprint-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>


    </div>
    <div class="row mb-5">
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Movement Status Repot</span>
                <div class="card-header-elements">
                  <span class="badge  rounded-pill {{$report > 0 ? 'bg-danger' : 'bg-secondary'}}">{{$report}}</span>
                </div>
              </div>
              <div class="card-body">
                <a href="/movement/approve-list?report=1" class="btn btn-primary">View</a>
                <p class="card-text pt-4"><small class="text-muted">Last updated 1 mins ago</small></p>
              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/movement-lottie.json')}}" background="transparent"
                speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Tapals</span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}

                  @php
                  $tapal_count=Tapal::whereHas('movements', function($query) use ($user) {
                  $query->where('to_user_id', $user->id)
                  ->where(function($q) {
                  $q->where('status', 'like', '%Pending%')
                  ->orWhere('status', 'like', '%Accepted%');
                  });
                  })->count();
                  @endphp


                  @if($tapal_count > 0 ) <span
                    class="badge  rounded-pill {{ $tapal_count  > 0 ? 'bg-danger' : 'bg-secondary'}}">
                    -{{ $tapal_count}} </span>@endif
                </div>
              </div>
              <div class="card-body">
                <a href="/tapals" class="btn btn-primary">Start</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/contact-mail-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>




      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Project Management BETA</span>
                <div class="card-header-elements">
                  @if($user->hasRole('director'))
                  @php
                  $requirement = Requirement::whereIn('status', [
                  Requirement::STATUS_SENT_TO_DIRECTOR,
                  Requirement::STATUS_SENT_TO_PAC
                  ])->count();
                  $proposlas = Proposal::where('status', Proposal::STATUS_SENT_TO_DIRECTOR)->count();
                  @endphp


                  @if($requirement > 0 ) <span
                    class="badge  rounded-pill {{ $requirement  > 0 ? 'bg-danger' : 'bg-secondary'}}">Requirements
                    -{{ $requirement}} </span>@endif
                  @if($proposlas > 0 ) <span
                    class="badge mt-2 rounded-pill {{  $proposlas > 0 ? 'bg-danger' : 'bg-secondary'}}">
                    Proposals -{{ $proposlas}}</span>@endif
                  @endif
                </div>
              </div>
              <div class="card-body">
                <a href="/pms/dashboard" class="btn btn-primary">Start</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/working-project-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>
      @if($user->hasRole('director'))
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Project Dashboard BETA</span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="/pms/reports/project-status-detailed" class="btn btn-primary">View</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/working-project-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>
      @endif

      @if($user->hasRole(['director', 'finance']))
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Bank Management </span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="/pms/finance/bank-dashboard" class="btn btn-primary">View</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/saving-money-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Project Master List</span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="{{ route('pms.requirements.master-list') }}" class="btn btn-primary">View</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/working-project-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>
      @endif


      @if($user->hasRole('finance'))
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Invoice Management </span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="/pms/finance/dashboard" class="btn btn-primary">View</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/invoice-lottie.json')}}" background="transparent"
                speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Expense Management </span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="/pms/expenses" class="btn btn-primary">View</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/expense-lottie.json')}}" background="transparent"
                speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>

      @endif


    </div>
    <!--/ Notification -->
    @endcan
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Hall Availability</span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="/availability/calendar" class="btn btn-primary">View</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/calendar-booking-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>

            </div>
          </div>
        </div>
      </div>
      @cannot('leave-request-approve')
      @if($user->hasRole('HR'))
      <div class="col-md">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Leave Cencel Request</span>
                <div class="card-header-elements">
                  <span
                    class="badge  rounded-pill {{$pending_leave_cancel_hr > 0 ? 'bg-danger' : 'bg-secondary'}}">{{$pending_leave_cancel_hr}}</span>
                </div>

              </div>
              <div class="card-body">
                <a href="/leave/hr/cancel-requests" class="btn btn-primary">Take Action</a>
                <p class="card-text pt-4"><small class="text-muted">Last updated 1 mins ago</small></p>
              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/calendar-lottie.json')}}" background="transparent"
                speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>

          </div>
        </div>
      </div>
      @endif

      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Tapals</span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}

                  @php
                  $tapal_count=Tapal::whereHas('movements', function($query) use ($user) {
                  $query->where('to_user_id', $user->id)
                  ->where(function($q) {
                  $q->where('status', 'like', '%Pending%')
                  ->orWhere('status', 'like', '%Accepted%');
                  });
                  })->count();
                  @endphp


                  @if($tapal_count > 0 ) <span
                    class="badge  rounded-pill {{ $tapal_count  > 0 ? 'bg-danger' : 'bg-secondary'}}">
                    -{{ $tapal_count}} </span>@endif
                </div>
              </div>
              <div class="card-body">
                <a href="/tapals" class="btn btn-primary">Start</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/contact-mail-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Project Management BETA</span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="/pms/dashboard" class="btn btn-primary">Start</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/working-project-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>


      @if($user->hasRole('finance'))




      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Invoice Management </span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="/pms/finance/dashboard" class="btn btn-primary">View</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/invoice-lottie.json')}}" background="transparent"
                speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-8">
              <div class="card-header header-elements">
                <span class=" me-2">Expense Management </span>
                <div class="card-header-elements">
                  {{-- <span class="badge "></span> --}}
                </div>
              </div>
              <div class="card-body">
                <a href="/pms/expenses" class="btn btn-primary">View</a>

              </div>
            </div>
            <div class="col-md-4">
              <dotlottie-player style=" " src="{{asset('assets/json/expense-lottie.json')}}" background="transparent"
                speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
          </div>
        </div>
      </div>

      @endif

      @endcannot
    </div>


    <!-- NEWS -->
    <h5 class="pb-1 mb-3">News & Updates</h5>
    <div class="row mb-2">
      <div class="col-md">
        <div class="card mb-1">
          <div class="row g-0">
            <div class="col-md-1">
              <dotlottie-player style=" " src="{{asset('assets/json/news-updates-lottie.json')}}"
                background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
              </dotlottie-player>
            </div>
            <div class="col-md-8">
              <div class="card-body">
                {{-- <h5 class="card-title">Card title</h5> --}}
                <p class="card-text">
                  {{-- No Data Available --}}

                </p>
                <p class="card-text"><small class="text-muted time">Last updated 1 mins ago</small></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--/ NEWS -->

    <dotlottie-player style=" " src="{{asset('assets/json/dashboard-lottie.json')}}" background="transparent" speed="1"
      style="width: 300px; height: 300px;" loop autoplay></dotlottie-player>

  </div>
</div>


@endsection