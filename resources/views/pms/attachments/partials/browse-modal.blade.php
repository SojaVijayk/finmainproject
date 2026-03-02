<div class="modal fade" id="browseDocumentsModal" tabindex="-1" aria-labelledby="browseDocumentsLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="browseDocumentsLabel">Browse Existing Documents</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <ul class="nav nav-tabs" id="documentTabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tapalDocs"
              type="button">Tapal Documents</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#systemDocs"
              type="button">Document System</button></li>

        </ul>

        <div class="tab-content mt-3">
          <div class="tab-pane fade show active" id="tapalDocs">
            <div id="tapal-documents-list">Loading...</div>
          </div>

          <div class="tab-pane fade" id="systemDocs">
            <div id="documents-documents-list">Loading...</div>
          </div>


        </div>

      </div>
      <div class="modal-footer">
        <button type="button" id="selectDocumentsBtn" class="btn btn-primary">Attach Selected</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>