<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;
use Tymon\JWTAuth\JWTAuth;

class Document extends Model
{

    use SearchableTrait, SoftDeletes;

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
            'documents.name' => 10,
        ],
    ];

    /**
     * The attributes that can be assign.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'filename',
        'cycle',
        'validity',
        'note',
        'status',
        'resent_at',
        'resents',
    ];

    /**
     * The attributes that will not be shown in the collections array.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'resent_at'];

    /**
     * Scope pending documents.
     *
     * @param $query
     * @return mixed
     */
    public function scopePending($query)
    {
        return $query->where('status', 2);
    }

    /**
     * Scope opened documents.
     *
     * @param $query
     * @return mixed
     */
    public function scopeOpened($query)
    {
        return $query->whereIn('status', [3, 4]);
    }

    /**
     * Scope expired documents.
     *
     * @param $query
     * @return mixed
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 5);
    }

    /**
     * Set the cycle data as null if the sent value is empty,
     * but if is filled transform in to a Carbon instance.
     *
     * @param $value
     * @return null|string
     */
    public function setCycleAttribute($value)
    {
        if (! empty($value)) {
            $this->attributes['cycle'] = Carbon::createFromFormat('m/Y', $value)->day(1)->hour(0)->minute(0)->second(0);
        } else {
            $this->attributes['cycle'] = null;
        }
    }

    /**
     * Set the validity data as null if the sent value is empty,
     * but if is filled transform in to a Carbon instance.
     *
     * @param $value
     * @return null|string
     */
    public function setValidityAttribute($value)
    {
        if (! empty($value)) {
            $this->attributes['validity'] = Carbon::createFromFormat('d/m/Y', $value)->hour(0)->minute(0)->second(0);
        } else {
            $this->attributes['validity'] = null;
        }
    }

    /**
     * Format the database value of cycle.
     *
     * @param $value
     * @return null|string
     */
    public function getCycleAttribute($value)
    {
        if ($value == null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('m/Y');
        }

        return Carbon::createFromFormat('Y-m-d', $value)->format('m/Y');
    }

    /**
     * Format the database value of validity
     *
     * @param $value
     * @return null|string
     */
    public function getValidityAttribute($value)
    {
        if ($value == null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('d/m/Y');
        }

        return Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y');
    }

    /**
     * Status data as array of values.
     *
     * @return array
     */
    public function getStatusAttribute($value)
    {
        switch ($value) {
            case 1:
            case 2:
                return [
                    'id'    => $value,
                    'name'  => 'Pendente',
                    'label' => 'label-warning',
                ];
            case 3:
                return [
                    'id'    => $value,
                    'name'  => 'Aberto',
                    'label' => 'label-success',
                ];
            case 4:
                return [
                    'id'    => $value,
                    'name'  => 'Vencido',
                    'label' => 'label-danger',
                ];
            case 5:
                return [
                    'id'    => $value,
                    'name'  => 'Pausado',
                    'label' => '',
                ];
        }
    }

    /**
     * Get the filename extension and return the type and icon of the type.
     *
     * @return array
     */
    public function getTypeAttribute()
    {
        $extension = pathinfo($this->filename, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'pdf':
                return [
                    'ext'          => 'pdf',
                    'label'        => 'Documento PDF',
                    'icon'         => 'mdi-file-pdf-box',
                    'visualizable' => true,
                ];
            case 'xls':
                return [
                    'ext'          => 'xls',
                    'label'        => 'Planilha',
                    'icon'         => 'mdi-file-excel-box',
                    'visualizable' => false,
                ];
            case 'csv':
                return [
                    'ext'          => 'csv',
                    'label'        => 'Planilha',
                    'icon'         => 'mdi-file-excel-box',
                    'visualizable' => false,
                ];
            case 'jpg':
                return [
                    'ext'          => 'jpg',
                    'label'        => 'Imagem',
                    'icon'         => 'mdi-image',
                    'visualizable' => true,
                ];
            case 'png':
                return [
                    'ext'          => 'png',
                    'label'        => 'Imagem',
                    'icon'         => 'mdi-image',
                    'visualizable' => true,
                ];
            case 'gif':
                return [
                    'ext'          => 'gif',
                    'label'        => 'Imagem',
                    'icon'         => 'mdi-image',
                    'visualizable' => true,
                ];
            case 'jpeg':
                return [
                    'ext'          => 'jpeg',
                    'label'        => 'Imagem',
                    'icon'         => 'mdi-image',
                    'visualizable' => true,
                ];
            case 'rar':
                return [
                    'ext'          => 'rar',
                    'label'        => 'Arquivo Compacto (RAR)',
                    'icon'         => 'mdi-zip-box',
                    'visualizable' => false,
                ];
            default:
                return [
                    'ext'          => $extension,
                    'label'        => 'Documento de Texto',
                    'icon'         => 'mdi-file-document-box',
                    'visualizable' => false,
                ];
        }
    }

    /**
     * A document belongs to a user. (The user that uploads the document to the system.)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToin the fewest possible words
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A document can have one or more history actions.
     *
     * @return $this
     */
    public function history()
    {
        return $this->hasMany(DocumentHistory::class);
    }

    /**
     * A document belongs to a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * a document can be shared with one or more contact.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'document_contact', 'document_id', 'contact_id');
    }

    /**
     * A document belongs to many dispatches.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dispatches()
    {
        return $this->belongsToMany(Dispatch::class, 'dispatch_document');
    }
}
