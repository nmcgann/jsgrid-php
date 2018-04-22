<?php

include "Client.php";

class ClientRepository {

    protected $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    private function read($row) {
        $result = new Client();
        $result->id = $row["id"];
        $result->name = $row["name"];
        $result->age = $row["age"];
        $result->address = $row["address"];
        $result->married = $row["married"] == 1 ? true : false;
        $result->country_id = $row["country_id"];
        return $result;
    }

    public function getById($id) {
        $sql = "SELECT * FROM clients WHERE id = :id";
        $q = $this->db->prepare($sql);
        $q->bindParam(":id", $id, PDO::PARAM_INT);
        $q->execute();
        $rows = $q->fetchAll();
   
        return !empty($rows)? $this->read($rows[0]) : [];
    }

    public function getAll($filter) {
        $name = "%" . $filter["name"] . "%";
        $address = "%" . $filter["address"] . "%";
        $country_id = $filter["country_id"];

        $page_size = $filter["pageSize"];
        $page_offset = $page_size * ($filter["pageIndex"] - 1);

        $sql = "SELECT count(*) FROM clients WHERE name LIKE :name AND address LIKE :address 
            AND (:country_id = 0 OR country_id = :country_id)";
        $q = $this->db->prepare($sql);
        $q->bindParam(":name", $name);
        $q->bindParam(":address", $address);
        $q->bindParam(":country_id", $country_id, PDO::PARAM_INT);
        $q->execute();
        $total_count = $q->fetchColumn();    
        
        $sql = "SELECT * FROM clients WHERE name LIKE :name AND address LIKE :address 
            AND (:country_id = 0 OR country_id = :country_id) LIMIT :psize OFFSET :poffset";
        $q = $this->db->prepare($sql);
        $q->bindParam(":name", $name);
        $q->bindParam(":address", $address);
        $q->bindParam(":country_id", $country_id, PDO::PARAM_INT);
        $q->bindParam(":psize", $page_size, PDO::PARAM_INT);
        $q->bindParam(":poffset", $page_offset, PDO::PARAM_INT);
        $q->execute();
        $rows = $q->fetchAll();

        $result = array();
        foreach($rows as $row) {
            array_push($result, $this->read($row));
        }
        
        //paged data format for jsgrid
        $result = ["data" => $result, "itemsCount" => $total_count];
              
        return [true, $result];
    }

    public function insert($data) {
        try{
            $sql = "INSERT INTO clients (name, age, address, married, country_id) VALUES (:name, :age, :address, :married, :country_id)";
            $q = $this->db->prepare($sql);
            $q->bindParam(":name", $data["name"]);
            $q->bindParam(":age", $data["age"], PDO::PARAM_INT);
            $q->bindParam(":address", $data["address"]);
            $q->bindParam(":married", $data["married"], PDO::PARAM_INT);
            $q->bindParam(":country_id", $data["country_id"], PDO::PARAM_INT);
            $q->execute();
        }catch(PDOException $ex){
            $err_msg = $ex->getMessage();
            if(preg_match('/constraint violation: 1452/', $err_msg )){
                $err_msg = "Country must be selected";
            }
            return [false, $err_msg];
       }
    
        return [true, $this->getById($this->db->lastInsertId())];
    }

    public function update($data) {
        try{        
            $sql = "UPDATE clients SET name = :name, age = :age, address = :address, married = :married, country_id = :country_id WHERE id = :id";
            $q = $this->db->prepare($sql);
            $q->bindParam(":name", $data["name"]);
            $q->bindParam(":age", $data["age"], PDO::PARAM_INT);
            $q->bindParam(":address", $data["address"]);
            $q->bindParam(":married", $data["married"], PDO::PARAM_INT);
            $q->bindParam(":country_id", $data["country_id"], PDO::PARAM_INT);
            $q->bindParam(":id", $data["id"], PDO::PARAM_INT);
            $result = $q->execute();
        }catch(PDOException $ex){
            $err_msg = $ex->getMessage();
            if(preg_match('/constraint violation: 1452/', $err_msg )){
                $err_msg = "Country must be selected";
            }
            return [false, $err_msg];
        }
        
        return [true, $result]; 
        
    }

    public function remove($id) {
        try{
            $sql = "DELETE FROM clients WHERE id = :id";
            $q = $this->db->prepare($sql);
            $q->bindParam(":id", $id, PDO::PARAM_INT);
            $q->execute();
        }catch(PDOException $ex){
            $err_msg = $ex->getMessage();
            
            return [false, $err_msg];
        }
        
        if($q->rowCount() != 1){
            return [false, "Failed to delete record"];
        }
        return [true, 1];

    }

}

?>