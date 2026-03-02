@extends('layouts/layoutMaster')

@section('title', ' CMD Research Centre - Candidate Registration')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/animate-on-scroll/animate-on-scroll.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')

<script src="{{asset('assets/js/extended-ui-timeline.js')}}"></script>
<script>
  $(document).ready(function() {
    $('.res_category').hide();
     $("#instructions").trigger('click');

     $('#institution').change(function() {
      var institution = $('#institution').val();
      console.log('#discipline option.' + institution);

      // Show only the options in #select3 that match the selected values of #select1 and #select2
      $('#discipline option').hide();
      $('#discipline option.' + institution).show(); // Show only matching options
  });

      $('#discipline, #institution').change(function() {

          var discipline = $('#discipline').val();
          var institution = $('#institution').val();
          console.log('#programme option.' + institution + '-' + discipline);

          // Show only the options in #select3 that match the selected values of #select1 and #select2
          $('#programme option').hide();
          $('#type option').hide(); // Hide all options initially
          $('#programme option.' + institution + '-' + discipline).show(); // Show only matching options
          $('#type option.' + institution).show();
      });
      var counter = 0; // Initialize a counter to give unique ids and names

      // Function to add a new input field
      $('#add-input').click(function(e) {
          e.preventDefault();
           counter++; // Increment counter to ensure unique id and name
          var inputField = `
          <div class="mb-3" id="input-${counter}">
                <label for="name" class="form-label">Educational Qualification</label>
                <input type="text" class="form-control" required id="education-${counter}"  placeholder="Input ${counter}" name="education[]"  autofocus>
                 <button class="remove-input btn mt-2 btn-sm btn-danger" data-id="${counter}">Remove</button>
              </div>
          `;

          // Append the new input field to the container
          $('#input-container').append(inputField);
      });

      // Function to remove a specific input field
      $(document).on('click', '.remove-input', function(e) {
        e.preventDefault();
          var id = $(this).data('id'); // Get the id of the input field to remove
          $('#input-' + id).remove(); // Remove the input field
      });

      $('input[name="reservation"]').on('change', function() {
        if ($('input[name="reservation"]:checked').val() === 'Yes') {
            $('.res_category').show();
            $('input[name="res_category"]').attr('required', true);
        } else {
            $('.res_category').hide();
            $('input[name="res_category"]').attr('required', false);
        }
    });



    $('#submit').on('click', function(e){
      e.preventDefault();
      if($("#reservation").val() == 'Yes'){
        var selectedValue = $('input[name="res_category"]:checked').val();
      }
     else{
      var selectedValue ='Nil'
     }

      var reservation_selectedValue = $('input[name="reservation"]:checked').val();

     var error =0;
      var educations = $("input[name='education[]']").map(function(){ return $(this).val()!='' ? $(this).val() : null; }).get(); // For array inputs


      if (educations.length == 0 ) {
        error= 1;
     }
     else{
      error=0;
     }
     console.log('list'+educations);
     console.log('length'+educations.length);
     console.log('error'+error);

{{--  alert($('input[name="physical_status"]:checked').val());exit;  --}}
        if(error == 0){
          $.ajax({
            url: "/research-submit", // Change to your route
            method: "POST",
            {{--  data: $(this).serialize(), // Serialize the form data including normal and array inputs  --}}
            data:   {
              name : $("#name").val(),
              email : $("#multicol-email").val(),
              mobile : $("#mobile").val(),
              institution : $("#institution").val(),
              discipline : $("#discipline").val(),
              programme : $("#programme").val(),
              type : $("#type").val(),
              education : educations,
              qualification : $("#qualification").val(),
              addl_qualification : $("#addl_qualification").val(),
              reservation : reservation_selectedValue,
              res_category : selectedValue,
              physical_status : $('input[name="physical_status"]:checked').val(),
              pro_qualification : $("#pro_qualification").val(),
              "_token": "{{ csrf_token() }}", //
            },
            success: function(response) {


              Swal.fire({
                icon: 'success',
                title: `Successfully Registerd!`,
                html: response.message+'<br> <a class="btn btn-primary btn-sm text-white mt-2" url="'+response.url+'">Visit '+response.institute+' Registration</a>',
                text: response.message+'<br> <a class="btn btn-primary btn-sm text-white mt-2" url="'+response.url+'">Visit '+response.institute+' Registration</a>',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
              window.location.href = response.url;
            },
            error: function(xhr) {
              let errors = xhr.responseJSON.errors;
                {{--  alert('Error: ' + xhr.responseText);  --}}
                Swal.fire({
                  icon: 'warning',
                  title: `Can't Save Request!`,
                  text: xhr.responseJSON.message,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                })
            }
        });
        }
        else{
          Swal.fire({
            icon: 'warning',
            title: `Can't Save Request!`,
            text: "Please Provide Educational Qualification",
            customClass: {
              confirmButton: 'btn btn-success'
            }
          })
        }

  });


  });
  (function() {


  })
</script>
@endsection

@section('content')



<div class="card text-center mt-3 mb-3">
  <div class="card-body">
    <a href="{{url('/')}}" class=" gap-2 card-title">
      <img height="100" width="100" src="{{ asset('assets/img/branding/cmdlogo.png') }}"></img>
    </a>
    <h5 class="card-title">Welcome to CMD Research Centre</h5>
    <p class="card-text">Recognized Research Centre in Management and Social Sciences</p>
    <button type="button" class="btn btn-primary" id="instructions" data-bs-toggle="modal" data-bs-target="#modalLong">
      Instructions
    </button>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-center ">
      <form class=" border rounded p-3 p-md-5">

        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label">Name of Scholar *</label>
            <input type="text" required class="form-control" id="name" name="name" placeholder="Enter your name"
              autofocus>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="multicol-email">Email *</label>
            <div class="input-group input-group-merge">
              <input type="text" required id="multicol-email" name="multicol-email" class="form-control"
                placeholder="john.doe" aria-label="john.doe" aria-describedby="multicol-email2" />
              <span class="input-group-text" id="multicol-email2">@example.com</span>
            </div>
          </div>
          <div class="col-md-6">
            <label for="name" class="form-label">Mobile Number *</label>
            <input type="text" required class="form-control" id="mobile" name="mobile"
              placeholder="Enter your Mobile Number" autofocus>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="centre">Institution *</label>
            <select class="select2 select-event-label form-select" required id="institution" name="institution">
              <option disabled selected>Select</option>

              {{-- <option data-label="danger" value="Amrita">Amrita Vishwa Vidyapeetham, Amritapuri</option> --}}
              <option data-label="info" value="CHRIST">CHRIST (Deemed to be University), Bangalore</option>
            </select>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="discipline">Discipline Applied *</label>
            <select class="select2 select-event-label form-select" required id="discipline" name="discipline">
              <option disabled selected>Select</option>

              <option class="Amrita CHRIST" data-label="danger" value="Management">Management</option>
              <option class="CHRIST" data-label="info" value="Social-Sciences">Social Sciences</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="programme">Programmes Selected *</label>
            <select class="select2 select-event-label form-select" required id="programme" name="v">
              <option disabled selected>Select</option>

              <option class="CHRIST-Management" data-label="danger" value="Business & Management">Business & Management
              </option>

              <option class="CHRIST-Social-Sciences" data-label="info" value="Sociology & Social Work">Sociology &
                Social Work</option>

              <option class="Amrita-Management" data-label="info" value="Social">Marketing</option>
              <option class="Amrita-Management" data-label="info" value="Finance">Finance</option>
              <option class="Amrita-Management" data-label="info" value="Operations">Operations</option>
              <option class="Amrita-Management" data-label="info" value="Organisational Behaviour & Human Resources">
                Organisational Behaviour & Human Resources</option>
              <option class="Amrita-Management" data-label="info" value="General Management">General Management</option>

              <option class="Amrita-Social-Sciences" data-label="info" value="Social & Behavoural Sciences">Social &
                Behavoural Sciences</option>
              <option class="Amrita-Social-Sciences" data-label="info" value="Social Sciences & Technology">Social
                Sciences & Technology</option>

            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="type">Full-Time/Part-Time *</label>
            <select class="select2 select-event-label form-select" required id="type" name="type">
              <option disabled selected>Select</option>

              <option class="Amrita CHRIST" data-label="danger" value="Part-Time">Part-Time</option>
              {{-- <option class="CHRIST" data-label="danger" value="Full-Time">Full-Time</option> --}}
            </select>
            <div class="form-text text-primary"> (Note: CMD encourages only Part-Time Admission) </div>
          </div>
          <div class="col-md-6" id="input-container">
            <label for="name" class="form-label">Educational Qualification *</label>
            <input type="text" class="form-control" id="education" required name="education[]"
              placeholder="Enter your educational qualification" autofocus>
            <button id="add-input" class=" btn btn-success btn-sm mt-2">Add New Education </button>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="qualification">Are you Qualified for *</label>
            <select class="select2 select-event-label form-select" required id="qualification" name="qualification">
              <option disabled selected>Select</option>

              <option data-label="danger" value="UGC NET">UGC NET</option>
              <option data-label="danger" value="UGC NET/JRF">UGC NET/JRF</option>
              <option data-label="danger" value="UGC CSIR NET">UGC CSIR NET</option>
              <option data-label="danger" value="UGC CSIR NET/JRF">UGC CSIR NET/JRF</option>
              <option data-label="danger" value="SLET">SLET</option>
              <option data-label="danger" value="GATE">GATE</option>
              <option data-label="danger" value="CEED">CEED</option>
              <option value="Nil">Not Applicable</option>
            </select>
          </div>
          <div class="col-md-6">
            <label for="addl_qualification" class="form-label">Other National Level Test, Please Specify</label>
            <input type="text" class="form-control" id="addl_qualification" name="addl_qualification"
              placeholder="Other National Level Test, Please Specify" autofocus>
          </div>
          <div class="col-md-6">
            <label class="form-label d-block"> Do you belong to SC/ST/OBC (Non Creamy layer)/Economically Weaker Section
              (EWS): Yes/No? *</label>
            <div class="form-check form-check-inline mt-3">
              <input class="form-check-input" type="radio" checked name="reservation" id="reservation" value="Yes" />
              <label class="form-check-label" for="inlineRadio1">
                Yes</label>
            </div>

            <div class="form-check form-check-inline mt-3 detailed-radio">
              <input class="form-check-input" type="radio" checked name="reservation" id="reservation" value="No" />
              <label class="form-check-label" for="inlineRadio1">
                No</label>
            </div>
          </div>
          <div class="col-md-6 res_category">
            <label class="form-label d-block">
              Category *</label>
            <div class="form-check form-check-inline mt-3">
              <input class="form-check-input" type="radio" checked name="res_category" id="res_category" value="SC" />
              <label class="form-check-label" for="inlineRadio1">
                SC</label>
            </div>

            <div class="form-check form-check-inline mt-3 detailed-radio">
              <input class="form-check-input" type="radio" name="res_category" id="res_category" value="ST" />
              <label class="form-check-label" for="inlineRadio1">
                ST</label>
            </div>
            <div class="form-check form-check-inline mt-3 detailed-radio">
              <input class="form-check-input" type="radio" name="res_category" id="res_category"
                value="OBC (Non Creamy layer)" />
              <label class="form-check-label" for="inlineRadio1">
                OBC (Non Creamy layer)</label>
            </div>
            <div class="form-check form-check-inline mt-3 detailed-radio">
              <input class="form-check-input" type="radio" name="res_category" id="res_category" value="Economically Weaker Section
              (EWS)" />
              <label class="form-check-label" for="inlineRadio1">
                Economically Weaker Section
                (EWS)</label>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label d-block"> Do you belong to Differentially Abled Yes/No? *</label>
            <div class="form-check form-check-inline mt-3">
              <input class="form-check-input" type="radio" name="physical_status" id="physical_status" value="Yes" />
              <label class="form-check-label" for="inlineRadio1">
                Yes</label>
            </div>

            <div class="form-check form-check-inline mt-3 detailed-radio">
              <input class="form-check-input" type="radio" checked name="physical_status" id="physical_status"
                value="No" />
              <label class="form-check-label" for="inlineRadio1">
                No</label>
            </div>

          </div>
          <div class="col-md-6">
            <label class="form-label" for="pro_qualification">Professional Qualification *</label>
            <select class="select2 select-event-label form-select" required id="pro_qualification"
              name="pro_qualification">
              <option disabled selected>Select</option>

              <option data-label="danger" value="Chartered Accountancy">Chartered Accountancy</option>
              <option data-label="danger" value="Cost Accountancy">Cost Accountancy</option>
              <option data-label="danger" value="Company Secretary ">Company Secretary </option>
              <option value="Nil">Not Applicable</option>


            </select>
          </div>
          <div class="col-md-6">
          </div>
        </div>
        <div class="pt-4">
          <button type="button" id="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
          <button type="reset" class="btn btn-label-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>



<div class="col-lg-4 col-md-3">
  <small class="text-light fw-semibold">Scrolling long content</small>
  <!-- Modal -->
  <div class="modal fade" id="modalLong" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLongTitle">Instructions</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="">
            <div class="card">
              {{-- <h5 class="card-header">Instructions</h5> --}}
              <div class="card-body pb-0">
                <ul class="timeline mt-3 mb-0">
                  <li class="timeline-item timeline-item-primary pb-4 border-left-dashed">
                    <span class="timeline-indicator timeline-indicator-primary">
                      <i class="ti ti-send"></i>
                    </span>
                    <div class="timeline-event">
                      <div class="timeline-header border-bottom mb-3">
                        <h6 class="mb-0">Step 1</h6>
                        <span class="text-muted">01</span>
                      </div>
                      <div class="d-flex justify-content-between flex-wrap mb-2">
                        <div>
                          <span>Applicant has to submit their application form through CMD’s official Portal.</span>

                        </div>
                        <div>
                          <span></span>
                        </div>
                      </div>
                      <a href="javascript:void(0)">
                        <i class="ti ti-link"></i>
                        hrms.cmd.kerala.gov.in/research
                      </a>
                    </div>
                  </li>
                  <li class="timeline-item timeline-item-success pb-4 border-left-dashed">
                    <span class="timeline-indicator timeline-indicator-success">
                      <i class="ti ti-brush"></i>
                    </span>
                    <div class="timeline-event">
                      <div class="timeline-header border-bottom mb-3">
                        <h6 class="mb-0">Step 02</h6>
                        <span class="text-muted">02</span>
                      </div>
                      <div class="d-flex justify-content-between flex-wrap mb-2">
                        <div>
                          <span>Complete “CMD Candidate Registration” on the CMD website.</span>

                        </div>
                        <div>
                          <span></span>
                        </div>
                      </div>

                    </div>
                  </li>
                  <li class="timeline-item timeline-item-danger pb-4 border-left-dashed">
                    <span class="timeline-indicator timeline-indicator-danger">
                      <i class="ti ti-basket"></i>
                    </span>
                    <div class="timeline-event">
                      <div class="timeline-header border-bottom mb-3">
                        <h6 class="mb-0">Step 03</h6>
                        <span class="text-muted">03</span>
                      </div>
                      <div class="d-flex justify-content-between flex-wrap mb-2">
                        <div>
                          <span>Applicant has to submit their Registration through Centre Portal (Amrita Vishwa
                            Vidyapeetham, Amritapuri / CHRIST (Deemed to be University) Bangalore) -</span>

                        </div>
                        <div>
                          <span></span>
                        </div>
                      </div>

                    </div>
                  </li>
                  <li class="timeline-item timeline-item-info pb-4 border-left-dashed">
                    <span class="timeline-indicator timeline-indicator-info">
                      <i class="ti ti-user-circle"></i>
                    </span>
                    <div class="timeline-event">
                      <div class="timeline-header border-bottom mb-3">
                        <h6 class="mb-0">Step 04</h6>
                        <span class="text-muted">04</span>
                      </div>
                      <div class="d-flex justify-content-between flex-wrap mb-2">
                        <div>
                          <span>Complete Registration and apply for the programme</span>

                        </div>
                        <div>
                          <span></span>
                        </div>
                      </div>

                    </div>
                  </li>
                  <li class="timeline-item timeline-item-secondary pb-3 border-0">
                    <span class="timeline-indicator timeline-indicator-secondary">
                      <i class="ti ti-bell"></i>
                    </span>
                    <div class="timeline-event">
                      <div class="timeline-header border-bottom mb-3">
                        <h6 class="mb-0">Step 05</h6>
                        <span class="text-muted">05</span>
                      </div>
                      <div class="d-flex justify-content-between flex-wrap mb-2">
                        <div>
                          <span>Send the soft copy of the application to the email id: research@cmd.kerala.gov.in</span>

                        </div>
                        <div>
                          <span></span>
                        </div>
                      </div>
                      <a href="javascript:void(0)">
                        <i class="ti ti-mail"></i>
                        research@cmd.kerala.gov.in
                      </a>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Proceed</button>
          {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
        </div>
      </div>
    </div>
  </div>
  <!-- Modal -->


</div>

@endsection