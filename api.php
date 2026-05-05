<?php 

class API {
    private $servername = "brighton";
    private $username = "jmb181_commentuser";
    private $password = "str0ngpassw0rd";
    private $db = "jmb181_VAMuseumComments";
    public $conn = null;

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

    public function HandleRequest() {
        header("Content-Type: application/json; charset=UTF-8");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'GET') {
            $this->Read();
        }

        else if ($method === 'POST') {
           $this->Create();
        }

        else {
            http_response_code(405);
            exit;
        }
    }

    public function Create() {
        $oid = isset($_POST['oid']) ? htmlspecialchars(strip_tags(trim($_POST['oid']))) : null;
        $name = isset($_POST['name']) ? htmlspecialchars(strip_tags(trim($_POST['name']))) : null;
        $comment = isset($_POST['comment']) ? htmlspecialchars(strip_tags(trim($_POST['comment']))) : null;

        if (empty($oid) || strlen($oid) > 32 || !ctype_alnum($oid)) { 
            http_response_code(400);
            exit;
        }

        if (!empty($name) && (strlen($name) < 1 || strlen($name) > 64)) {
            http_response_code(400);
            exit;
        }
        
        if (empty($comment)) {
            http_response_code(400);
            exit;
        }

        $stmt = $this->conn->prepare("INSERT INTO tComments (objectId, name, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $oid, $name, $comment);
        $stmt->execute();
        http_response_code(201);
        exit;
    }
      

    public function Read() {
        $oid = isset($_GET['oid']) ? htmlspecialchars(strip_tags(trim($_GET['oid']))) : null;

        if (empty($oid) || strlen($oid) > 32 || !ctype_alnum($oid)) { 
            http_response_code(400);
            exit;
        }

        $stmt = $this->conn->prepare("SELECT * FROM tComments WHERE objectId = ?");
        $stmt->bind_param("s", $oid);
        $stmt->execute();

        $result = $stmt->get_result(); // get a result object you can iterate

        if ($result->num_rows > 0) {
            http_response_code(200);
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        } else {
            http_response_code(204);
        }
        exit;
}
}

$api = new API();
$api->HandleRequest();