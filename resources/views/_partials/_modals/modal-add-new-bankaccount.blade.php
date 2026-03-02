<!-- Add New Credit Card Modal -->
<div class="modal fade" id="addNewBankAccount" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Add New Account</h3>
          <p class="text-muted">Add new Account to complete payment</p>
        </div>
        <form id="addNewBankAccountForm" class="row g-3" onsubmit="return false">

            <div class="col-sm-6 form-password-toggle">
              <label class="form-label" for="account_number">Account Number</label>
              <div class="input-group input-group-merge">
                <input type="password" id="account_number" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password2" />
                <span class="input-group-text cursor-pointer" id="account_number"><i class="ti ti-eye-off"></i></span>
              </div>
            </div>
            <div class="col-sm-6 form-password-toggle">
              <label class="form-label" for="account_number_confirm">Confirm Account Number</label>
              <div class="input-group input-group-merge">
                <input type="password" id="account_number_confirm" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="confirm-password2" />
                <span class="input-group-text cursor-pointer" id="account_number_confirm"><i class="ti ti-eye-off"></i></span>
              </div>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="account_holder_name">Account Holder Name</label>
              <input type="text" id="account_holder_name" class="form-control" placeholder="" />
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="ifsc">IFSC</label>
              <input type="text" id="ifsc" class="form-control" placeholder="" />
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="bank_name">Bank Name</label>
              <input type="text" id="bank_name" class="form-control" placeholder="" />
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="branch">Branch</label>
              <input type="text" id="branch" class="form-control" placeholder="" />
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="branch">Branch</label>
             <textarea class="form-control" id="bank_address"></textarea>
            </div>


          <div class="col-12">
            <label class="switch">
              <input type="checkbox" class="switch-input">
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="switch-label">Save card for future billing?</span>
            </label>
          </div>
          <div class="col-12 text-center">
            <button type="submit" id="submit-bank-account" data-id="" class="btn btn-primary me-sm-3 me-1 ">Submit</button>
            <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Credit Card Modal -->
