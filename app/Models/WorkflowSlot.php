<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowSlot extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'workflow_id',
        'slot_number',
        'description',
        'approval_method',
        'parent_slot_id'
    ];
    
    protected $dates = ['deleted_at'];
    
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'slot_user');
    }
    
    public function parentSlot()
    {
        return $this->belongsTo(WorkflowSlot::class, 'parent_slot_id');
    }
    
    public function childSlots()
    {
        return $this->hasMany(WorkflowSlot::class, 'parent_slot_id');
    }
    
    public function slotStatuses()
    {
        return $this->hasMany(DocumentSlotStatus::class);
    }

    public function isCurrentSlotForUser($user)
    {
        if (!$user) return false;
        return $this->users()->where('users.id', $user->id)->exists();
    }
}
