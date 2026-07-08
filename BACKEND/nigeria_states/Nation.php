<?php 
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json'); 

include "INation.php";


class Nation implements INation {
  private $data;
  private $targetResource;
  
  function __construct()
  {
    $this->initialize();
  }

  public function getCapital(){
    return "The capital is ......";
  }

  public function initialize(){
    include "Data.php";
    $this->data = $statesData;
  }

  // UPDATE: Added a parameter to accept the resource the user is looking for
  public function search($targetResource){
    $results = [];

    // Loop through the states data
    foreach($this->data as $state){
      // Check if the state has the requested mineral resource
      $resources = $state['minerals'] ?? [];

      if (is_array($resources) && in_array(strtolower( $targetResource), array_map('strtolower', $resources))) {
        $results[] = $state['name'];
      }
    }

    return $results;
  }

  public function getStates(){
    $stateName = array_column($this->data, 'name');
    return $stateName;
  }
}


$nigeria = new Nation();

// Let's assume the user searched for 'gold' via a URL query parameter, e.g., ?resource=gold
// We use a fallback default here for safety
$searchTerm = $_GET['resource'] ?? 'gold'; 

// Call the updated search function
$matchingStates = $nigeria->search($searchTerm);

// Output the result as a JSON array of matching states
echo json_encode($matchingStates);
?>