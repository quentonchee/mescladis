<?php
require_once __DIR__ . '/../config/database.php';

class Event
{
    private $conn;
    private $table = 'Event';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllFutureWithUserStatus($userId)
    {
        $query = "
            SELECT 
                e.*,
                a.status as userStatus
            FROM " . $this->table . " e
            LEFT JOIN Attendance a ON e.id = a.eventId AND a.userId = :userId
            WHERE e.date >= date('now')
            ORDER BY e.date ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " (id, title, date, location, description, isClosed) VALUES (:id, :title, :date, :location, :description, 0)";
        $stmt = $this->conn->prepare($query);

        $id = uniqid('evt_');
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':date', $data['date']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':description', $data['description']);

        if ($stmt->execute()) {
            return $id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE " . $this->table . " SET title = :title, date = :date, location = :location, description = :description WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':date', $data['date']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':description', $data['description']);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateAttendance($userId, $eventId, $status)
    {
        $event = $this->getById($eventId);
        if (!$event) return ['error' => 'Event not found'];
        if ($event['isClosed']) return ['error' => 'Inscriptions closes'];

        try {
            $this->conn->beginTransaction();

            $checkStmt = $this->conn->prepare("SELECT id FROM Attendance WHERE userId = ? AND eventId = ?");
            $checkStmt->execute([$userId, $eventId]);
            $exists = $checkStmt->fetch();

            if ($exists) {
                $update = $this->conn->prepare("UPDATE Attendance SET status = ?, updatedAt = CURRENT_TIMESTAMP WHERE userId = ? AND eventId = ?");
                $update->execute([$status, $userId, $eventId]);
            } else {
                $insert = $this->conn->prepare("INSERT INTO Attendance (id, userId, eventId, status) VALUES (?, ?, ?, ?)");
                $insert->execute([uniqid('att_'), $userId, $eventId, $status]);
            }

            $historyInit = $this->conn->prepare("INSERT INTO AttendanceHistory (id, userId, eventId, status) VALUES (?, ?, ?, ?)");
            $historyInit->execute([uniqid('hist_'), $userId, $eventId, $status]);

            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['error' => $e->getMessage()];
        }
    }
}
