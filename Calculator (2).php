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

class Calculator{

	private $bills = [];

	public function __construct(array $bills){
		foreach($bills as $bill_item){

			$this->bills[] = new BillItem($bill_item);
		}
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
		 $resultMatrix = ['thijs' => [], 'danny' => [], 'stefan' => [], 'den' => []]; // debtMatrix
		 $lessTransactionResultMatrix = []; // optimizedMatrix

		 $resultMatrix = $this->doSharingAmoungParticipant($resultMatrix); // calculateDebts
     $lessTransactionResultMatrix = $this->eliminateUnnecessaryTransaction($resultMatrix); // optimizeTransactions
     //print_r($resultMatrix);

		 return $lessTransactionResultMatrix;
	}

private function eliminateUnnecessaryTransaction($filteredResultMatrix){ // optimizeTransactions
		foreach ($filteredResultMatrix as $currentPersonName => $whomCurrentPersonOwe) {
			if(is_array($whomCurrentPersonOwe) && sizeof($whomCurrentPersonOwe) > 0){
				foreach ($whomCurrentPersonOwe as $thePersonNameWhoCurrentPersonOwe => $valueOfOweCurrentPersonToThePerson) {
					if(is_array($filteredResultMatrix[$thePersonNameWhoCurrentPersonOwe]) && sizeof($filteredResultMatrix[$thePersonNameWhoCurrentPersonOwe]) > 0){
							$peopleWhoCurrentPersonIOweOweTo = $filteredResultMatrix[$thePersonNameWhoCurrentPersonOwe];
							$whomToIOwe = $this->findWhomToOweForASpecificPeople($filteredResultMatrix,$currentPersonName);
							$findPeopleBothOfUsOweTo = array_intersect_key($whomToIOwe, $peopleWhoCurrentPersonIOweOweTo);
						 if(sizeof($findPeopleBothOfUsOweTo) > 0){
						 	$filteredResultMatrix = $this->eliminateTransactionBetweenThree($filteredResultMatrix,$currentPersonName,$thePersonNameWhoCurrentPersonOwe,$findPeopleBothOfUsOweTo);
						}
					}
				}
			}
     }
			$filteredResultMatrix = $this->eliminateMoreIfNeeded($filteredResultMatrix);
			return $filteredResultMatrix;

  }

	private function eliminateMoreIfNeeded($filteredResultMatrix){ // reorderTransactions
		$listOfPeopleWhoHasTransaction = array_filter($filteredResultMatrix, function($v, $k) {
    return  sizeof($v) > 0;}, ARRAY_FILTER_USE_BOTH);
    $listOfPeopleToCompare  = array_keys($listOfPeopleWhoHasTransaction);

    $peopleNameWhoHasTheMaxNumTransaction = $this->findPeopleWhoTheMaxTransaction($filteredResultMatrix,$listOfPeopleToCompare);
		$listOfPeopleToCompare = array_filter($listOfPeopleToCompare, function($v, $k) {return  $k != $peopleNameWhoHasTheMaxNumTransaction;}, ARRAY_FILTER_USE_BOTH);

		$listOfPeopleWhoMakTransactionOweTo = $filteredResultMatrix[$peopleNameWhoHasTheMaxNumTransaction];

		foreach ($listOfPeopleToCompare as $peopleNameWhoHasTransaction) {
			$findPeopleBothOfUsOweTo = array_intersect_key($filteredResultMatrix[$peopleNameWhoHasTransaction], $listOfPeopleWhoMakTransactionOweTo);
			foreach ($findPeopleBothOfUsOweTo as $peopleNameWeBothOweTo => $value) {
				$howMuchMaxTransactionOwe = $this->howMuchIOwe($filteredResultMatrix, $peopleNameWhoHasTheMaxNumTransaction, $peopleNameWeBothOweTo);
				$filteredResultMatrix[$peopleNameWhoHasTheMaxNumTransaction][$peopleNameWeBothOweTo] = 	$howMuchMaxTransactionOwe + 	$value ;
				$filteredResultMatrix[$peopleNameWhoHasTransaction][$peopleNameWhoHasTheMaxNumTransaction] += $value;
				unset(	$filteredResultMatrix[$peopleNameWhoHasTransaction][$peopleNameWeBothOweTo]);
			}

		}
    return $filteredResultMatrix;
	}


	private function findPeopleWhoTheMaxTransaction($filteredResultMatrix, $listOfPeopleToCompare){
    $countOfTransaction = 0;
		foreach ($listOfPeopleToCompare as $peopleName) {
			$transactionsForThisPeople = $filteredResultMatrix[$peopleName];
			$sizeOfTransactions = sizeof($transactionsForThisPeople) ;
			if($sizeOfTransactions > $countOfTransaction){
				$peopleNameWhoHasTheMaxNumTransaction = $peopleName;
				$countOfTransaction = $sizeOfTransactions;
			}
		}
		return $peopleNameWhoHasTheMaxNumTransaction;
	}

	private function doSharingAmoungParticipant($resultMatrix){
			foreach($this->bills as $bill_item){
				$sizeOfAttendees = sizeof($bill_item->attendees);
				$amountToPay = $bill_item -> price / $sizeOfAttendees;
				foreach ( $bill_item->attendees as $key => $value) {
					$toWhom = $bill_item -> paid_by;
					if($toWhom != $value){
						$currentDebtOfToWhom = $resultMatrix[$toWhom][$value];
						if($currentDebtOfToWhom > 0){
							if($amountToPay < $currentDebtOfToWhom){
								unset($resultMatrix[$value][$toWhom]);
								$resultMatrix[$toWhom][$value] = $currentDebtOfToWhom - $amountToPay;
							}else{
								unset($resultMatrix[$toWhom][$value]);
								$resultMatrix[$value][$toWhom] = $amountToPay - $currentDebtOfToWhom;
							}
						}else{
							 $resultMatrix[$value][$toWhom] += $amountToPay;
						}
					}
				}
			}
			return $resultMatrix;
		}

		private function eliminateTransactionBetweenThree($filteredResultMatrix,$currentPersonName,$thePersonNameWhoCurrentPersonOwe,$findPeopleBothOfUsOweTo){
			$peopleNameArray = array_keys($findPeopleBothOfUsOweTo);
			foreach ($peopleNameArray as $name) {

				$amountOfIOwe = $this->howMuchIOwe($filteredResultMatrix, $currentPersonName,$thePersonNameWhoCurrentPersonOwe);
					if($amountOfIOwe > 0){
					$amountOfOweOfIOwe = $this->howMuchIOwe($filteredResultMatrix,$thePersonNameWhoCurrentPersonOwe,$name);
					$amountOfMe = $this->howMuchIOwe($filteredResultMatrix,$currentPersonName,$name);
						if($amountOfIOwe > $amountOfOweOfIOwe){
						  unset($filteredResultMatrix[$thePersonNameWhoCurrentPersonOwe][$name]);
						  $filteredResultMatrix[$currentPersonName][$thePersonNameWhoCurrentPersonOwe] = $amountOfIOwe - $amountOfOweOfIOwe;
						  $filteredResultMatrix[$currentPersonName][$name] = $amountOfMe +  $amountOfOweOfIOwe;
						}else{
						  $filteredResultMatrix[$thePersonNameWhoCurrentPersonOwe][$name] =  $amountOfOweOfIOwe - $amountOfIOwe;
						  unset($filteredResultMatrix[$currentPersonName][$thePersonNameWhoCurrentPersonOwe]);
						  $filteredResultMatrix[$currentPersonName][$name] = $amountOfMe + $amountOfIOwe;
					 }
			    }
	 	 }
		return $filteredResultMatrix;
	}

		private function findWhomToOweForASpecificPeople($matrixToBeSearched, $peopleName){ // findPeopleIOwe
			return $matrixToBeSearched[$peopleName];
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

	}
}
?>
