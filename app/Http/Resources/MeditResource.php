<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeditResource extends JsonResource
{

    public $status;
    public $message;
    public $code;

    public function __construct($status, $code , $message, $resource){
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
        $this->code = $code;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => $this->status,
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->resource
        ];
    }
}
