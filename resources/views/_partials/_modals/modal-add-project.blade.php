<!-- Add Project Modal -->
<div class="modal addProjectModal fade" id="addProjectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-project">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="project-title mb-2">Add New Project</h3>

          <div class="divider contactperson">
            <div class="divider-text">
               Project Basic Information
            </div>
          </div>
        </div>
        <!-- Add project form -->
        <form id="addProjectForm" class="row g-3" onsubmit="return false">
          {{--  {{ csrf_field() }}  --}}
          <div class="col-12 mb-4">
            <label class="form-label" for="modalProjectName">Project Name</label>
            <input type="text" id="modalProjectName" name="modalProjectName" class="form-control" placeholder="Enter a project name" tabindex="-1" />
          </div>
          <div class="col-6 mb-4">
            <label class="form-label" for="modalProjectType">Project Type</label>
            {{--  <input type="text" id="modalProjectType" name="modalProjectType" class="form-control" placeholder="Enter a project type" tabindex="-1" />  --}}

              {{--  <label for="select2Multiple" class="form-label">Multiple</label>  --}}
              <select  id="modalProjectType" name="modalProjectType" class="select2 form-select" multiple>
                @foreach ($projectTypes as $projectType)
                  <option value={{$projectType->id}} >{{$projectType->type_name}}</option>
                  @endforeach
              </select>

          </div>
          <div class="col-6 mb-4">
            <label class="form-label" for="projectcost">Project Cost</label>
            <input type="text" id="projectcost" name="projectcost" class="form-control" placeholder="" tabindex="-1" />
          </div>
          <div class="col-8">
            {{--  <h5>Project Clients </h5>  --}}
            <div class="col-md">
              <small class="text-light fw-medium d-block">Project Clients/Sponsering Agency</small>
              @foreach ($clients as $client)
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input client-checkbox projectClient" name="projectClient" type="checkbox" data-id={{$client->id}} value={{$client->id}} type="checkbox" id={{$client->id}} />
                <label class="form-check-label" for="inlineCheckbox1">{{$client->client_name}}</label>
              </div>
              @endforeach

            </div>
         {{--  <div class="table-responsive">
              <table class="table table-flush-spacing">
                <tbody>

                  @foreach ($clients as $client)
                  <tr>
                    <td class="text-nowrap  fw-semibold">{{$client->client_name}}</td>
                    <td>
                      <div class="d-flex">
                        <div class="form-check me-3 me-lg-5">
                          <input class="form-check-input client-checkbox" data-id={{$client->id}} value={{$client->id}} type="checkbox" id={{$client->id}} />
                          <label class="form-check-label" for="userManagementRead">

                          </label>
                        </div>


                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>  --}}

          </div>

          <div class="col-4 mb-4">
            <label class="form-label" for="contactperson">Client Contact Person</label>
              <select  id="contactperson" name="contactperson" class="select2 form-select" multiple>

              </select>

          </div>


          <div class="col-12 mb-4">
            <label class="form-label" for="modalProjectDescription">Project Description</label>
            {{--  <input type="text" id="modalProjectDescription" name="modalProjectDescription" class="form-control" placeholder="Enter a project description" tabindex="-1" />  --}}
            <textarea class="form-control" id="modalProjectDescription" name="modalProjectDescription" rows="3"></textarea>
          </div>

          <div class="divider contactperson">
            <div class="divider-text">
              Project Team Details
            </div>
          </div>
          <div class="col-4 mb-4">
            <label class="form-label" for="leads">Project Leads</label>
              <select  id="leads" name="leads" class="select2 form-select" multiple>
                @foreach ($members as $member)
                <option value={{$member->id}} >{{$member->name}}</option>
                @endforeach
              </select>

          </div>
          <div class="col-4 mb-4">
            <label class="form-label" for="members">Team Members</label>
              <select  id="members" name="members" class="select2 form-select" multiple>
                @foreach ($members as $member)
                  <option value={{$member->id}} >{{$member->name}}</option>
                  @endforeach
              </select>

          </div>

          <div class="col-4 mb-4">
            <label class="form-label" for="initiatedBy">Project Initiated By</label>
              <select  id="initiatedBy" name="initiatedBy" class="select2 form-select" multiple>
                @foreach ($members as $member)
                  <option value={{$member->id}} >{{$member->name}}</option>
                  @endforeach
              </select>

          </div>
          {{--  <h5>Project Staff Strength </h5>  --}}
          <small class="text-light fw-medium d-block">Project Staff Strenght</small>
          <div class="col-4 mb-4">
            <label class="form-label" for="contaractStaff">Contract Staff</label>
            <input type="number" id="contaractStaff" name="contaractStaff" class="form-control" placeholder="" tabindex="-1" />
          </div>
          <div class="col-4 mb-4">
            <label class="form-label" for="fieldStaff">Field Staff</label>
            <input type="number" id="fieldStaff" name="fieldStaff" class="form-control" placeholder="" tabindex="-1" />
          </div>
          <div class="col-4 mb-4">
            <label class="form-label" for="projectStaff">Project Staff</label>
            <input type="number" id="projectStaff" name="projectStaff" class="form-control" placeholder="" tabindex="-1" />
          </div>

          <div class="divider contactperson">
            <div class="divider-text">
              Project Duration Details
            </div>
          </div>
          {{--  <h5>Tenure Project</h5>  --}}
          <small class="text-light fw-medium d-block">Project Tenure</small>
          <div class="col-4 mb-4">
            <label class="form-label" for="tenure_year">Year</label>
            <input type="text" id="tenure_year" name="tenure_year" class="form-control" placeholder="" tabindex="-1" />
          </div>
          <div class="col-4 mb-4">
            <label class="form-label" for="tenure_month">Month</label>
            <input type="text" id="tenure_month" name="tenure_month" class="form-control" placeholder="" tabindex="-1" />
          </div>
          <div class="col-4 mb-4">
            <label class="form-label" for="tenure_days">Days</label>
            <input type="text" id="tenure_days" name="tenure_days" class="form-control" placeholder="" tabindex="-1" />
          </div>

          <div class="col-6 mb-4">
            <label class="form-label" for="expected_start_date">Expected Start Date</label>
            <input type="text" id="expected_start_date" name="expected_start_date" class="form-control" placeholder="" tabindex="-1" />
          </div>
          <div class="col-6 mb-4">
            <label class="form-label" for="expected_end_date">Expected End Date</label>
            <input type="text" id="expected_end_date" name="expected_end_date" class="form-control" placeholder="" tabindex="-1" />
          </div>


          <div class="col-6 mb-4">
            <label class="form-label" for="additional_support">Additonal Support </label>
             <textarea class="form-control" id="additional_support" name="additional_support" rows="3"></textarea>
          </div>
          <div class="col-6 mb-4">
            <label class="form-label" for="remarks">Remarks </label>
             <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
          </div>

          <div class="col-12 text-center mt-4">
            <button type="submit" id="submit_project" data-id="0" data-type="new" class="btn submit-project btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
        <!--/ Add project form -->
      </div>
    </div>
  </div>
</div>
<!--/ Add Project Modal -->
