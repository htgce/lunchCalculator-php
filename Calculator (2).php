<?php
$lines = [
	'40.00 Thijs Danny,Danny,Thijs,Stefan,Den',
	'45.00 Danny Danny,Thijs,Stefan,Den',
	 '36.00 Stefan Danny,Thijs,Stefan',
	 '40.00 Stefan Danny,Thijs,stefan,Den',
	 '40.00 Danny Danny,Thijs,Stefan,Den',
	 '12.00 Stefan Thijs,Stefan,Den',
	 '44.00 Danny Danny,Thijs,Stefan,Den',
	 '42.40 Den Danny,Stefan,Den,Den',
	 '40.00 danny Danny,Thijs,Stefan,Den',
	 '50.40 Thijs Danny,Thijs,Den',
	 '48.00 Den Danny,thijs,Stefan,Den',
	 '84.00 Thijs Thijs,Stefan,den'
];

$Calculator = new Calculator($lines);
$Calculator->printBill();

//print_r($lines) ;

class Calculator{

	private $bills = [];

	public function __construct(array $bills){
		foreach($bills as $bill_item){

			$this->bills[] = new BillItem($bill_item);
		}
	//	print_r($this->bills); ;
	}

	public function printBill(){
		$payout = $this->calculate();

		foreach($payout as $debtor => $lines){
			$debtor = ucfirst($debtor);

			foreach($lines as $creditor => $amount){

				$amount = number_format($amount, 2);
				$creditor = ucfirst($creditor);
				echo "$debtor pays $creditor $amount" . PHP_EOL;
			}
		}
	}

    private function calculate()
    {
        // Implement me!
				 $resultMatrix = ["thijs" => [], "danny" => [], "stefan" => [], "den" => []];
         $filteredResultMatrix = [];
				 $lessTransactionResultMatrix = [];
				 		foreach($this->bills as $bill_item){
							$sizeOfAttendees = sizeof($bill_item->attendees);
							$amountToPay = $bill_item -> price / $sizeOfAttendees;
							foreach ( $bill_item->attendees as $key => $value) {
								$toWhom = $bill_item -> paid_by;
								if($toWhom != $value){
									$currentDebtOfToWhom = $resultMatrix[$toWhom][$value];
									if($currentDebtOfToWhom > 0){
										if($amountToPay < $currentDebtOfToWhom){
											$resultMatrix[$value][$toWhom] = 0;
											$resultMatrix[$toWhom][$value] = $currentDebtOfToWhom - $amountToPay;
										}else{
											$resultMatrix[$toWhom][$value] = 0;
											$resultMatrix[$value][$toWhom] = $amountToPay - $currentDebtOfToWhom;
										}
									}else{
											$resultMatrix[$value][$toWhom] += $amountToPay;
									}
								}
							}
						}
						foreach ($resultMatrix as $key => $value) {
							$filteredResultMatrix[$key] = array_filter($value, function ($var) {
							    return  $var != 0.00 ||  $var != 0;
							});
					}

					foreach ($filteredResultMatrix as $key => $value) {
						//echo "current key $key";
						// key :stefan
						// value : this, danny, den
						if(is_array($value) && sizeof($value) > 0){
							foreach ($value as $internalKey => $internalValue) {
								//$internalKey : this
								//$internalValue :
								//this kime borclu
								if(is_array($filteredResultMatrix[$internalKey]) && sizeof($filteredResultMatrix[$internalKey]) > 0){
											$whomToOweWhoIOwe = $this->findWhomToOweForASpecificPeople($filteredResultMatrix,$internalKey);
											//stefanda bunlara borclu mu
											if($key == "stefan" && $internalKey == "thijs"){
												//print_r($whomToOweWhoIOwe);
											  foreach ($whomToOweWhoIOwe as $peopleIndex => $peopleNameValue) {
													//print_r($peopleNameValue);

													foreach ($peopleNameValue as $peopleNameWhomToOweByIOwe => $valueOfOweByIOwe) {
														// code... danny ->45
														//dannye ben de borclu muyum
														//print_r($value);
														$filterArray [] =$peopleNameWhomToOweByIOwe;
														$filtered = array_filter($value,  function ($key) use ($filterArray) {  return in_array($key, $filterArray);},
														ARRAY_FILTER_USE_KEY
													);
													  //echo "$peopleNameWhomToOweByIOwe";
														//print_r($filtered);
														if(sizeof($filtered) > 0 ) {
															//echo $peopleNameWhomToOweByIOwe; //danny
															//stefanın thise borcunun miktarı
															 $amountOfIOwe = $this->howMuchIOwe($filteredResultMatrix, $key,$internalKey); //stefan-this
															 $amountOfOweOfIOwe = $this->howMuchIOwe($filteredResultMatrix,$internalKey,$peopleNameWhomToOweByIOwe); //this-danny
															 $amountOfOweOfMeToAllWeOwe = $this->howMuchIOwe($filteredResultMatrix,$key,$peopleNameWhomToOweByIOwe);//stefan-danny
															echo "Amounts : ";
															echo "$amountOfIOwe , $amountOfOweOfIOwe, $amountOfOweOfMeToAllWeOwe  ";
															if($amountOfIOwe > $amountOfOweOfIOwe){
																$filteredResultMatrix[$internalKey][$peopleNameWhomToOweByIOwe] = 0;
																$filteredResultMatrix[$key][$internalKey] = $amountOfIOwe - $amountOfOweOfIOwe;
																$filteredResultMatrix[$key][$peopleNameWhomToOweByIOwe] = $amountOfOweOfMeToAllWeOwe +  $amountOfIOwe;
																
															}else{

															}

														}
													}

											  }
											}
										}
								}
							}
						}

						echo 		"Result".PHP_EOL;
					  //print_r($filteredResultMatrix);

						//return $filteredResultMatrix;
	}


		private function findWhoOweToASpecificPeople($matrixToBeSearched, $peopleName){
      $filteredMatrixBySpecificPeople =[];
			foreach ($matrixToBeSearched as $key => $value) {
				foreach ($value as $name => $val) {
					if($name == $peopleName){
						$filteredMatrixBySpecificPeople[] = $key;
					}
				}
			}
			return $filteredMatrixBySpecificPeople;
		}

		private function findWhomToOweForASpecificPeople($matrixToBeSearched, $peopleName){
			$filteredMatrixBySpecificPeople =[];
			$filteredMatrixBySpecificPeople[] = $matrixToBeSearched[$peopleName];
			return $filteredMatrixBySpecificPeople;
		}

		private function howMuchIOwe($matrix, $nameOfDebtor, $nameOfIOwe){
		  return	$matrix[$nameOfDebtor][$nameOfIOwe];
		}

}


class BillItem{

	public $price;
	public $paid_by;
	public $attendees = [];

	public function __construct($row){

		$data = explode(' ', $row);
		$this->price = (float) $data[0];
		$this->paid_by = strtolower($data[1]);
		foreach(explode(',', $data[2]) as $debtor){
			$this->attendees[] = strtolower($debtor);
		}
		//print_r($this->attendees);

	}
}
?>
