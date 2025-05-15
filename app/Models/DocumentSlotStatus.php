<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSlotStatus extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'document_workflow_instance_id',
        'workflow_slot_id',
        'user_id',
        'status',
        'signed_at',
        'comments'
    ];
    
    protected $dates = [
        'signed_at',
        'deleted_at'
    ];
    
    public function workflowInstance()
    {
        return $this->belongsTo(DocumentWorkflowInstance::class, 'document_workflow_instance_id');
    }
    
    public function workflowSlot()
    {
        return $this->belongsTo(WorkflowSlot::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
