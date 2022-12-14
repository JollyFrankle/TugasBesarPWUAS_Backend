<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    public $code;
    public $message;

    public function __construct($code, $message = null, $resource = null)
    {
        $this->code = $code;
        $this->message = $message;
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->resource,
        ];
    }

    /**
     * Response code
     */
    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->code);
    }
}
