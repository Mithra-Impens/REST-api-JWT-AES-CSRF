<?php

class PatientController
{
    public function index()
    {
        $userId = $_REQUEST['user']['user_id'];

        $patientModel = new Patient();

        $patients = $patientModel->getAll($userId);

        Response::json(
            true,
            "Patients fetched successfully",
            $patients
        );
    }

    public function show($id)
    {
        $userId = $_REQUEST['user']['user_id'];

        $patientModel = new Patient();

        $patient = $patientModel->findById($id, $userId);

        if (!$patient) {
            Response::json(
                false,
                "Patient not found or unauthorized",
                [],
                404
            );
        }

        Response::json(
            true,
            "Patient fetched successfully",
            $patient
        );
    }

    public function store()
    {
        $userId = $_REQUEST['user']['user_id'];
        $data = $_REQUEST['body'];

        if (empty($data['name']) || empty($data['age']) || empty($data['gender' ]) || empty($data['phone']) || empty($data['address']) ||
        empty($data['diagnosis'])) {
            Response::json(false, "Required fields missing", [], 400);
        }

        $patientModel = new Patient();
        $patientModel->create($data, $userId); 
        Response::json(true, "Patient created successfully");
    }

    public function update($id)
    {
        $userId = $_REQUEST['user']['user_id'];
        $data = $_REQUEST['body'];

        if (empty($data['name']) || empty($data['age']) || empty($data['gender']) || empty($data['gender' ]) || empty($data['phone']) || empty($data['address']) ||
        empty($data['diagnosis'])) {
            Response::json(false, "Required fields missing", [], 400);
        }

        $patientModel = new Patient();
        $patient = $patientModel->findById($id, $userId); 

        if (!$patient) {
            Response::json(false, "Patient not found", [], 404);
        }

        $patientModel->update($id, $data, $userId);
        Response::json(true, "Patient updated successfully");
    }

    public function delete($id)
    {
        $userId = $_REQUEST['user']['user_id'];
        $patientModel = new Patient();
        $patient = $patientModel->findById($id, $userId); 

        if (!$patient) {
            Response::json(false, "Patient not found", [], 404);
        }

        $patientModel->delete($id, $userId);
        Response::json(true, "Patient deleted successfully");
    }
}