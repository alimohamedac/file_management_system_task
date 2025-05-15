<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'subject',
        'status',
        'doc_path',
        'created_by'
    ];
    
    protected $dates = ['deleted_at'];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function workflowInstances()
    {
        return $this->hasMany(DocumentWorkflowInstance::class);
    }
    
    public function currentWorkflowInstance()
    {
        return $this->hasOne(DocumentWorkflowInstance::class)->latest();
    }
}
