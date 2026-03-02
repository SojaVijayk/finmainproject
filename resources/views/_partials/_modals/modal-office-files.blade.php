<!-- Add Designation Modal -->
<div class="modal fade" id="DesignationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="mb-2 designation-title">Create new office file</h3>

        </div>

        <form id="designationForm" class="row" onsubmit="return false">
          <div class="mb-3 col-9">
            <label class="form-label" for="name">File Name / Particulars</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="File Name / Particulars" />
          </div>
          <div class="mb-3 col-3">
            <label class="form-label" for="numbers">Numbers</label>
            <input type="number" class="form-control" id="numbers" name="numbers" placeholder="Numbers" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="description">Description</label>
            <textarea class="form-control" name="description" id="description"></textarea>
          </div>
          <div class="mb-3 col-8">
            <label for="date" class="form-label">Date</label>
            <input type="text" class="form-control datepicker" id="date" name="date" placeholder="DD/MM/YYYY" class="form-control" />

          </div>
          <div class="mb-3 col-4">
            <label class="form-label" for="year">Year</label>
            <input type="number" class="form-control" id="year" name="year" placeholder="Year" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="status">Status</label>
            <select class="select2 select-event-label form-select" id="status" name="status">
              {{--  <option data-label="primary" value="Field" selected>Field</option>  --}}
              <option data-label="danger" value="1">Active</option>
              <option data-label="info" value="2">Closed</option>
            </select>
          </div>







          <div class="col-12 text-center demo-vertical-spacing">
            <button type="submit" id="submit_file" data-id="0" data-type="new" class="btn btn-primary me-sm-3 me-1 submit-file">Save</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Discard</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add Permission Modal -->
