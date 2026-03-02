<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientContactPerson;
use App\Models\ProjectCategory;
use App\Models\ProjectSubCategory;
use App\Traits\LogsActivity;


class Requirement extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'type_id',
        'temp_no',
        'project_category_id',
        'project_subcategory_id',
        'project_title',
        'project_description',
        'client_id',
        'client_contact_person_id',
        'ref_no',
        'allocated_to',
        'allocated_at',
        'allocated_by',
        'status',
         'proposal_status',
        'created_by'
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
    ];

    // Requirement types
    const TYPE_REQUIREMENT = 1;
    const TYPE_DIRECT_PROPOSAL = 2;
    const TYPE_TENDER = 3;

    // Status constants
    const STATUS_INITIATED = 0;
    const STATUS_SENT_TO_DIRECTOR = 1;
    const STATUS_APPROVED_BY_DIRECTOR = 2;
    const STATUS_REJECTED = 3;
    const STATUS_SENT_TO_PAC = 4;
     const STATUS_APPROVED_BY_PAC = 5;

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ProjectSubcategory::class, 'project_subcategory_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contactPerson()
    {
        return $this->belongsTo(ClientContactPerson::class, 'client_contact_person_id');
    }

    public function allocatedTo()
    {
        return $this->belongsTo(User::class, 'allocated_to');
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(RequirementDocument::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function project()
{
    return $this->hasOne(Project::class, 'requirement_id');
}

    public function getTypeNameAttribute()
    {
        return [
            self::TYPE_REQUIREMENT => 'Requirement',
            self::TYPE_DIRECT_PROPOSAL => 'Direct Proposal',
              self::TYPE_TENDER => 'Tender',
        ][$this->type_id] ?? 'Unknown';
    }

    public function getStatusNameAttribute()
    {
        return [
            self::STATUS_INITIATED => 'Initiated',
            self::STATUS_SENT_TO_DIRECTOR => 'Sent to Director',
            self::STATUS_APPROVED_BY_DIRECTOR => 'Approved by Director',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_SENT_TO_PAC => 'Sent to PAC',
            self::STATUS_APPROVED_BY_PAC => 'Allocated By PAC',
        ][$this->status] ?? 'Unknown';
    }

     public function getStatusBadgeColorAttribute()
{
    return [
        self::STATUS_INITIATED => 'info', // gray
        self::STATUS_SENT_TO_DIRECTOR => 'primary',     // yellow
        self::STATUS_APPROVED_BY_DIRECTOR => 'success',   // green
        self::STATUS_REJECTED => 'danger',   // green
         self::STATUS_SENT_TO_PAC => 'warning',
          self::STATUS_APPROVED_BY_PAC => 'success',
    ][$this->status] ?? 'dark'; // fallback
}

 public static function generateRequirementCode($clientId, $categoryId)
    {
        $year = now()->format('y');

        // Fetch client & category codes
        $clientCode = \App\Models\Client::find($clientId)?->code ?? 'CLNT';
        $categoryCode = \App\Models\ProjectCategory::find($categoryId)?->code ?? 'CAT';

        // Find the last requirement for this client-category-year
        $lastRequirement = self::where('client_id', $clientId)
            ->where('project_category_id', $categoryId)
            ->whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRequirement && preg_match('/(\d+)$/', $lastRequirement->temp_no, $matches)) {
            $lastSequence = (int) $matches[1];
        } else {
            $lastSequence = 0;
        }

        $newSequence = str_pad($lastSequence + 1, 2, '0', STR_PAD_LEFT);

        return "REQ/{$clientCode}/{$categoryCode}/{$year}/{$newSequence}";
    }
}