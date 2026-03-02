@extends('layouts/layoutMaster')

@section('title', 'Project Status Report')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}">

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('categoryChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($categorySummary->toArray())) !!},
            datasets: [{
                label: 'Budget',
                data: {!! json_encode($categorySummary->pluck('total_budget')->toArray()) !!},
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
            },{
                label: 'Revenue',
                data: {!! json_encode($categorySummary->pluck('total_revenue')->toArray()) !!},
                backgroundColor: 'rgba(121, 254, 167, 0.6)',
            }
            {{--  ,
             {
                label: 'Invoice',
                data: {!! json_encode($categorySummary->pluck('total_invoice_raised')->toArray()) !!},
                backgroundColor: 'rgba(255, 165, 0, 0.6)',
            },
            {
                label: 'Paid',
                data: {!! json_encode($categorySummary->pluck('total_invoice_paid')->toArray()) !!},
                backgroundColor: 'rgba(60, 179, 113, 0.6)',
            }  --}}
            ]
        }
    });

        const investigatorCategoryWise = {!! json_encode($investigatorCategoryWise) !!};

    const investigatorLabels = Object.keys(investigatorCategoryWise);

    const categories = [...new Set(
        investigatorLabels.flatMap(inv => Object.keys(investigatorCategoryWise[inv]))
    )];

    const datasets = categories.map((cat, index) => {
        return {
            label: cat,
            data: investigatorLabels.map(inv => {
                return investigatorCategoryWise[inv][cat]?.total_budget ?? 0;
            }),
            backgroundColor: `hsla(${index * 60}, 70%, 60%, 0.6)`
        };
    });

    new Chart(document.getElementById('investigatorCategoryChart'), {
        type: 'bar',
        data: {
            labels: investigatorLabels,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Investigator-wise Project Budget (by Category)'
                }
            }
        }
    });

    const ctx1 = document.getElementById('invoiceChart');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($categorySummary->toArray())) !!},
            datasets: [
             {
                label: 'Proforma Invoice',
                data: {!! json_encode($categorySummary->pluck('total_invoice_raised_proforma')->toArray()) !!},
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
            },
            {
                label: 'Tax Invoice',
                data: {!! json_encode($categorySummary->pluck('total_invoice_raised_tax')->toArray()) !!},
                backgroundColor: 'rgba(255, 165, 0, 0.6)',
            },
            {
                label: 'Paid',
                data: {!! json_encode($categorySummary->pluck('total_invoice_paid')->toArray()) !!},
                backgroundColor: 'rgba(60, 179, 113, 0.6)',
            },
             {
                label: 'Balance',
                data: {!! json_encode($categorySummary->pluck('total_balance')->toArray()) !!},
                backgroundColor: 'rgba(255, 0, 0, 0.6)',
            }]
        }
    });

     const ctx2 = document.getElementById('projectChart');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($categorySummary->toArray())) !!},
            datasets: [
            {
                label: 'Initiated',
                data: {!! json_encode($categorySummary->pluck('initiated_count')->toArray()) !!},
                backgroundColor: 'rgba(238, 197, 145, 0.6)',
            },
            {
                label: 'Ongoing',
                data: {!! json_encode($categorySummary->pluck('ongoing_count')->toArray()) !!},
                backgroundColor: 'rgba(102, 192, 88, 0.6',
            },
            {
                label: 'Completed',
                data: {!! json_encode($categorySummary->pluck('completed_count')->toArray()) !!},
                backgroundColor: 'rgba(106, 90, 205, 0.6)',
            },
            {
                label: 'Delayed',
                data: {!! json_encode($categorySummary->pluck('delayed_count')->toArray()) !!},
                backgroundColor: 'rgba(251, 0, 0, 0.6',
            },

             {
                label: 'Archived',
                data: {!! json_encode($categorySummary->pluck('archived_count')->toArray()) !!},
                backgroundColor: 'rgba(93, 99, 71, 0.6)',
            }]
        }
    });


    const ctx_PI = document.getElementById('categoryPlanningChart');
    new Chart(ctx_PI, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($categorySummary_planningStage->toArray())) !!},
            datasets: [{
                label: 'Created',
                data: {!! json_encode($categorySummary_planningStage->pluck('created')->toArray()) !!},
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
            },{
                label: 'Submitted',
                data: {!! json_encode($categorySummary_planningStage->pluck('submitted')->toArray()) !!},
                backgroundColor: 'rgba(121, 254, 167, 0.6)',
            },
             {
                label: 'Under PAG',
                data: {!! json_encode($categorySummary_planningStage->pluck('under_pac')->toArray()) !!},
                backgroundColor: 'rgba(238, 197, 145, 0.6)',
            },
            {
                label: 'Approved',
                data: {!! json_encode($categorySummary_planningStage->pluck('approved')->toArray()) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
            },
            {
                label: 'Rejected',
                data: {!! json_encode($categorySummary_planningStage->pluck('rejected')->toArray()) !!},
                backgroundColor: 'rgba(255, 0, 0, 0.6)',
            }
              ]
        }
    });

      const ctx_PS = document.getElementById('categoryProposalSubmittedChart');
    new Chart(ctx_PS, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($categorySummary_proposalSubmitted->toArray())) !!},
            datasets: [{
                label: 'Budget',
                data: {!! json_encode($categorySummary_proposalSubmitted->pluck('total_budget')->toArray()) !!},
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
            },{
                label: 'Revenue',
                data: {!! json_encode($categorySummary_proposalSubmitted->pluck('total_revenue')->toArray()) !!},
                backgroundColor: 'rgba(121, 254, 167, 0.6)',
            },
             {
                label: 'Submitted',
                data: {!! json_encode($categorySummary_proposalSubmitted->pluck('submitted')->toArray()) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
            },
             {
                label: 'Accepted',
                data: {!! json_encode($categorySummary_proposalSubmitted->pluck('Accepted')->toArray()) !!},
                backgroundColor: 'rgba(238, 197, 145, 0.6)',
            },

              {
                label: 'Rework',
                data: {!! json_encode($categorySummary_proposalSubmitted->pluck('resubmit_requested')->toArray()) !!},
                backgroundColor: 'rgba(60, 150, 255, 0.6)',
            },
             {
                label: 'Rejected',
                data: {!! json_encode($categorySummary_proposalSubmitted->pluck('Rejected')->toArray()) !!},
                backgroundColor: 'rgba(255, 0, 0, 0.6)',
            }]
        }
    });
     $('.select2').select2({ width: 'resolve' });
document.getElementById("downloadFullPDF").addEventListener("click", function () {

    const page = document.body;

    html2canvas(page, {
        scale: 2,
        useCORS: true,
        scrollX: 0,
        scrollY: -window.scrollY,
        windowWidth: document.documentElement.scrollWidth,
        windowHeight: document.documentElement.scrollHeight
    }).then(canvas => {

        const imgData = canvas.toDataURL("image/png");

        const pdf = new jspdf.jsPDF('p', 'mm', 'a4');

        const pageWidth = 210;     // A4 width (mm)
        const pageHeight = 297;    // A4 height (mm)

        const canvasWidth = canvas.width;
        const canvasHeight = canvas.height;

        // Image height proportionate to PDF width
        const imgHeight = (canvasHeight * pageWidth) / canvasWidth;

        let heightLeft = imgHeight;
        let position = 0;

        // Add the first page
        pdf.addImage(imgData, 'PNG', 0, position, pageWidth, imgHeight);

        heightLeft -= pageHeight;

        // Add extra pages smoothly
        while (heightLeft > -10) { // prevent cutoff
            position -= pageHeight - 5; // 5mm overlap to avoid cutting
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 0, position, pageWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        pdf.save("Full_Report_" + Date.now() + ".pdf");
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

@endsection
@section('header', 'Project Status Report')

@section('content')
@php

//$users = \App\Models\User::with('employee')
//->whereHas('employee', function($q) {
//$q->whereIn('designation', [2, 7, 9]);
//$user = Auth::user();
//})
//->get();
$users = \App\Models\User::role('Project Investigator')
->with('employee')
->get();
@endphp
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Project Status Report</h5>
      <div>
        {{-- <a type="button" id="downloadFullPDF" class="btn  btn-label-dark ">
          📄 Export
        </a> --}}
        <a type="button" class="btn btn-label-primary" href="{{ route('pms.reports.project-status') }}">Project Progress
          Report</a>
        {{-- @if(Auth::user()->hasRole('director') || Auth::user()->hasRole('finance')) --}}
        <a type="button" class="btn btn-label-primary" href="{{ route('pms.reports.project-status-report') }}">Project
          Financial
          Report</a>
        {{-- @endif --}}
      </div>
    </div>
  </div>
  <div class="card-body">


    {{-- Filters --}}
    <form method="GET" class="mb-3 row">
      <div class="col">
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
      </div>
      <div class="col">
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
      </div>

      <div class="col">
        <select name="category_id" class="form-control select2">
          <option value="">All Categories</option>
          @foreach(\App\Models\ProjectCategory::all() as $cat)
          <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id ? 'selected':'' }}>
            {{ $cat->name }}
          </option>
          @endforeach
        </select>
      </div>

      @if(Auth::user()->hasRole('director') || Auth::user()->hasRole('Project Report') ||
      Auth::user()->hasRole('finance') || Auth::user()->hasRole('Project Investigator') )
      <div class="col">
        <select name="investigator_id" class="form-control select2">
          <option value="">All Investigators</option>
          @foreach($users as $user)
          <option value="{{ $user->id }}" {{ request('investigator_id')==$user->id ? 'selected':'' }}>
            {{ $user->name }}
          </option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="col">
        <button class="btn btn-primary">Filter</button>
      </div>
    </form>

    <div class="row mb-4">
      <div class="col-md-2">
        <div class="card text-white ">
          <div class="card-body">
            <small class="text-primary">Live Projects</small>
            <h3>{{ $ongoingProjects+$completedProjects }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card text-white ">
          <div class="card-body">
            <small class="text-success">Ongoing</small>
            <h3>{{ $ongoingProjects }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card text-white ">
          <div class="card-body">
            <small class="text-dark">Completed</small>
            <h3>{{ $completedProjects }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card text-white ">
          <div class="card-body">
            <small class="text-danger">Delayed</small>
            <h3>{{ $delayedProjects }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card text-white ">
          <div class="card-body">
            <small class="text-warning">Proposal Submitted</small>
            <h3>{{ $proposalSubmitted }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card text-white ">
          <div class="card-body">
            <small class="text-secondary">Planned</small>
            <h3>{{ $planningStage }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2 mt-2">
        <div class="card text-white ">
          <div class="card-body">
            <small class="text-secondary">Archived</small>
            <h3>{{ $archived }}</h3>
          </div>
        </div>
      </div>

    </div>


    <div class="row mt-2">
      <div class="col-md-12  mb-4">
        {{-- Category Summary Table --}}
        <div class="alert alert-success" role="alert">
          <h5>Summary by Category(O+C+D Projects)</h5><small class="text-secondary">Note: All amounts are without
            GST, except for invoice amounts and payments.</small>
        </div>

        <div class="table-responsive mb-4">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th rowspan="2">Category</th>
                <th rowspan="2">Budget (Lakhs)</th>
                <th rowspan="2">Gross Income (Lakhs)</th>
                <th colspan="3"><span class="badge bg-label-primary">Invoice Raised (Lakhs)</span></th>
                <th rowspan="2"><span class="badge bg-label-success">Invoice Paid (Lakhs)</span></th>
                <th rowspan="2"><span class="badge bg-label-danger">Balance (Lakhs)</span></th>
                <th rowspan="2"><span class="badge bg-label-warning">Initiated</span></th>
                <th rowspan="2"><span class="badge bg-label-success">Ongoing</span></th>
                <th rowspan="2"><span class="badge bg-label-danger">Delayed</span></th>
                <th rowspan="2"><span class="badge bg-label-primary">Completed</span></th>
                <th rowspan="2"><span class="badge bg-label-dark">Archived</span></th>

              </tr>
              <tr>
                <th>Tax</th>
                <th>Proforma</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              @php
              $totalBudget = $totalRevenue = $totalInvoiceRaised = $totalInvoicePaid = $totalBalance = 0;
              $totalInitiated = $totalOngoing = $totalCompleted = $totalArchived = $totalDelayed = 0;
              $totalInvoiceRaisedTax = $totalInvoiceRaisedProforma = 0;
              @endphp
              @foreach($categorySummary as $cat => $data)
              <tr>
                <td><i class="icon-base fas fa-project-diagram text-success me-4"></i> <span class="fw-medium">
                  </span>{{ $cat }}</td>
                <td>{{ number_format($data['total_budget'], 2) }}</td>
                <td>{{ number_format($data['total_revenue'], 2) }}</td>

                <td>{{ number_format($data['total_invoice_raised_tax'], 2) }}</td>
                <td>{{ number_format($data['total_invoice_raised_proforma'], 2) }}</td>
                <td>{{ number_format($data['total_invoice_raised'], 2) }}</td>

                <td>{{ number_format($data['total_invoice_paid'], 2) }}</td>
                <td>{{ number_format($data['total_balance'], 2) }}</td>
                <td>{{ $data['initiated_count'] }}</td>
                <td>{{ $data['ongoing_count'] }}</td>
                <td>{{ $data['delayed_count'] }}</td>
                <td>{{ $data['completed_count'] }}</td>
                <td>{{ $data['archived_count'] }}</td>

              </tr>
              @php
              $totalBudget += $data['total_budget'];
              $totalRevenue += $data['total_revenue'];
              $totalInvoiceRaised += $data['total_invoice_raised'];
              $totalInvoiceRaisedTax += $data['total_invoice_raised_tax'];
              $totalInvoiceRaisedProforma += $data['total_invoice_raised_proforma'];
              $totalInvoicePaid += $data['total_invoice_paid'];
              $totalBalance += $data['total_balance'];
              $totalInitiated += $data['initiated_count'];
              $totalOngoing += $data['ongoing_count'];
              $totalDelayed += $data['delayed_count'];
              $totalCompleted += $data['completed_count'];
              $totalArchived += $data['archived_count'];
              @endphp
              @endforeach
              <tr class="table-success fw-bold">
                <td class="text-end">Grand Total</td>
                <td>{{ number_format($totalBudget, 2) }}</td>
                <td>{{ number_format($totalRevenue, 2) }}</td>

                <td>{{ number_format($totalInvoiceRaisedTax, 2) }}</td>
                <td>{{ number_format($totalInvoiceRaisedProforma, 2) }}</td>
                <td>{{ number_format($totalInvoiceRaised, 2) }}</td>
                <td>{{ number_format($totalInvoicePaid, 2) }}</td>
                <td>{{ number_format($totalBalance, 2) }}</td>
                <td>{{ $totalInitiated }}</td>
                <td>{{ $totalOngoing }}</td>
                <td>{{ $totalDelayed }}</td>
                <td>{{ $totalCompleted }}</td>
                <td>{{ $totalArchived }}</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>


    <div class="row mt-2">
      <div class="col-md-12  mb-4">
        <div class="alert alert-primary" role="alert">
          <h5>Summary by Category(Proposal Submitted)</h5><small class="text-secondary">Note: All amounts are without
            GST, except for invoice amounts and payments.</small>
        </div>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Category</th>
              <th>Budget (Lakhs)</th>
              <th>Gross Income (Lakhs)</th>
              <th><span class="badge bg-label-primary">Submitted</span></th>
              <th><span class="badge bg-label-success">Accepted</span></th>
              <th><span class="badge bg-label-danger">Rejected</span></th>
              <th><span class="badge bg-label-warning">Resubmission</span></th>
              <th><span class="badge bg-label-dark">Total</span></th>

            </tr>
          </thead>
          <tbody>
            @php
            $totalBudgetPS = $totalRevenuePS = 0;
            $totalSubmitted = $totalAccepted = $totalRejected = $totalResubmit = 0;
            @endphp
            @foreach($categorySummary_proposalSubmitted as $cat => $data)
            <tr>
              <td><i class="icon-base fas fa-project-diagram text-primary me-4"></i> <span class="fw-medium">{{ $cat
                  }}</span>
              </td>
              <td>{{ number_format($data['total_budget'], 2) }}</td>
              <td>{{ number_format($data['total_revenue'], 2) }}</td>
              <td>{{ $data['submitted'] }}</td>
              <td>{{ $data['Accepted'] }}</td>
              <td>{{ $data['Rejected'] }}</td>
              <td>{{ $data['resubmit_requested'] }}</td>
              <td class="fw-bold">{{ $data['submitted']+$data['Accepted']+$data['Rejected']+$data['resubmit_requested']
                }}</td>
            </tr>
            @php
            $totalBudgetPS += $data['total_budget'];
            $totalRevenuePS += $data['total_revenue'];
            $totalSubmitted += $data['submitted'];
            $totalAccepted += $data['Accepted'];
            $totalRejected += $data['Rejected'];
            $totalResubmit += $data['resubmit_requested'];
            @endphp
            @endforeach
            {{-- Grand Total Row --}}
            <tr class="table-primary fw-bold">
              <td class="text-end">Grand Total</td>
              <td>{{ number_format($totalBudgetPS, 2) }}</td>
              <td>{{ number_format($totalRevenuePS, 2) }}</td>
              <td>{{ $totalSubmitted }}</td>
              <td>{{ $totalAccepted }}</td>
              <td>{{ $totalRejected }}</td>
              <td>{{ $totalResubmit }}</td>
              <td>{{ $totalSubmitted + $totalAccepted + $totalRejected + $totalResubmit }}</td>
            </tr>
          </tbody>
        </table>
      </div>


      <div class="col-md-12 mt-2 mb-4">
        <div class="alert alert-dark" role="alert">
          <h5>Summary by Category(Project Planning)</h5>
        </div>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Category</th>
              <th><span class="badge bg-label-secondary">Initiated</span></th>
              <th><span class="badge bg-label-primary">Submitted</span></th>
              <th><span class="badge bg-label-warning">Under PAC</span></th>
              <th><span class="badge bg-label-success">Approved</span></th>
              <th><span class="badge bg-label-danger">Rejected</span></th>
              <th><span class="badge bg-label-dark">Total</span></th>

            </tr>
          </thead>
          <tbody>
            @php
            $totalCreatedPI = $totalSubmittedPI = $totalUnderPacPI = $totalRejectedPI = $totalApprovedPI = 0;
            @endphp
            @foreach($categorySummary_planningStage as $cat => $data)
            <tr>
              <td><i class="icon-base fas fa-project-diagram text-dark me-4"></i> <span class="fw-medium">{{ $cat
                  }}</span>
              </td>

              <td>{{ $data['created'] }}</td>
              <td>{{ $data['submitted'] }}</td>
              <td>{{ $data['under_pac'] }}</td>
              <td>{{ $data['rejected'] }}</td>
              <td>{{ $data['approved'] }}</td>
              <td class="fw-bold">{{
                $data['created']+$data['submitted']+$data['under_pac']+$data['rejected']+$data['approved'] }}</td>
            </tr>
            @php
            $totalCreatedPI += $data['created'];
            $totalSubmittedPI += $data['submitted'];
            $totalUnderPacPI += $data['under_pac'];
            $totalRejectedPI += $data['rejected'];
            $totalApprovedPI += $data['approved'];
            @endphp
            @endforeach
            {{-- Grand Total Row --}}
            <tr class="table-dark fw-bold">
              <td class="text-end">Grand Total</td>
              <td>{{ $totalCreatedPI }}</td>
              <td>{{ $totalSubmittedPI }}</td>
              <td>{{ $totalUnderPacPI }}</td>
              <td>{{ $totalRejectedPI }}</td>
              <td>{{ $totalApprovedPI }}</td>
              <td>{{ $totalCreatedPI + $totalSubmittedPI + $totalUnderPacPI + $totalRejectedPI + $totalApprovedPI }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>


    </div>

    <div class="row">
      <div class="col-md-6 mt-4">
        <div class="alert alert-dark" role="alert">
          <h4 class="">Project Status</h4>
        </div>
        <canvas id="projectChart"></canvas>
      </div>

      <div class="col-md-6">
        <div class="alert alert-dark mt-4" role="alert">
          <h4 class="text-center">Proposal Submitted</h4>
        </div>
        <canvas id="categoryProposalSubmittedChart"></canvas>
      </div>

      <div class="col-md-6">
        <div class="alert alert-dark mt-4" role="alert">
          <h4 class=" text-center">Project Planned</h4>
        </div>
        <canvas id="categoryPlanningChart"></canvas>
      </div>

      <div class="col-md-6 mt-4">
        <div class="alert alert-dark" role="alert">
          <h4 class="">Invoice Status</h4><small class="text-secondary">Note: All amounts are without
            GST, except for invoice amounts and payments.</small>
        </div>
        <canvas id="invoiceChart"></canvas>
      </div>
    </div>

    {{-- Charts --}}
    <div class="row">
      <div class="col-md-6">
        <div class="alert alert-dark mt-4" role="alert">
          <h4 class="text-center">Budget</h4>
        </div>
        <canvas id="categoryChart"></canvas>
      </div>
      <div class="col-md-6">
        <div class="alert alert-dark mt-4" role="alert">
          <h4 class="text-center">Principal investigator</h4>
        </div>
        <canvas id="investigatorCategoryChart"></canvas>
      </div>


    </div>



    <div class="col-md-12 mt-2 mb-4">
      <div class="alert alert-dark" role="alert">
        <h5>Principal investigator </h5><small class="text-secondary">Note: All amounts are without
          GST, except for invoice amounts and payments.</small>

      </div>
      @php
      // Prepare grand totals
      $grandTotals = [
      'budget' => 0,
      'revenue' => 0,
      'invoice_raised' => 0,
      'invoice_raised_tax' => 0,
      'invoice_raised_proforma' => 0,
      'invoice_paid' => 0,
      'balance' => 0,
      'ongoing' => 0,
      'completed' => 0,
      'delayed' => 0,
      'initiated' => 0,
      'archived' => 0,
      ];
      @endphp

      @foreach($investigatorCategoryWise as $investigator => $categories)
      <h4>{{ $investigator }} </h4>
      @php
      // Reset investigator subtotal
      $subTotals = [
      'budget' => 0,
      'revenue' => 0,
      'invoice_raised' => 0,
      'invoice_raised_tax' => 0,
      'invoice_raised_proforma' => 0,
      'invoice_paid' => 0,
      'balance' => 0,
      'ongoing' => 0,
      'completed' => 0,
      'delayed' => 0,
      'initiated' => 0,
      'archived' => 0,
      ];
      @endphp

      <table border="1" cellpadding="6" cellspacing="0"
        style="border-collapse: collapse; width:100%; margin-bottom:20px;">
        <thead style="background:#f4f4f4;">
          <tr>
            <th rowspan="2">Category</th>
            <th rowspan="2">Budget (Lakhs)</th>
            <th rowspan="2">Gross Income (Lakhs)</th>
            <th colspan="3">Invoice Raised (Lakhs)</th>
            <th rowspan="2">Invoice Paid (Lakhs)</th>
            <th rowspan="2">Balance (Lakhs)</th>
            <th rowspan="2"><span class="badge bg-label-warning">Initiated</span></th>
            <th rowspan="2"><span class="badge bg-label-success">Ongoing</span></th>
            <th rowspan="2"><span class="badge bg-label-danger">Delayed</span></th>
            <th rowspan="2"><span class="badge bg-label-primary">Completed</span></th>
            <th rowspan="2"><span class="badge bg-label-dark">Archived</span></th>

          </tr>
          <tr>
            <th>Tax</th>
            <th>Proforma</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($categories as $category => $data)
          <tr>
            <td>{{ $category }}</td>
            <td>{{ number_format($data['total_budget'], 2) }}</td>
            <td>{{ number_format($data['total_revenue'], 2) }}</td>
            <td>{{ number_format($data['total_invoice_raised_tax'], 2) }}</td>
            <td>{{ number_format($data['total_invoice_raised_proforma'], 2) }}</td>
            <td>{{ number_format($data['total_invoice_raised'], 2) }}</td>
            <td>{{ number_format($data['total_invoice_paid'], 2) }}</td>
            <td>{{ number_format($data['total_balance'], 2) }}</td>
            <td>{{ $data['initiated_count'] }}</td>
            <td>{{ $data['ongoing_count'] }}</td>
            <td>{{ $data['delayed_count'] }}</td>
            <td>{{ $data['completed_count'] }}</td>

            <td>{{ $data['archived_count'] }}</td>
          </tr>

          @php
          // Update investigator subtotals
          $subTotals['budget'] += $data['total_budget'];
          $subTotals['revenue'] += $data['total_revenue'];
          $subTotals['invoice_raised'] += $data['total_invoice_raised'];
          $subTotals['invoice_raised_tax'] += $data['total_invoice_raised_tax'];
          $subTotals['invoice_raised_proforma'] += $data['total_invoice_raised_proforma'];
          $subTotals['invoice_paid'] += $data['total_invoice_paid'];
          $subTotals['balance'] += $data['total_balance'];
          $subTotals['ongoing'] += $data['ongoing_count'];
          $subTotals['delayed'] += $data['delayed_count'];
          $subTotals['completed'] += $data['completed_count'];
          $subTotals['initiated'] += $data['initiated_count'];
          $subTotals['archived'] += $data['archived_count'];
          @endphp
          @endforeach
        </tbody>
        <tfoot style="background:#e9ecef; font-weight:bold;">
          <tr>
            <td>Total ({{ $investigator }})</td>
            <td>{{ number_format($subTotals['budget'], 2) }}</td>
            <td>{{ number_format($subTotals['revenue'], 2) }}</td>
            <td>{{ number_format($subTotals['invoice_raised_tax'], 2) }}</td>
            <td>{{ number_format($subTotals['invoice_raised_proforma'], 2) }}</td>
            <td>{{ number_format($subTotals['invoice_raised'], 2) }}</td>
            <td>{{ number_format($subTotals['invoice_paid'], 2) }}</td>
            <td>{{ number_format($subTotals['balance'], 2) }}</td>
            <td>{{ $subTotals['initiated'] }}</td>
            <td>{{ $subTotals['ongoing'] }}</td>
            <td>{{ $subTotals['delayed'] }}</td>
            <td>{{ $subTotals['completed'] }}</td>

            <td>{{ $subTotals['archived'] }}</td>
          </tr>
        </tfoot>
      </table>

      @php
      // Update grand totals
      foreach($subTotals as $key => $val) {
      $grandTotals[$key] += $val;
      }
      @endphp
      @endforeach

      {{-- GRAND TOTAL TABLE --}}
      <h3>Grand Totals (All Principal investigators)</h3>
      <table border="1" cellpadding="6" cellspacing="0"
        style="border-collapse: collapse; width:100%; margin-top:20px; font-weight:bold;">
        <thead style="background:#dcdcdc;">
          <tr>
            <th rowspan="2">Budget (Lakhs)</th>
            <th rowspan="2">Gross Income (Lakhs)</th>
            <th colspan="3">Invoice Raised (Lakhs)</th>
            <th rowspan="2">Invoice Paid (Lakhs)</th>
            <th rowspan="2">Balance (Lakhs)</th>
            <th rowspan="2"><span class="badge bg-label-warning">Initiated</span></th>
            <th rowspan="2"><span class="badge bg-label-success">Ongoing</span></th>
            <th rowspan="2"><span class="badge bg-label-danger">Delayed</span></th>
            <th rowspan="2"><span class="badge bg-label-primary">Completed</span></th>
            <th rowspan="2"><span class="badge bg-label-dark">Archived</span></th>
          </tr>
          <tr>
            <th>Tax</th>
            <th>Proforma</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ number_format($grandTotals['budget'], 2) }}</td>
            <td>{{ number_format($grandTotals['revenue'], 2) }}</td>
            <td>{{ number_format($grandTotals['invoice_raised_tax'], 2) }}</td>
            <td>{{ number_format($grandTotals['invoice_raised_proforma'], 2) }}</td>
            <td>{{ number_format($grandTotals['invoice_raised'], 2) }}</td>
            <td>{{ number_format($grandTotals['invoice_paid'], 2) }}</td>
            <td>{{ number_format($grandTotals['balance'], 2) }}</td>
            <td>{{ $grandTotals['initiated'] }}</td>
            <td>{{ $grandTotals['ongoing'] }}</td>
            <td>{{ $grandTotals['delayed'] }}</td>
            <td>{{ $grandTotals['completed'] }}</td>

            <td>{{ $grandTotals['archived'] }}</td>
          </tr>
        </tbody>
      </table>
    </div>



  </div>
</div>
@endsection