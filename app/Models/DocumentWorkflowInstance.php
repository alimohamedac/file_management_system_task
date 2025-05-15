<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentWorkflowInstance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'document_id',
        'workflow_id',
        'status',
        'sent_at'
    ];
    
    protected $dates = [
        'sent_at',
        'deleted_at'
    ];
    
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
    
    public function slotStatuses()
    {
        return $this->hasMany(DocumentSlotStatus::class);
    }
    
    public function currentSlotStatus()
    {
        return $this->slotStatuses()->whereHas('workflowSlot', function ($query) {
            $query->orderBy('slot_number', 'desc');
        })->first();
    }

    public function isCompleted()
    {
        return $this->status === 'approved' || $this->status === 'rejected';
    }
}
