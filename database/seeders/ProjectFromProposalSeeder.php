<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PMS\Project;
use App\Models\PMS\Proposal;
use Illuminate\Support\Facades\DB;

class ProjectFromProposalSeeder extends Seeder
{
    public function run()
    {
        // Cache sequence numbers for each client-category-year
        $sequenceCache = [];

        // Get proposals with project_status = 1
        $proposals = Proposal::with(['requirement.client', 'requirement.category'])
            ->where('project_status', 1)
            ->get();

        foreach ($proposals as $proposal) {
            $requirement = $proposal->requirement;

            if (!$requirement || !$requirement->client || !$requirement->category) {
                continue; // skip if missing data
            }

            $clientCode = $requirement->client->code ?? 'CLNT';
            $categoryCode = $requirement->category->code ?? 'CAT';
            $year = $proposal->expected_start_date
                ? $proposal->expected_start_date->format('y')
                : now()->format('y');

            // Unique key for sequence tracking
            $seqKey = "{$clientCode}-{$categoryCode}-{$year}";

            if (!isset($sequenceCache[$seqKey])) {
                // Get last sequence from DB for existing projects
                $lastProject = Project::whereHas('requirement.client', fn($q) => $q->where('code', $clientCode))
                    ->whereHas('requirement.category', fn($q) => $q->where('code', $categoryCode))
                    ->whereYear('start_date', '20' . $year)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastProject && preg_match('/(\d+)$/', $lastProject->project_code, $matches)) {
                    $sequenceCache[$seqKey] = (int) $matches[1];
                } else {
                    $sequenceCache[$seqKey] = 0;
                }
            }

            // Increment sequence
            $sequenceCache[$seqKey]++;
            $sequenceNum = str_pad($sequenceCache[$seqKey], 2, '0', STR_PAD_LEFT);

            // Generate project code
            $projectCode = "{$clientCode}/{$categoryCode}/{$year}/{$sequenceNum}";

            // Insert into projects table
             $project=  Project::create([
                'project_code'           => $projectCode,
                'requirement_id'         => $requirement->id,
                'proposal_id'            => $proposal->id,
                'title'                  => $requirement->project_title,
                'start_date'             => $proposal->expected_start_date,
                'end_date'               => $proposal->expected_end_date,
                'budget'                 => $proposal->budget,
                'estimated_expense'      => $proposal->estimated_expense,
                'revenue'                => $proposal->revenue,
                'status'                 => Project::STATUS_ONGOING,
                'project_investigator_id'=> $proposal->created_by,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

             $project->teamMembers()->create([
            'user_id' => $proposal->created_by,
            'role' => 'lead',
            'expected_time_investment_minutes' => 0,
        ]);

        }
    }
}