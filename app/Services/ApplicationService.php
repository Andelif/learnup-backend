<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApplicationService{
    public function getAllApplications()
    {
        return DB::select("SELECT * FROM applications");
    }

    public function getTutorStats($userId)
    {
        $tutor = DB::select("SELECT TutorID FROM tutors WHERE user_id = ?", [$userId]);
        if (empty($tutor)) {
            return null;
        }

        $tutorId = $tutor[0]->TutorID;
        $query = "
            SELECT 
                (SELECT COUNT(*) FROM applications WHERE tutor_id = ? AND status = 'Applied')) AS applied,
                (SELECT COUNT(*) FROM applications WHERE tutor_id = ? AND status = 'Shortlisted') AS shortlisted,
                (SELECT COUNT(*) FROM applications WHERE tutor_id = ? AND status = 'Confirmed') AS confirmed,
                (SELECT COUNT(*) FROM applications WHERE tutor_id = ? AND status = 'Cancelled') AS cancelled;
        ";

        $results = DB::select($query, [$tutorId, $tutorId, $tutorId, $tutorId]);

        return $results[0];
    }

    public function getLearnerStats($userId)
    {
        $learner = DB::select("SELECT LearnerID FROM learners WHERE user_id = ?", [$userId]);
        if (empty($learner)) {
            return null;
        }

        $learnerId = $learner[0]->LearnerID;
        $query = "
            SELECT 
                (SELECT COUNT(*) FROM applications WHERE learner_id = ? AND status = 'Applied') AS applied,
                (SELECT COUNT(*) FROM applications WHERE learner_id = ? AND status = 'Shortlisted') AS shortlisted,
                (SELECT COUNT(*) FROM applications WHERE learner_id = ? AND status = 'Confirmed') AS confirmed,
                (SELECT COUNT(*) FROM applications WHERE learner_id = ? AND status = 'Cancelled') AS cancelled;
        ";

        $results = DB::select($query, [$learnerId, $learnerId, $learnerId, $learnerId]);

        return $results[0];
    }

    public function createApplication($request)
    {
        $user = Auth::user();
    if (!$user || $user->role !== 'tutor') {
        return ['error' => 'Unauthorized: Only tutors can apply for tuition requests', 'status' => 403];
    }

    // Validate tuition_id
    $tution_id = $request->tution_id;
    $validateTution = DB::select("SELECT COUNT(*) as count FROM tuition_requests WHERE TutionID = ?", [$tution_id]);

    if ($validateTution[0]->count == 0) {
        return ['error' => 'Invalid tuition request', 'status' => 400];
    }

    // Get TutorID from tutors table
    $tutor = DB::select("SELECT TutorID FROM tutors WHERE user_id = ?", [$user->id]);

    if (empty($tutor)) {
        return ['error' => 'Tutor not found', 'status' => 404];
    }

    $tutor_id = $tutor[0]->TutorID;

    // Check if tutor has already applied
    $applicationExists = DB::select("
        SELECT COUNT(*) as count FROM applications 
        WHERE tution_id = ? AND tutor_id = ?", 
        [$tution_id, $tutor_id]
    );

    if ($applicationExists[0]->count > 0) {
        return ['error' => 'You have already applied for this job', 'status' => 400];
    }

    // Get LearnerID from tuition_requests table
    $learner = DB::select("SELECT LearnerID FROM tuition_requests WHERE TutionID = ?", [$tution_id]);

    if (empty($learner)) {
        return ['error' => 'Learner not found for this tuition request', 'status' => 404];
    }

    $learner_id = $learner[0]->LearnerID;

    try {
        // Start a transaction
        DB::beginTransaction();

        // Insert application record using raw SQL
        DB::statement("
            INSERT INTO applications (tution_id, learner_id, tutor_id, matched, status) 
            VALUES (?, ?, ?, ?, ?)", 
            [$tution_id, $learner_id, $tutor_id, 0, 'Applied'] // Use 0 for matched instead of false
        );

        DB::commit();
        return ['message' => 'Application submitted successfully', 'status' => 200];
    } catch (\Exception $e) {
        DB::rollBack();
        return ['error' => 'Failed to apply: ' . $e->getMessage(), 'status' => 500];
    }
    }
}