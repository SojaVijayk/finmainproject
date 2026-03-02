<!-- Add Designation Modal -->
<div class="modal fade" id="leaveActionModal" tabindex="-1" data-bs-focus="false" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="mb-2 designation-title">Leave Approve/ Reject</h3>

        </div>


        <div class="row col-xl-12 mb-4">
          <div class="alert alert-warning alert-dismissible d-flex align-items-baseline" role="alert">
            <span class="alert-icon alert-icon-lg text-primary me-2">
              <i class="ti ti-calendar ti-sm"></i>
            </span>
            <div class="d-flex flex-column ps-1">

              <p class="mb-0"> Leave Statistics for the period of <span class="leave-start"></span> TO <span class="leave-end"></span></p>
              <h5 class="alert-heading mb-2"></h5>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
              </button>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                  <div class="content-left">
                    <span class="leave-type-name">Casual Leave</span>
                    <div class="d-flex align-items-center my-1">
                      <h4 class="mb-0 me-2 leave-total-credit">Not found</h4>

                    </div>
                    <span>Total Credit</span>
                  </div>
                  <span class="badge bg-label-primary rounded p-2">
                    <i class="ti ti-user ti-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                  <div class="content-left">
                    <span class="leave-type-name">Casual Leave</span>
                    <div class="d-flex align-items-center my-1">
                      <h4 class="mb-0 me-2 leave-total-availed">Not found</h4>

                    </div>
                    <span>Total Availed</span>
                  </div>
                  <span class="badge bg-label-primary rounded p-2">
                    <i class="ti ti-user ti-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                  <div class="content-left">
                    <span class="leave-type-name">Casual Leave</span>
                    <div class="d-flex align-items-center my-1">
                      <h4 class="mb-0 me-2 leave-total-requested">Not found</h4>

                    </div>
                    <span>Total Requested</span>
                  </div>
                  <span class="badge bg-label-primary rounded p-2">
                    <i class="ti ti-user ti-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                  <div class="content-left">
                    <span class="leave-type-name">Casual Leave</span>
                    <div class="d-flex align-items-center my-1">
                      <h4 class="mb-0 me-2 leave-total-balance">Not found</h4>

                    </div>
                    <span>Total Balance</span>
                  </div>
                  <span class="badge bg-label-primary rounded p-2">
                    <i class="ti ti-user ti-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

          <div class="card">
            <div class=" table-responsive">
              <table class="datatables-leave-list table border-top">
                <thead>
                  <tr>

                    <th>Leave Type</th>
                    <th>Date</th>
                    <th>Leave Day Type</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="dataList"></tbody>
              </table>
            </div>
          </div>


          <div class="col-12 text-center demo-vertical-spacing">

            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Discard</button>
          </div>

      </div>
    </div>
  </div>
</div>
<!--/ Add Permission Modal -->
