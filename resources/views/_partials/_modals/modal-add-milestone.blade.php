<!-- Add Role Modal -->
<div class="modal addMilestoneModal fade" id="addMilestoneModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered modal-add-new-client">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="client-title mb-2">Add New Milestone</h3>
          <p class="text-muted"></p>
        </div>
        <!-- Add role form -->
        <form id="addMilestoneForm" class="row g-3" onsubmit="return false">
          {{--  {{ csrf_field() }}  --}}

          <div class="col-12 mb-4">
            <label class="form-label" for="milestone">Milestone</label>
            <input type="text" id="milestone" name="milestone" class="form-control" placeholder="Enter a client name" tabindex="-1" />
          </div>
          <div class="col-12 mb-4">
            <label class="form-label" for="due_date">Due Date</label>
            <input type="text" id="due_date" name="due_date" class="form-control" placeholder="Enter a client Email" tabindex="-1" />
          </div>








          <div class="col-12 text-center mt-4">
            <button type="submit" id="submit_milestone"  data-id="0" data-type="new" class="btn submit_milestone-client btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
        <!--/ Add role form -->
      </div>
    </div>
  </div>
</div>
<!--/ Add Role Modal -->
