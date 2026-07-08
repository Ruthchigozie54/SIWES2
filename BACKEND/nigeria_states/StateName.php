<?php 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
// It is highly recommended to add the JSON header so the frontend knows how to read it
header('Content-Type: application/json'); 

include 'Data.php';

class StateName {
    private array $data;

    // Pass the data array into the class when you create it
    public function __construct(array $statesData)
    {
        $this->data = $statesData;
    }

    // This function handles the loop and returns the final array
    public function getStateNames(): array 
    {
        $stateNames = [];

        foreach ($this->data as $state) {
            // Check if 'name' exists to prevent warnings, then add it to the array
            if (isset($state['name'])) {
                $stateNames[] = $state['name'];
            }
        }

        return $stateNames;
    }
}


// Create the object and pass the data from data.php into it
$stateNameFetcher = new StateName($statesData);

// Call the function to get the array of names
$names = $stateNameFetcher->getStateNames();

echo json_encode($names);
?>