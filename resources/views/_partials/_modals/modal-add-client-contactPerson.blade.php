<!-- Add Role Modal -->
<div class="modal addContactPersonModal fade" id="addContactPersonModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-contactPerson">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="client-title mb-2">Add New Contact Person</h3>
          <p class="text-muted"></p>
        </div>
        <!-- Add role form -->
        <form id="addContactPersonForm" class="row g-3" onsubmit="return false">
          {{--  {{ csrf_field() }}  --}}




          <div class="divider contactperson">
            <div class="divider-text">
               Contact Person Details
            </div>
          </div>
            <div data-repeater-list="group-a">
              <div data-repeater-item>
                <div class="row contactperson">
                  <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
                    <label class="form-label" for="form-repeater-1-1">Name</label>
                    <input type="text" id="contactName" name="contactName" class="form-control" placeholder="" />
                  </div>
                  <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
                    <label class="form-label" for="contactDesignation">Designation</label>
                    <input type="text" id="contactDesignation" name="contactDesignation" class="form-control" placeholder="" />
                  </div>

                  <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
                    <label class="form-label" for="contactEmail">Email</label>
                    <input type="text" id="contactEmail" name="contactEmail" class="form-control" placeholder="" />
                  </div>
                  <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
                    <label class="form-label" for="contactMobile">Mobile</label>
                    <input type="text" id="contactMobile" name="contactMobile" class="form-control" placeholder="" />
                  </div>
                  <div class="mb-12 col-lg-12 col-xl-12 col-12 mb-0">
                    <label class="form-label" for="contactAddress">Address</label>
                    <textarea class="form-control" id="contactAddress" name="contactAddress" rows="3"></textarea>
                  </div>

                </div>
                <hr>
              </div>
            </div>




          <div class="col-12 text-center mt-4">
            <button type="submit" id="submit_contact_person" data-client="0" data-id="0" data-type="new" class="btn submit-contact_person btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
        <!--/ Add role form -->
      </div>
    </div>
  </div>
</div>
<!--/ Add Role Modal -->
