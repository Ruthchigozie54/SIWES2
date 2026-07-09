<?php 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

include 'Data.php';

class StateDetail{
  private array $data;

    public function __construct(array $statesData)
    {
        $this->data = $statesData;
    }
  


public function getStateDetail(string $state_name){
    foreach ($this->data as $state) {
        if (strtolower($state['name']) === strtolower($state_name)) {
            return $state;
        }
    }
    return null; // Return null if the state is not found
}

}

$stateInfo = $_GET['name'] ?? '';


$searcher = new StateDetail($statesData);


$state = $searcher->getStateDetail($stateInfo);

// handle the response output
if ($state) {
    echo json_encode($state);
} else {
    echo json_encode(['error' => 'State not found']);
}
?>