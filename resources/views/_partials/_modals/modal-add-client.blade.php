<!-- Add Role Modal -->
<div class="modal addClientModal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-client">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="client-title mb-2">Add New Client</h3>
          <p class="text-muted"></p>
        </div>
        <!-- Add role form -->
        <form id="addClientForm" class="row g-3" onsubmit="return false">
          {{-- {{ csrf_field() }} --}}
          <div class="divider">
            <div class="divider-text">
              Client Details
            </div>
          </div>
          <div class="col-12 mb-4">
            <label class="form-label" for="modalClientName">Client Name</label>
            <input type="text" id="modalClientName" name="modalClientName" class="form-control"
              placeholder="Enter a client name" tabindex="-1" />
          </div>
          <div class="col-12 mb-4">
            <label class="form-label" for="modalClientCode">Client Code</label>
            <input type="text" id="modalClientCode" name="modalClientCode" class="form-control"
              placeholder="Unique 4 digit code" maxlength="4" />
            <small id="codeError" class="text-danger d-none">Code already exists</small>
          </div>
          <div class="col-6 mb-4">
            <label class="form-label" for="modalClientEmail">Client Email</label>
            <input type="text" id="modalClientEmail" name="modalClientEmail" class="form-control"
              placeholder="Enter a client Email" tabindex="-1" />
          </div>
          <div class="col-6 mb-4">
            <label class="form-label" for="modalClientPhone">Client Phone</label>
            <input type="text" id="modalClientPhone" name="modalClientPhone" class="form-control"
              placeholder="Enter a client Phone" tabindex="-1" />
          </div>
          <div class="col-12 mb-4">
            <label class="form-label" for="modalClientAddress">Client Address</label>
            {{-- <input type="text" id="modalClientAddress" name="modalClientAddress" class="form-control"
              placeholder="Enter a client Address" tabindex="-1" /> --}}
            <textarea class="form-control" id="modalClientAddress" name="modalClientAddress" rows="3"></textarea>
          </div>







          <div class="col-12 text-center mt-4">
            <button type="submit" id="submit_client" data-id="0" data-type="new"
              class="btn submit-client btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
              aria-label="Close">Cancel</button>
          </div>
        </form>
        <!--/ Add role form -->
      </div>
    </div>
  </div>
</div>
<!--/ Add Role Modal -->