<!-- Add Designation Modal -->
<div class="modal fade" id="submitReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="mb-2 designation-title">Movement Status Report</h3>

        </div>

        <form id="designationForm" class="row" onsubmit="return false">





           <div class="mb-3 statusReport">
            <label class="form-label" for="statusReport">Report</label>
            <textarea rows="10" class="form-control" name="statusReport" id="statusReport"></textarea>
          </div>

          <div class="col-12 text-center demo-vertical-spacing buttons">
            <button type="submit" id="submit_report" data-id="0" data-type="new" class="btn btn-primary me-sm-3 me-1 submit-report">Save</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Discard</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add Permission Modal -->
