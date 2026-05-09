<?php 

class API {
    private $servername = "brighton";
    private $username = "jmb181_commentuser";
    private $password = "str0ngpassw0rd";
    private $db = "jmb181_VAMuseumComments";
    private $conn = null;
    private $responseCode = null;

    public function __construct() {
        try {
            $this->conn = new mysqli(
                $this->servername,
                $this->username,
                $this->password,
                $this->db
            );
        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            exit;
        }
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'GET') {
            $this->read();
        } else if ($method === 'POST') {
            $this->create();
        } else {
            $this->responseCode = 405;
        }
        http_response_code($this->responseCode);
    }

    public function create() {
        $this->responseCode = 201;

        if (isset($_POST['oid']) && isset($_POST['comment']) && isset($_POST['name']) ) {
            $oid = trim($_POST['oid']);
            $comment = trim($_POST['comment']);
            $name = trim($_POST['name']);
        } else {
            $this->responseCode = 400;
        }

        if (isset($oid) && (strlen($oid) > 32 || !ctype_alnum($oid))) {
            $this->responseCode = 400;
        }

        if (isset($name) && (strlen($name) < 1 || strlen($name) > 64)) {
            $this->responseCode = 400;
        }

        if (isset($comment) && (strlen($comment) < 1)) {
            $this->responseCode = 400;
        }

        if ($this->responseCode == 201) {
            $stmt = $this->conn->prepare("INSERT INTO tComments (oid, name, comment) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $oid, $name, $comment);
            $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(["id" => $stmt->insert_id]);
        } else {
                $this->responseCode = 500;
            }
        }
    }

    public function read() {
        $this->responseCode = 200;

        if (isset($_GET['oid']) && !empty(trim($_GET['oid']))) {
            $oid = trim($_GET['oid']);
        } else {
            $this->responseCode = 400;
        }

        if (isset($oid) && (strlen($oid) > 32 || !ctype_alnum($oid))) {
            $this->responseCode = 400;
        }

        if ($this->responseCode == 200) {
            $stmt = $this->conn->prepare("SELECT * FROM tComments WHERE oid = ? ORDER BY cDate ASC");
            $stmt->bind_param("s", $oid);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $rows = [];
                while ($row = $result->fetch_assoc()) {
                    $rows[] = [
                        "id" => (int)$row['id'],
                        "date" => date('d F Y', strtotime($row['cDate'])),
                        "name" => $row['name'],
                        "comment" => $row['comment']
                    ];
                }
                $response = [
                    "oid" => $oid,
                    "comments" => $rows
                ];
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode($response);
            } else {
                $this->responseCode = 204;
            }
        }
    }
}

$api = new API();
$api->handleRequest();