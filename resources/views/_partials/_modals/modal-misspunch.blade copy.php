<!-- Add Designation Modal -->
<div class="modal fade" id="DesignationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="mb-2 designation-title">Request Miss Punch</h3>

        </div>

        <form id="designationForm" class="row" onsubmit="return false">

          <div class="mb-3">
            <label class="form-label" for="eventLabel">Type</label>
            <select class="select2 select-event-label form-select" id="type" name="type">
              <option data-label="primary" value="0" disabled selected>Select</option>
              <option data-label="primary" value="1" >Checkin</option>
              <option data-label="danger" value="2">Checkout</option>
              <option data-label="warning" value="3">Checkin & Checkout</option>

            </select>
          </div>
          <div class="row">
          <div class="mb-3 col-12">
            <label for="date" class="form-label">Date</label>
            <input type="text" class="form-control datepicker" id="date" name="date" placeholder="DD/MM/YYYY" class="form-control" />

          </div>
        </div>
        <div class="row">
          <div class="mb-3 col-6">
            <label for="timepicker-step" class="form-label">Checkin Time</label>
            <input type="text" class="form-control timepicker" id="checkinTime" placeholder="HH:MMam" class="form-control" />
          </div>




          <div class="mb-3 col-6">
            <label for="timepicker-step" class="form-label">Checkout Time</label>
            <input type="text" class="form-control timepicker" id="checkoutTime" placeholder="HH:MMam" class="form-control" />
          </div>
        </div>



          <div class="mb-3">
            <label class="form-label" for="eventDescription">Remark</label>
            <textarea class="form-control" name="description" id="description"></textarea>
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
