<?php
// app/Models/ResourceFormat.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceFormat extends Model
{
  protected $fillable = ['resource_id', 'formatable_type', 'formatable_id'];

  public function resource()
  {
    return $this->belongsTo(Resource::class);
  }

  public function formatable()
  {
    return $this->morphTo();
  }
}
