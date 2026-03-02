<!-- Add Designation Modal -->
<div class="modal fade" id="DesignationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog  modal-lg modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="mb-2 designation-title">Request Leave</h3>

          <div class="alert alert-warning message" role="alert">
            <h6 class="alert-heading mb-2">Warning</h6>
            <p class="mb-0">You have no credit and cannot request <span class="leaveTypeName"></span>. Kindly select a
              different leave type</p>
          </div>

          <div class="col-xl-12 p-4 availability">
            <small class="text-light fw-semibold">Leave Credit Details</small>
            <div class="demo-vertical-spacing">
              <button type="button" class="btn btn-outline-primary">
                Total
                <span class="badge bg-label-primary badge-center ms-1 total-leave">0</span>
              </button>
              <button type="button" class="btn btn-outline-primary">
                Availed
                <span class="badge rounded-pill bg-label-success badge-center ms-1 availed-leave">0</span>
              </button>
              <button type="button" class="btn btn-outline-primary">
                Requested
                <span class="badge rounded-pill bg-label-warning badge-center ms-1 requested-leave">0</span>
              </button>
              <button type="button" class="btn btn-outline-primary">
                Balance
                <span class="badge rounded-pill bg-label-danger badge-center ms-1 balance-leave">0</span>
              </button>
            </div>
          </div>

        </div>

        <form id="designationForm" class="row" onsubmit="return false">
          <div class="mb-3">
            <label class="form-label" for="leaveType">Leave Type *</label>
            <select class="select2 select-event-label form-select" id="leaveType" name="leaveType" required>
              <option value="">Select</option>
              @foreach ($leaves_total_credit as $leave)
              <option value={{$leave->leave_type_id}} >{{$leave->leave_type}}</option>

              @endforeach
              {{-- <option value="4">Compensatory Off</option>--}}
              <option value="5">Leave Without Allowance (Loss of Pay)</option>

            </select>
          </div>
          {{-- <div class="mb-3">
            <label for="bs-datepicker-multidate" class="form-label">Multidate</label>
            <input type="text" id="bs-datepicker-multidate" placeholder="MM/DD/YYYY, MM/DD/YYYY" class="form-control" />
          </div> --}}

          <div class="mb-3 col-6 date-group">
            <label for="fromDate" class="form-label">From *</label>
            <input type="text" class="form-control datepicker" id="fromDate" name="fromDate" placeholder="DD/MM/YYYY"
              class="form-control" required />

          </div>




          <div class="mb-3 col-6 date-group">
            <label for="toDate" class="form-label">To *</label>
            <input type="text" class="form-control datepicker" id="toDate" name="toDate" placeholder="DD/MM/YYYY"
              class="form-control" required />

          </div>
          <div class="mb-3 col-12">
            <input type="hidden" value="" id="dateListCount" />
            <ul id="dateList"></ul>
          </div>

          <div id="dutyAssignmentContainer">
            <div id="floatingInputHelp" class="form-text  text-warning"> You may leave duty assignments blank if not
              applicable. If filled, at least one duty assigned user & responsibility description are
              required..</div>
            <label class="form-label">Duty Assigned To </label>

            <div class="duty-assignment-entry mb-2 row">
              <div class="col-md-5">
                <select name="duty_assignments[0][user_id]" class="form-select">
                  <option value="">Select User </option>
                  @foreach($users as $user)
                  <option value="{{ $user->id }}">{{ $user->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-5">
                <input type="text" name="duty_assignments[0][description]" class="form-control"
                  placeholder="Responsibility ">
              </div>

              <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-assignment">Remove</button>
              </div>
            </div>
          </div>

          <button type="button" id="addDutyAssignment" class="btn btn-secondary mb-3">Add More Duty</button>



          <div class="mb-3 remark-input">
            <label class="form-label" for="eventDescription">Remark</label>
            <textarea class="form-control" name="eventDescription" id="eventDescription"></textarea>
          </div>

          <div class="col-12 text-center demo-vertical-spacing">
            <button type="submit" id="submit_designation" data-id="0" data-type="new"
              class="btn btn-primary me-sm-3 me-1 submit-designation">Save</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
              aria-label="Close">Discard</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add Permission Modal -->