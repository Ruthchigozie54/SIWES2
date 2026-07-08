<?php 
include 'Data.php';

class Population {
    private array $data;

  public function __construct(array $statesData)
  {
      $this->data = $statesData;
  }    

  public function filter(int $lowest, int $highest){

    $filteredStates = [];

    foreach ($this->data as $state) {
      if($state['population'] >= $lowest && $state['population'] <= $highest){
        $filteredStates[] = [
            'name' => $state['name'],
            'population' => $state['population']
        ];
      }
    }
    echo json_encode($filteredStates);
  }
}

$lowest = (int)($_GET['lowest'] ?? 0);
$highest = (int) ($_GET['highest'] ?? 1000000000); // Default to a very high number if not provided

$populationFilter = new Population($statesData);
$populationFilter->filter($lowest, $highest); // Example range, you can change this as needed


