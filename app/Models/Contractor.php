<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $connection = 'skim';

    protected $table = 'contractors';

    protected $primaryKey = 'ctr_clab_no';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'ctr_clab_no',
        'ctr_comp_name',
    ];

    /**
     * Fetch approved & verified contractors ordered by company name.
     * Returns empty collection if the SKIM database is unreachable (e.g. local dev).
     */
    public static function allSafe(): \Illuminate\Support\Collection
    {
        try {
            return self::select('ctr_clab_no', 'ctr_comp_name')
                ->where('ctr_appstatus', ['3','2'])
                ->orderBy('ctr_comp_name')
                ->get();
        } catch (\Exception) {
            return collect();
        }
    }
}
