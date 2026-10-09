<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'score' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ];
    }
}