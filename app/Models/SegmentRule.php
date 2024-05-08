<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegmentRule extends Model {

    protected $guarded = [];

    public function getRules() {
        return is_string($this->rules) ? json_decode($this->rules, true) : $this->rules;
    }

    public function getNotRules() {
        return is_string($this->not_rules) ? json_decode($this->not_rules, true) : $this->not_rules;
    }

    public function getTopRules() {
        return is_string($this->top_rules) ? json_decode($this->top_rules, true) : $this->top_rules;
    }
}
