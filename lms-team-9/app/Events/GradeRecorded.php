<?php

namespace App\Events;

use App\Models\Grade;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GradeRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Grade $grade) {}
}
