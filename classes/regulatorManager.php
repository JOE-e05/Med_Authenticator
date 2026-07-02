<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/authorization.php';

class RegulatorManager {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    public function getStatusCount($status) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM report WHERE status = :status");
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function getAllReports() {
        try {
            $sql = "SELECT r.*, u.CustomerName
                    FROM report r
                    LEFT JOIN users u ON r.userID = u.customerID
                    ORDER BY r.reported_at DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getInvestigationFeed($search = '', $sourceFilter = '', $statusFilter = '') {
        try {
            $sql = "SELECT r.*, u.CustomerName,
                           v.verification_type, v.result AS verification_result,
                           v.actor_role, v.verified_at AS verification_time
                    FROM report r
                    LEFT JOIN users u ON r.userID = u.customerID
                    LEFT JOIN verification_log v ON r.verification_log_id = v.loginID
                    WHERE 1=1";

            $params = [];

            if ($sourceFilter !== '') {
                $sql .= " AND r.source_type = :sourceFilter";
                $params[':sourceFilter'] = $sourceFilter;
            }

            if ($statusFilter !== '') {
                $sql .= " AND r.status = :statusFilter";
                $params[':statusFilter'] = $statusFilter;
            }

            if ($search !== '') {
                $sql .= " AND (
                            r.batchNumber LIKE :search
                            OR r.description LIKE :search
                            OR u.CustomerName LIKE :search
                            OR COALESCE(v.result, '') LIKE :search
                            OR COALESCE(v.verification_type, '') LIKE :search
                          )";
                $params[':search'] = "%$search%";
            }

            $sql .= " ORDER BY r.reported_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $sql = "SELECT r.*, u.CustomerName
                    FROM report r
                    LEFT JOIN users u ON r.userID = u.customerID
                    WHERE 1=1";

            $params = [];

            if ($statusFilter !== '') {
                $sql .= " AND r.status = :statusFilter";
                $params[':statusFilter'] = $statusFilter;
            }

            if ($search !== '') {
                $sql .= " AND (r.batchNumber LIKE :search OR r.description LIKE :search OR u.CustomerName LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $sql .= " ORDER BY r.reported_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }
    }

    public function updateReport($reportId, $status, $adminReview, $regulatorId, $actorRole = 'Regulator') {
        authz_require_role($actorRole);

        $sql = "UPDATE report 
                SET status = :status, admin_review = :adminReview, reviewed_by = :regulatorId 
                WHERE reportID = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':adminReview' => $adminReview,
            ':regulatorId' => $regulatorId,
            ':id' => $reportId
        ]);
    }

    public function getVerificationSummary() {
        $summary = [
            'genuine' => 0,
            'counterfeit' => 0,
            'total' => 0
        ];

        try {
            $stmt = $this->pdo->query("SELECT result, COUNT(*) AS total FROM verification_log GROUP BY result");
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $result = strtolower((string) $row['result']);
                $count = (int) $row['total'];
                if ($result === 'genuine' || $result === 'authentic') {
                    $summary['genuine'] += $count;
                } else {
                    $summary['counterfeit'] += $count;
                }
            }

            $summary['total'] = $summary['genuine'] + $summary['counterfeit'];
        } catch (PDOException $e) {
            // Keep zero values if table/columns are not ready.
        }

        return $summary;
    }
}
?>