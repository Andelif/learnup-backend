<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $primaryKey = 'ApplicationID';
    public $incrementing = true;

    // Create a new application
    public static function createApplication($data)
    {
        DB::insert("INSERT INTO applications (tution_id, learner_id, tutor_id, matched) VALUES (?, ?, ?, ?)", [
            $data['tution_id'],
            $data['learner_id'],
            $data['tutor_id'],
            false
        ]);
        return DB::getPdo()->lastInsertId();
    }

    // Fetch application by ID
    public static function findById($application_id)
    {
        return DB::select("SELECT * FROM applications WHERE ApplicationID = ?", [$application_id])[0] ?? null;
    }

    // Get all applications for a tutor
    public static function getApplicationsByTutor($tutor_id)
    {
        return DB::select("SELECT * FROM applications WHERE tutor_id = ?", [$tutor_id]);
    }
}