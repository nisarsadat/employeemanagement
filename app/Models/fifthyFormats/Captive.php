<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\CaptiveDocument;
class Captive extends Model
{
    use HasFactory;

    protected $fillable = [
        'information_id',
        'name',
        'picture',
        'nick_name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'specific_place',
        'street_number',
        'home_number',
        'specific_form',
        'band_distince',
        'now_activity',
        'phone_number',
        'note',
        'Province_addr',
        'coordinates',
        'resource_id',
    ];

    // 🔗 documents relation
    public function documents()
    {
        return $this->hasMany(CaptiveDocument::class , 'captive_id' , 'id');
    }

    // (if not already there)
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
    public function information()
    {
        return $this->belongsTo(\App\Models\Information::class, 'information_id');
    }
    // 🧹 delete files when a captive is deleted
    protected static function booted()
    {
        static::deleting(function (Captive $captive) {
            foreach ($captive->documents as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
            }
        });
    }
}
