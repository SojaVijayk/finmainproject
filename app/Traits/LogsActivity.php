<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Str;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        foreach (static::getModelEvents() as $event) {
            static::$event(function ($model) use ($event) {
                $model->logActivity($event);
            });
        }
    }
    
    protected static function getModelEvents()
    {
        if (isset(static::$recordEvents)) {
            return static::$recordEvents;
        }
        
        return [
            'created',
            'updated',
            'deleted'
        ];
    }
    
    protected function logActivity($event)
    {
        ActivityLog::create([
            'subject_id' => $this->id,
            'subject_type' => get_class($this),
            'event' => $event,
            'description' => $this->getActivityDescription($event),
            'user_id' => auth()->id(),
            'properties' => $this->getActivityProperties($event)
        ]);
    }
    
    protected function getActivityDescription($event)
    {
        $modelName = class_basename($this);
        
        switch ($event) {
            case 'created':
                return "Created new {$modelName}: {$this->getActivityName()}";
            case 'updated':
                return "Updated {$modelName}: {$this->getActivityName()}";
            case 'deleted':
                return "Deleted {$modelName}: {$this->getActivityName()}";
            default:
                return "Performed action on {$modelName}: {$this->getActivityName()}";
        }
    }
    
    protected function getActivityName()
    {
        if (isset($this->name)) {
            return $this->name;
        }
        
        if (isset($this->title)) {
            return $this->title;
        }
        
        return Str::singular($this->getTable()) . " #{$this->id}";
    }
    
    protected function getActivityProperties($event)
    {
        $properties = [];
        
        if ($event == 'updated') {
            $properties['old'] = collect($this->getOriginal())->except($this->getHidden());
            $properties['attributes'] = $this->getAttributes();
        } else {
            $properties['attributes'] = $this->getAttributes();
        }
        
        return $properties;
    }
}