<?php

# app/Code.php

namespace App;

use Illuminate\Database\Eloquent\Model;

final class Code extends Model {

  protected $hidden = ["id", "updated_at", "created_at"];

}
