<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['name'];
    
    protected $dates = ['deleted_at'];
    
    public function slots()
    {
        return $this->hasMany(WorkflowSlot::class);
    }
    
    public function documents()
    {
        return $this->hasMany(DocumentWorkflowInstance::class);
    }
}
