<?php 

class API {
    private $servername = "brighton";
    private $username = "jmb181_commentuser";
    private $password = "str0ngpassw0rd";
    private $db = "jmb181_VAMuseumComments";
    public $conn = null;

    public function __construct() {
        $this->conn = new mysqli(
            $this->servername,
            $this->username,
            $this->password,
            $this->db
        );

        if ($this->conn->connect_errno) {
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
        $oid = (trim($_POST['oid']));
        $name = (trim($_POST['name'])) ?? null;
        $comment = (trim($_POST['comment']));

        if (empty($oid) || strlen($oid) > 32 || !ctype_alnum($oid)) { {
            http_response_code(400);
            exit;
        }

        if (!empty($name) && strlen($name) > 64) {
            http_response_code(400);
            exit;
        }
        
        if (empty($comment)) {
            http_response_code(400);
            exit;
        }

        $stmt = $this->mysqli->prepare("INSERT INTO tComments (objectId, name, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $oid, $name, $comment);
        $stmt->execute();
        http_response_code(201);
        exit;
    }
    }  

    public function Read() {
        $oid = (trim($_GET['oid']));

        if (empty($oid) || strlen($oid) > 32 || !ctype_alnum($oid)) { 
            http_response_code(400);
            exit;
        }

        $stmt = $this->mysqli->prepare("SELECT * FROM tComments WHERE objectId = ?");
        $stmt->bind_param("s", $oid);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            http_response_code(200); 
            
        } else {
            http_response_code(204); 
        }
        exit;
    }
}