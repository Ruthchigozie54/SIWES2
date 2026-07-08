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
// get the query parameter from the URL
$stateInfo = $_GET['name'] ?? '';

// instantiate the class, passing the $statesData array to the constructor
$searcher = new StateDetail($statesData);

// call the function inside the class to get the state details
$state = $searcher->getStateDetail($stateInfo);

// handle the response output
if ($state) {
    echo json_encode($state);
} else {
    echo json_encode(['error' => 'State not found']);
}
?>