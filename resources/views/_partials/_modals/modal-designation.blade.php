<!-- Add Designation Modal -->
<div class="modal fade" id="DesignationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="mb-2 designation-title">Add New Designation</h3>
          <p class="text-muted">Designation you may use and assign to your users.</p>
        </div>
        <div class="alert alert-warning" role="alert">
          <h6 class="alert-heading mb-2">Warning</h6>
          <p class="mb-0">Added Designation name can't be delete or edit, you might break the system  functionality. Please ensure you're absolutely certain before proceeding.</p>
        </div>
        <form id="designationForm" class="row" onsubmit="return false">
          <div class="col-12 mb-3">
            <label class="form-label" for="employment_type">Employment Type</label>
            <select id="employment_type" name="employment_type" class=" form-select">
              <option disabled value="">Select</option>
              @foreach ($employment_types as $employment_type)
              <option value={{$employment_type->id}}> {{$employment_type->employment_type}}</option>
              @endforeach


            </select>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label" for="modalDesignationName">Designation Name</label>
            <input type="text" id="modalDesignationName" name="modalDesignationName" class="form-control" placeholder="Designation Name" autofocus />
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
