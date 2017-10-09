<?php

namespace App;

use App\UPCont\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Nicolaslopezj\Searchable\SearchableTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable implements JWTSubject
{

    use Notifiable, SearchableTrait;
    use EntrustUserTrait {
        restore as private restoreEntrust;
    }
    use SoftDeletes {
        restore as private restoreSoftDelete;
    }

    /**
     * The attribute set the database connection as account.
     *
     * @var string
     */
    protected $connection = 'account';

    /**
     * Serchable filter.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'users.name'  => 10,
            'users.email' => 9,
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'is_user',
        'is_contact',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Fix the duplicate restore.
     */
    public function restore()
    {
        $this->restoreEntrust();
        $this->restoreSoftDelete();
    }

    /**
     * Return the user identifier to JWT authentication.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->id;
    }

    /**
     * Return the custom claims to JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * User permissions
     *
     * @return array
     */
    public function perms()
    {
        return $this->belongsToMany(Permission::class,
            config('entrust.permission_user_table'), 'user_id');
    }

    /**
     * Get all user and role permissions
     * @return array
     */
    public function getPermissionsAttribute()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey = implode('_', [$this->getTable(), $this->$userPrimaryKey]);

        return Cache::tags('entrust')->remember($cacheKey, config('cache.ttl'), function () {
            $permissions = [];

            foreach ($this->cachedRoles() as $role) {
                $permissions = array_merge(
                    $permissions,
                    $role->cachedPermissions()->pluck('name')->toArray()
                );
            }

            $permissions = array_merge($permissions,
                $this->perms->pluck('name')->toArray());

            return $permissions;
        });
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission
     * @param bool $requireAll
     *
     * @return bool
     */
    public function can($permission, $requireAll = false)
    {
        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->can($permName);
                if ($hasPerm && ! $requireAll) {
                    return true;
                } elseif (! $hasPerm && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        } else {
            // Check permissions from role
            foreach ($this->cachedRoles() as $role) {
                foreach ($role->cachedPermissions() as $perm) {
                    if (str_is($permission, $perm->name)) {
                        return true;
                    }
                }
            }

            // Check permissions from user
            foreach ($this->permissions as $perm) {
                if (str_is($permission, $perm)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return the users that is assigned as contacts.
     *
     * @param $query
     * @return mixed
     */
    public function scopeContact($query)
    {
        return $query->where('is_contact', true);
    }

    /**
     * Return the regular users.
     * The users that is not assigned as contacts.
     *
     * @param $query
     * @return mixed
     */
    public function scopeRegular($query)
    {
        return $query->where('is_user', true);
    }

    /**
     * Return the users that is active.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Save the email field in database as null if the value is empty.
     *
     * @param $value
     */
    public function setEmailAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['email'] = null;
        } else {
            $this->attributes['email'] = $value;
        }
    }

    /**
     * Only set the password if the value is not empty.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        if (! empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * Only set the photo if is not null.
     *
     * @param $value
     */
    public function setPhotoAttribute($value)
    {
        if (! empty($value)) {
            $this->attributes['photo'] = $value;
        }
    }

    /**
     * Define the is_active attribute as a integer.
     *
     * @param $value
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = (int) $value;
    }

    /**
     * Check if user is active.
     *
     * @return mixed
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * A user belongs to many companies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_contact', 'contact_id', 'company_id')
            ->with('address');
    }

    /**
     * A user is_contact has an address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function address()
    {
        return $this->hasOne(ContactAddress::class, 'contact_id');
    }

    /**
     * A user is_contact has many phones.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phones()
    {
        return $this->hasMany(ContactPhone::class, 'contact_id');
    }

    /**
     * A user can have many documents (Documents that user has uploaded)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * A user can have many document history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentHistory()
    {
        return $this->hasMany(DocumentHistory::class);
    }

    /**
     * A contact can have one or many documents shared.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sharedDocuments()
    {
        return $this->belongsToMany(Document::class, 'document_contact', 'contact_id', 'document_id');
    }

    /**
     * A user has many user notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notificationsSettings()
    {
        return $this->hasMany(UserNotification::class);
    }
}
