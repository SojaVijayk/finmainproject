<!-- Add Designation Modal -->
<div class="modal fade" id="DesignationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="mb-2 designation-title">Request Movement</h3>

        </div>

        <form id="designationForm" class="row" onsubmit="return false">
          <div class="mb-3">
            <label class="form-label" for="eventTitle">Purpose</label>
            <input type="text" class="form-control" id="eventTitle" name="eventTitle" placeholder="Purpose" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="eventLabel">Movement Type</label>
            <select class="select2 select-event-label form-select" id="eventLabel" name="eventLabel">
              {{--  <option data-label="primary" value="Field" selected>Field</option>  --}}
              {{--  <option data-label="danger" value="Personal">Personal</option>  --}}
              <option data-label="danger" value="Compensatory off">Compensatory off</option>
              {{--  <option data-label="warning" value="Meeting">Meeting</option>
              <option data-label="success" value="Training">Training</option>  --}}
              <option data-label="info" value="Official" selected>Official</option>
            </select>
          </div>

           <div class="mb-3">
            <label class="form-label" for="referenceType">Reference Type (Optional)</label>
            <select class="select2 form-select" id="referenceType" name="referenceType">
              <option value="">Select Reference Type</option>
              <option value="Requirement">Requirement</option>
              <option value="Proposal">Proposal</option>
              <option value="Project">Project</option>
            </select>
          </div>
          <div class="mb-3" id="referenceIdDiv" style="display:none;">
              <label class="form-label" for="referenceId">Reference Item</label>
              <select class="select2 form-select" id="referenceId" name="referenceId">
                  <option value="">Select Item</option>
              </select>
          </div>
          <div class="row">
          <div class="mb-3 col-6">
            <label for="fromDate" class="form-label">From</label>
            <input type="text" class="form-control datepicker" id="fromDate" name="fromDate" placeholder="DD/MM/YYYY" class="form-control" />

          </div>
          <div class="mb-3 col-6">
            <label for="timepicker-step" class="form-label">Time</label>
            <input type="text" class="form-control timepicker" id="fromTime" placeholder="HH:MMam" class="form-control" />
          </div>
        </div>

        <div class="row">
          <div class="mb-3 col-6">
            <label for="toDate" class="form-label">To</label>
            <input type="text" class="form-control datepicker" id="toDate" name="toDate" placeholder="DD/MM/YYYY" class="form-control" />

          </div>
          <div class="mb-3 col-6">
            <label for="timepicker-step" class="form-label">Time</label>
            <input type="text" class="form-control timepicker" id="toTime" placeholder="HH:MMam" class="form-control" />
          </div>
        </div>


          <div class="mb-3">
            <label class="form-label" for="eventLocation">Location</label>
            <input type="text" class="form-control" id="eventLocation" name="eventLocation" placeholder="Enter Location" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="eventDescription">Description</label>
            <textarea class="form-control" name="eventDescription" id="eventDescription"></textarea>
          </div>


           <div class="mb-3 eventReport">
            <label class="form-label" for="eventReport">Report (Outcome of Meeting/Movement)</label>
            <textarea class="form-control" name="eventReport" id="eventReport" placeholder="You can submit the report here, or save the request and submit the report later using the 'Submit Report' option."></textarea>
          </div>

          <div class="col-12 text-center demo-vertical-spacing">
            <button type="submit" id="submit_designation" data-id="0" data-type="new" class="btn btn-primary me-sm-3 me-1 submit-designation">Save</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Discard</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add Permission Modal -->
