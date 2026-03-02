@extends('layouts.audit')

@section('content')
<div class="mb-3">
    <a href="{{ route('audit.projects.index') }}" class="text-muted">&larr; Back to List</a>
</div>

<div class="row">
    <div class="col-12">
        <div class="audit-card mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                     <h3 class="mb-1">{{ $project->title }}</h3>
                     <span class="text-primary fw-bold">{{ $project->project_code }}</span>
                </div>
                 <div>
                    @if($project->status == 0) <span class="badge bg-info fs-6">Initiated</span>
                    @elseif($project->status == 1) <span class="badge bg-primary fs-6">Ongoing</span>
                    @elseif($project->status == 2) <span class="badge bg-success fs-6">Completed</span>
                    @endif
                </div>
            </div>
            <p class="mt-3 text-muted">{{ $project->description ?? 'No description available.' }}</p>

            <div class="row mt-4">
                <div class="col-md-3">
                    <label class="text-muted small d-block">Principal Investigator</label>
                    <span class="fw-bold">{{ $project->investigator->name ?? 'N/A' }}</span>
                </div>
                 <div class="col-md-3">
                    <label class="text-muted small d-block">Duration</label>
                    <span>
                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '?' }}
                        -
                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '?' }}
                    </span>
                </div>
                <div class="col-md-3">
                     <label class="text-muted small d-block">Total Budget</label>
                    <span class="fw-bold text-success fs-5">₹{{ number_format($project->budget, 2) }}</span>
                </div>
                 <div class="col-md-3">
                     <label class="text-muted small d-block">Estimated Expense</label>
                    <span class="fw-bold text-danger fs-5">₹{{ number_format($project->estimated_expense, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-overview-tab" data-bs-toggle="pill" data-bs-target="#pills-overview" type="button" role="tab">Overview & Team</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-expenses-tab" data-bs-toggle="pill" data-bs-target="#pills-expenses" type="button" role="tab">Financials (Expenses)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-invoices-tab" data-bs-toggle="pill" data-bs-target="#pills-invoices" type="button" role="tab">Invoices & Payments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-docs-tab" data-bs-toggle="pill" data-bs-target="#pills-docs" type="button" role="tab">Documents</button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">

            <!-- OVERVIEW TAB -->
            <div class="tab-pane fade show active" id="pills-overview" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="audit-card">
                            <h5>Team Members</h5>
                            <ul class="list-group list-group-flush">
                                @forelse($project->teamMembers as $member)
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>{{ $member->user->name ?? 'Unknown' }}</span>
                                    <span class="badge bg-label-secondary">{{ ucfirst($member->role) }}</span>
                                </li>
                                @empty
                                <li class="list-group-item">No team members assigned.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                     <div class="col-md-6">
                        <div class="audit-card">
                            <h5>Requirement Details</h5>
                            @if($project->requirement)
                            <table class="table table-sm table-borderless">
                                <tr><td>Client:</td><td class="fw-bold">{{ $project->requirement->client->client_name ?? 'N/A' }}</td></tr>
                                <tr><td>Category:</td><td>{{ $project->requirement->category->name ?? 'N/A' }}</td></tr>
                                <tr><td>Ref No:</td><td>{{ $project->requirement->ref_number ?? 'N/A' }}</td></tr>
                            </table>
                            @else
                            <p class="text-muted">No requirement linked.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- FINANCIALS TAB -->
            <div class="tab-pane fade" id="pills-expenses" role="tabpanel">

                <!-- 1. Proposal Stage Expenses -->
                @if($project->proposal)
                <div class="audit-card mb-4">
                    <h5 class="text-muted mb-3"><i class="ti ti-file-text me-2"></i>Proposal Stage - Expense Components</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>Component</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->proposal->expenseComponents as $comp)
                            <tr>
                                <td>{{ $comp->category->name ?? '-' }}</td>
                                <td>{{ $comp->component }}</td>
                                <td class="text-end">₹{{ number_format($comp->amount, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold bg-light">
                                <td colspan="2" class="text-end">Total Proposal Estimate:</td>
                                <td class="text-end">₹{{ number_format($project->proposal->expenseComponents->sum('amount'), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif

                <div class="row">
                    <!-- 2. Project Budgets (Estimated vs Budgeted) -->
                    <div class="col-md-6">
                        <div class="audit-card mb-4 h-100">
                           <h5 class="text-dark mb-3">Project Financial Structure</h5>

                           <!-- Estimated -->
                           <h6 class="text-primary mt-3">Estimated Expenses</h6>
                           <table class="table table-sm table-hover mb-3">
                               <tbody>
                                    @foreach($project->estimatedExpenseComponents() as $est)
                                    <tr>
                                        <td><small>{{ $est->component }}</small></td>
                                        <td class="text-end text-primary">₹{{ number_format($est->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                               </tbody>
                           </table>

                           <!-- Budgeted -->
                           <h6 class="text-success mt-3">Budgeted Expenses</h6>
                           <table class="table table-sm table-hover mb-3">
                               <tbody>
                                    @foreach($project->budgetedExpenseComponents() as $bud)
                                    <tr>
                                        <td><small>{{ $bud->component }}</small></td>
                                        <td class="text-end text-success">₹{{ number_format($bud->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                               </tbody>
                           </table>
                        </div>
                    </div>

                    <!-- 3. Year Wise Split -->
                     <div class="col-md-6">
                        <div class="audit-card mb-4 h-100">
                           <h5 class="text-dark mb-3">Yearly Budget Split</h5>
                           @if($project->yearlyBudgets->count() > 0)
                           <table class="table table-striped table-sm">
                               <thead>
                                   <tr>
                                       <th>Financial Year</th>
                                       <th class="text-end">Amount</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   @foreach($project->yearlyBudgets as $yb)
                                   <tr>
                                       <td>{{ $yb->financial_year }}</td>
                                        <td class="text-end">₹{{ number_format($yb->amount, 2) }}</td>
                                   </tr>
                                   @endforeach
                                   <tr class="fw-bold">
                                       <td class="text-end">Total:</td>
                                       <td class="text-end">₹{{ number_format($project->yearlyBudgets->sum('amount'), 2) }}</td>
                                   </tr>
                               </tbody>
                           </table>
                           @else
                           <p class="text-center text-muted py-4">No yearly budget split defined.</p>
                           @endif
                        </div>
                    </div>
                </div>

                <!-- 4. Actual Expenses Incurred -->
                <div class="audit-card">
                    <h5 class="text-danger mb-3">Actual Expenses Incurred</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Vendor</th>
                                    <th>Category</th>
                                    <th>Payment Mode</th>
                                    <th class="text-end">Amount (+Tax)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->expenses as $exp)
                                <tr>
                                    <td>{{ $exp->payment_date ? \Carbon\Carbon::parse($exp->payment_date)->format('d-m-Y') : '-' }}</td>
                                    <td>{{ $exp->vendor->name ?? '-' }}</td>
                                    <td>{{ $exp->category->name ?? '-' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $exp->payment_mode)) }}</td>
                                    <td class="text-end">₹{{ number_format($exp->total_amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">No actual expenses recorded yet.</td></tr>
                                @endforelse
                                <tr class="table-dark">
                                    <td colspan="4" class="text-end"><strong>Total Actual Expense</strong></td>
                                    <td class="text-end"><strong>₹{{ number_format($project->expenses->sum('total_amount'), 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- INVOICES & PAYMENTS TAB -->
            <div class="tab-pane fade" id="pills-invoices" role="tabpanel">
                <div class="audit-card">
                     <h5>Client Invoices & Received Payments</h5>

                     @forelse($project->invoices as $inv)
                     <div class="border rounded p-3 mb-3 bg-light">
                         <div class="d-flex justify-content-between align-items-center mb-2">
                             <div>
                                 <strong>Invoice #{{ $inv->invoice_number }}</strong>
                                 <small class="text-muted ms-2">({{ $inv->invoice_date ? $inv->invoice_date->format('d-m-Y') : 'No Date' }})</small>
                             </div>
                             <div>
                                 <span class="badge {{ $inv->status == 2 ? 'bg-success' : 'bg-warning' }}">{{ $inv->status_name ?? $inv->status }}</span>
                                 <strong class="ms-3">₹{{ number_format($inv->amount, 2) }}</strong>
                             </div>
                         </div>

                         <!-- Associated Payments -->
                         @if($inv->payments->count() > 0)
                             <div class="ms-4 mt-2">
                                <h6 class="text-muted small mb-1">Received Payments:</h6>
                                <table class="table table-sm table-borderless bg-white rounded">
                                    <thead class="small text-muted">
                                        <tr>
                                            <th>Ref #</th>
                                            <th>Date</th>
                                            <th>Mode</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($inv->payments as $pay)
                                        <tr>
                                            <td>{{ $pay->reference_number ?? '-' }}</td>
                                            <td>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('d-m-Y') : '-' }}</td>
                                            <td>{{ ucfirst($pay->payment_mode) }}</td>
                                            <td class="text-end text-success">₹{{ number_format($pay->amount, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                             </div>
                         @else
                            <div class="ms-4 small text-muted">No payments recorded for this invoice.</div>
                         @endif
                     </div>
                     @empty
                     <p class="text-center text-muted">No invoices found for this project.</p>
                     @endforelse
                </div>
            </div>

             <!-- DOCUMENTS TAB -->
            <div class="tab-pane fade" id="pills-docs" role="tabpanel">
                 <div class="audit-card">
                     <h5>Project Documents</h5>
                      <div class="table-responsive">
                         <table class="table">
                             <thead>
                                 <tr>
                                     <th>File Name</th>
                                     <th>Type</th>
                                     <th>Uploaded By</th>
                                     <th>Date</th>
                                     <th>Action</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @forelse($project->documents as $doc)
                                 <tr>
                                     <td>{{ $doc->file_name }}</td>
                                     <td>{{ $doc->document_type }}</td>
                                     <td>{{ $doc->uploader->name ?? 'N/A' }}</td>
                                     <td>{{ $doc->created_at->format('d-m-Y') }}</td>
                                     <td>
                                         <a href="{{ route('audit.projects.document.download', [$project->id, $doc->id]) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                     </td>
                                 </tr>
                                 @empty
                                 <tr><td colspan="5">No project documents uploaded.</td></tr>
                                 @endforelse
                             </tbody>
                         </table>
                     </div>
                 </div>
            </div>

        </div>
    </div>
</div>
@endsection
