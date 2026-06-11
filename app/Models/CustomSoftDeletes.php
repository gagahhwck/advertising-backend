<?php

namespace App\Models;

use App\Events\LoadDataEvent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait CustomSoftDeletes
{
  use SoftDeletes;

  protected function performDeleteOnModel()
  {
    if ($this->forceDeleting) {
      parent::performDeleteOnModel();
    } else {
      $this->deleted_by = Auth::check() ? Auth::user()->username : null;
      $this->deleted_at = $this->freshTimestamp();
      $this->saveQuietly();
    }
    event(new LoadDataEvent);
  }
}
