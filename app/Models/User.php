<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\PMS\Project;
use App\Models\PMS\Invoice;




class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'user_role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function employee()
  {
    return $this->hasOne(\App\Models\Employee::class);
  }

  public function timesheets()
{
    return $this->hasMany(\App\Models\PMS\Timesheet::class);
}

  public function projects()
{
    return $this->hasMany(\App\Models\PMS\Project::class,'project_investigator_id');
}
  public function requirements()
{
    return $this->hasMany(\App\Models\PMS\Requirement::class,'created_by');
}


  public function hostedBookings()
{
    return $this->hasMany(Booking::class, 'hosted_by');
}

public function coordinatedBookings()
{
    return $this->hasMany(Booking::class, 'coordinator_id');
}

public function bookingRequests()
{
    return $this->hasMany(BookingRequest::class, 'requested_by');
}

public function investigatorProjects()
{
    return $this->hasMany(Project::class, 'project_investigator_id');
}

public function requestedInvoices()
{
    return $this->hasMany(Invoice::class, 'requested_by');
}


}
