<?php 

class API {
    private $servername = "brighton"; // The connection to the SQL database
    private $username = "jmb181_commentuser";
    private $password = "str0ngpassw0rd";
    private $db = "jmb181_VAMuseumComments";
    public $conn = null;
    public $responseCode = null;

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
            $this->Read();
        }

        else if ($method === 'POST') {
           $this->Create();
        }

        else {
            $this->responseCode = 405;
        }

        http_response_code($this->responseCode);

    }

    public function create() {

        $this->responseCode = 201; 

        if (isset($_POST['oid']) && isset($_POST['comment'])) {
            $oid = trim($_POST['oid']);
            $comment = trim($_POST['comment']);
        }
        else {
            $this->responseCode = 400;
        }

        if (isset($_POST['name'])) {
            $name = trim($_POST['name']);
        }

        if (strlen($oid) > 32 || !ctype_alnum($oid)) { 
            $this->responseCode = 400;
        }

        if (strlen($name) < 1 || strlen($name) > 64) {
            $this->responseCode = 400;
        }

        if ($this->responseCode == 201) {

            $stmt = $this->conn->prepare("INSERT INTO tComments (objectId, name, comment) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $oid, $name, $comment);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode($oid);
            } else {
                $this->responseCode = 500;
            }

        };
    }
      

    public function read() {

        $responseCode = 200;

        if (isset($_POST['oid'])) {
            $oid = trim($_POST['oid']);
        }

        else {
            $responseCode = 400;
        }

        if (strlen($oid) > 32 || !ctype_alnum($oid)) { 
            $responseCode = 400;
        }
        if ($responseCode == 200) {
            $stmt = $this->conn->prepare("SELECT * FROM tComments WHERE objectId = ?");
            $stmt->bind_param("s", $oid);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $rows = [];
                while ($row = $result->fetch_assoc()) {
                    $row['cDate'] = date('d F Y', strtotime($row['cDate']));
                    $rows[] = $row;
                }
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode($rows);
            } else {
                $responseCode = 204;
            }
        }   
        }
}

$api = new API();
$api->HandleRequest();