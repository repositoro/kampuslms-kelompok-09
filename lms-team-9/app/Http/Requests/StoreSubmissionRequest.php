<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'file_url' => 'required|string',
        ];
    }
}