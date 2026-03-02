<!-- Add Designation Modal -->
<div class="modal fade " id="viewReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          {{--  <h3 class="mb-2 designation-title">Movement Status Report</h3>  --}}

        </div>
          <div class="card text-center">
  <div class="card-header">
    Movement Status Report
  </div>
  <div class="card-body">
    {{--  <h5 class="card-title">Special title treatment</h5>  --}}
    <p class="card-text " id="eventReportData">.</p>

  </div>
  <div class="card-footer text-body-secondary">
   Updated At :  <span id="eventReport_updated_at"></span>
  </div>
</div>
           {{--  <div class="mb-3 statusReport">
            <label class="form-label" for="statusReport">Report</label>
            <textarea rows="10" class="form-control" name="eventReportData" id="eventReportData"></textarea>
          </div>  --}}

      </div>
    </div>
  </div>
</div>
<!--/ Add Permission Modal -->
