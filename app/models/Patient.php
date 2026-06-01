<?php

class Patient
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    public function getAll($userId)
    {
        $sql = "SELECT * FROM patients WHERE user_id = :user_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decrypt sensitive fields for each patient
        foreach ($patients as &$patient) {
            $patient = $this->decryptPatient($patient);
        }

        return $patients;
    }

    public function create($data, $userId)
    {
        $sql = "INSERT INTO patients(user_id, name, age, gender, phone, address, diagnosis)
                VALUES(:user_id, :name, :age, :gender, :phone, :address, :diagnosis)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id'  => $userId,
            ':name'    => $data['name'],
            ':age'     => $data['age'],
            ':gender'  => $data['gender'],
            ':phone'   => Encryption::encrypt($data['phone']),    
            ':address' => Encryption::encrypt($data['address']),
            ':diagnosis' => Encryption::encrypt($data['diagnosis'])   
        ]);
    }

    public function findById($id, $userId)
    {
        $sql = "SELECT * FROM patients WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            return false;
        }

        // Decrypt sensitive fields before returning
        return $this->decryptPatient($patient);
    }

    public function update($id, $data, $userId)
    {
        $sql = "UPDATE patients
                SET name = :name, age = :age, gender = :gender,
                    phone = :phone, address = :address, diagnosis = :diagnosis
                WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':name'    => $data['name'],
            ':age'     => $data['age'],
            ':gender'  => $data['gender'],
            ':phone'   => Encryption::encrypt($data['phone']),   
            ':address' => Encryption::encrypt($data['address']), 
            ':diagnosis' => Encryption::encrypt($data['diagnosis']),
            ':id'      => $id,
            ':user_id' => $userId
        ]);
    }

    public function delete($id, $userId)
    {
        $sql = "DELETE FROM patients WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    // Helper: decrypt sensitive fields of a patient record
    private function decryptPatient($patient)
    {
        $patient['phone']   = Encryption::decrypt($patient['phone']);
        $patient['address'] = Encryption::decrypt($patient['address']);
        $patient['diagnosis'] = Encryption::decrypt($patient['diagnosis']);
        return $patient;
    }
}