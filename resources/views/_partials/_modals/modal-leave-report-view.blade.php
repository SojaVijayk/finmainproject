<!-- Add Designation Modal -->
<div class="modal fade" id="LeaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="mb-2 designation-title">Leave Report</h3>
          {{--  <p class="text-muted">Designation you may use and assign to your users.</p>  --}}
        </div>
        {{--  <div class="alert alert-warning" role="alert">
          <h6 class="alert-heading mb-2">Warning</h6>
          <p class="mb-0">Added Designation name can't be delete or edit, you might break the system  functionality. Please ensure you're absolutely certain before proceeding.</p>
        </div>  --}}


        <div class="card">

          <div class=" table-responsive">
            <table class="datatables-leave-list table border-top">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Leave Type</th>
                  <th>Date</th>
                  <th>Leave Day Type</th>
                  <th>Requested at</th>
                  <th>Status</th>
                  <th>Action By</th>
                  <th>Action at</th>

                </tr>
              </thead>
              <tbody id="dataList"></tbody>
            </table>
          </div>
        </div>


      </div>
    </div>
  </div>
</div>
<!--/ Add Permission Modal -->
