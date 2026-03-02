<?php

namespace App\Exports;

use App\Models\Document;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DocumentsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Document::with([
            'documentType',
            'creator',
            'authorizedPerson',
            'code.user'
        ]);

        // Apply the same filters as the index method
        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('to_address_details', 'like', "%{$search}%")
                  ->orWhere('project_details', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('authorizedPerson', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('code', function($q) use ($search) {
                      $q->where('code', 'like', "%{$search}%");
                  });
            });
        }

        // Add other filters as needed...

        return $query;
    }

    public function headings(): array
    {
        return [
            'Document Number',
            'Type',
            'Subject',
            'To Address',
            'Project Details',
            'Code',
            'Created By',
            'Authorized Person',
            'Status',
            'Created At',
        ];
    }

    public function map($document): array
    {
        return [
            $document->document_number,
            $document->documentType->name,
            $document->subject,
            $document->to_address_details,
            $document->project_details ?? 'N/A',
            $document->code->code,
            $document->creator->name,
            $document->authorizedPerson->name,
            ucfirst($document->status),
            $document->created_at->format('d-m-Y H:i'),
        ];
    }
}
