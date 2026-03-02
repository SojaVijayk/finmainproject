<!-- Edit User Modal -->
@php
    $genders = DB::table('gender')->where('status',1)->get();
    $country = DB::table('country')->where('status',1)->get();
    $state = DB::table('state')->where('status',1)->get();
    $district = DB::table('district')->where('status',1)->get();
    $prefix = DB::table('prefix')->where('status',1)->get();

@endphp
<div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-edit-user">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Edit User Information</h3>
          <p class="text-muted">Updating user details will receive a privacy audit.</p>
        </div>
        <!-- Vertical Icons Wizard -->
        <div class="col-12 mb-4">
          <small class="text-light fw-semibold">Categories</small>
          <div class="bs-stepper vertical wizard-vertical-icons-example mt-2">
            <div class="bs-stepper-header">
              <div class="step" data-target="#personal-info-vertical">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle">
                    <i class="ti ti-user  "></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">Personal Info</span>
                    <span class="bs-stepper-subtitle">Add personal info</span>
                  </span>
                </button>
              </div>
              <div class="line"></div>
              <div class="step" data-target="#contact-details-vertical">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle">
                    <i class="ti ti-address-book"></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">Contact Details</span>
                    <span class="bs-stepper-subtitle">Add Contact Details</span>
                  </span>
                </button>
              </div>
              <div class="line"></div>
              @role('HR')
              <div class="step" data-target="#account-details-vertical">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle">
                    <i class="ti ti-tie"></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">Employment Details</span>
                    <span class="bs-stepper-subtitle">Add Employment Details</span>
                  </span>
                </button>
              </div>
              <div class="line"></div>

              <div class="step" data-target="#social-links-vertical">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle"><i class="ti ti-credit-card"></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">Account Details</span>
                    <span class="bs-stepper-subtitle">Add Account Details</span>
                  </span>
                </button>
              </div>
              <div class="line"></div>
              @endif

              <div class="step" data-target="#documents">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle"><i class="ti ti-file-description"></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">Documents</span>
                    <span class="bs-stepper-subtitle">Add Documents</span>
                  </span>
                </button>
              </div>
            </div>
            <div class="bs-stepper-content">
              <form onSubmit="return false">


                <!-- Personal Info -->
                <div id="personal-info-vertical" class="content">
                  <div class="content-header mb-3">
                    <h6 class="mb-0">Personal Info</h6>
                    <small>Enter Your Personal Info.</small>
                  </div>
                  <div class="row g-3">
                    <div class="col-sm-6">
                      <label class="form-label" for="prefix">Prefix</label>
                      <select class="select2" name="prefix" id="prefix">
                        <option label=" "></option>
                        <option label=" "></option>
                        @foreach ($prefix as $item )
                        <option value="{{$item->id}}">{{$item->prefix_name}}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-sm-6">
                      <label class="form-label" for="country1">Gender</label>
                      <select class="select2" name="gender" id="gender">
                        <option label=" "></option>
                       @foreach ($genders as $item )
                       <option value="{{$item->id}}">{{$item->gender_name}}</option>
                       @endforeach
                      </select>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="dob">DOB</label>
                      <input type="text" name="dob" id="dob" class="form-control datepicker" placeholder="" />
                    </div>

                    <div class="col-sm-6">
                      <label class="form-label" for="country">Country</label>
                      <select class="select2" name="country" id="country">
                        <option label=" "></option>
                        @foreach ($country as $item )
                        <option value="{{$item->id}}">{{$item->country_name}}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="state">State</label>
                      <select class="select2" name="state" id="state">
                        <option label=" "></option>
                        @foreach ($state as $item )
                       <option value="{{$item->id}}">{{$item->state_name}}</option>
                       @endforeach
                      </select>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="district">District</label>
                      <select class="select2" name="district" id="district">
                        <option label=" "></option>
                        @foreach ($district as $item )
                        <option value="{{$item->id}}">{{$item->district_name}}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="address">Address</label>
                      <textarea class="form-control" name="address" id="address" placeholder="" ></textarea>

                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="pincode">Pincode</label>
                      <input type="text" id="pincode"  name="pincode" class="form-control" placeholder="" />
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="pan">Pan</label>
                      <input type="text" id="pan"  name="pan" class="form-control" placeholder="" />
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="languages">Language</label>
                      <select class="selectpicker w-auto" name="languages" id="languages" data-style="btn-default" data-icon-base="ti" data-tick-icon="ti-check text-white" multiple>
                        <option value='1'>English</option>
                        <option value='2'>French</option>
                        <option value='3'>Spanish</option>
                        <option value='4'>Malayalam</option>
                      </select>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                      {{--  <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                      </button>  --}}
                      <button class="btn btn-primary btn-next"> <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i class="ti ti-arrow-right"></i></button>
                    </div>
                  </div>
                </div>
                <div id="contact-details-vertical" class="content">
                  <div class="content-header mb-3">
                    <h6 class="mb-0">Contact Details</h6>
                    <small>Enter Your Contact Details.</small>
                  </div>
                  <div class="row g-3">

                    <div class="col-sm-6">
                      <label class="form-label" for="mobile_sec">Secondary Mobile</label>
                      <input type="text" id="mobile_sec" name="mobile_sec" class="form-control" placeholder="" />
                    </div>

                    <div class="col-sm-6">
                      <label class="form-label" for="email_sec">Secondary Email</label>
                      <input type="text" id="email_sec" name="email_sec" class="form-control" placeholder="" aria-label="john.doe" />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="twitter">Twitter</label>
                      <input type="text" id="twitter" name="twitter" class="form-control" placeholder="https://twitter.com/abc" />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="facebook">Facebook</label>
                      <input type="text" id="facebook" class="form-control" placeholder="https://facebook.com/abc" />
                    </div>
                    {{--  <div class="col-md-6">
                      <label class="form-label" for="formtabs-google">Google+</label>
                      <input type="text" id="formtabs-google" class="form-control" placeholder="https://plus.google.com/abc" />
                    </div>  --}}
                    <div class="col-md-6">
                      <label class="form-label" for="flinkedin">Linkedin</label>
                      <input type="text" id="linkedin" class="form-control" placeholder="https://linkedin.com/abc" />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="instagram">Instagram</label>
                      <input type="text" id="instagram" class="form-control" placeholder="https://instagram.com/abc" />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="whatsapp">Whatsapp</label>
                      <input type="text" id="whatsapp" class="form-control" placeholder="https://quora.com/abc" />
                    </div>

                    <div class="col-12 d-flex justify-content-between">
                      <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                      </button>
                      <button class="btn btn-primary btn-next"> <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i class="ti ti-arrow-right"></i></button>
                    </div>
                  </div>
                </div>
                @role('HR')
                <!-- Account Details -->
                <div id="account-details-vertical" class="content">
                  <div class="content-header mb-3">
                    <h6 class="mb-0">Employment Details</h6>
                    <small>Enter Your Employment Details.</small>
                  </div>
                  <div class="row g-3">


                    <div class="col-sm-6">
                      <label class="form-label" for="contract_start_date">Contract Start Date</label>
                      <input type="text" id="contract_start_date" class="form-control datepicker" placeholder="" />
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="contract_duration">Contract Duration</label>
                      <input type="text" id="contract_duration" class="form-control" placeholder="" />
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="contract_end_date">Contract End Date</label>
                      <input type="text" id="contract_end_date" class="form-control datepicker" placeholder="" />
                    </div>

                    <div class="col-12 d-flex justify-content-between">
                      <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                      </button>
                      <button class="btn btn-primary btn-next"> <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i class="ti ti-arrow-right"></i></button>
                    </div>
                  </div>
                </div>

                <!-- Social Links -->
                <div id="social-links-vertical" class="content">
                  <div class="content-header mb-3">
                    <h6 class="mb-0">Bank Account Details</h6>
                    <small>Enter Your Bank Account Details.</small>
                  </div>
                  <div class="row g-3">
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
                    <div class="col-12 d-flex justify-content-between">
                      <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                      </button>
                      <button class="btn btn-primary btn-next"> <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i class="ti ti-arrow-right"></i></button>
                    </div>
                  </div>
                </div>
                @endif
                <div id="documents" class="content">
                  <div class="content-header mb-3">
                    <h6 class="mb-0">Documents</h6>
                    <small>Upload Documents.</small>
                  </div>
                  <div class="row g-3">
                    <div class="col-sm-6">
                      <label class="form-label" for="doc_name">Document Name</label>
                      <input type="text" id="doc_name" class="form-control" placeholder="" />
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="doc_number">Document Number</label>
                      <input type="text" id="doc_number" class="form-control" placeholder="" />
                    </div>
                    <div class="card-body">

                        <input type="file" name="file" class="form-control">

                    </div>
                    <div class="col-12 d-flex justify-content-between">
                      <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                      </button>
                      <button id="updateInfo" class="btn btn-primary btn-submit">Submit</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Edit User Modal -->
