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
	private $debtGraph = [];
	private $resultMatrix = [];
  private $optimizedResultMatrix = [];

	public function __construct($bills){

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
        $this->populateDebtGraph();
				foreach ($this->debtGraph as $nodeItem) {
						$this->resultMatrix[$nodeItem->getName()] = $nodeItem->getCreditorList();
				}

				//return $this->resultMatrix;
				$this->optimizeTransactions();
				//var_dump($this->optimizedResultMatrix);
				return $this->optimizedResultMatrix;
    }

	private function populateDebtGraph(){
			foreach($this->bills as $bill_item){
				$sizeOfAttendees = sizeof($bill_item->attendees);
				$amountToPay = $bill_item -> price / $sizeOfAttendees;
				$creditor = $bill_item -> paid_by;
				$currentNode = $this->getNode($creditor);
				 foreach ( $bill_item->attendees as $debtor) {
						if($currentNode->isEqual($debtor) != true){
							$debtorNode = $this->getNode($debtor);
							$creditorName = $currentNode->getName();
							$debtAmountOfCreditor = $currentNode->getDebt($debtor);
							if($debtAmountOfCreditor > 0){
								if($debtAmountOfCreditor < $amountToPay){
									$currentNode->removeDebt($debtorNode, $debtAmountOfCreditor);
									$debtorNode->setCreditor($currentNode, $amountToPay-$debtAmountOfCreditor);
								}else{
									$currentNode->removeDebt($debtorNode,  $amountToPay);
								}
							}else{
								$debtorNode->setCreditor($currentNode, $amountToPay);
							}
						}
					}
			}
		}
	private function getNode($creditor){
			$index = $this->getIndexOfNode($this->debtGraph, $creditor);
			$currentNode;
			if($index == -1){
				$currentNode = new Node($creditor);
				$currentNode->loan(0);
				$currentNode->setDebtAmount(0);
				array_push($this->debtGraph, $currentNode);
			}else{
				$currentNode = $this->debtGraph[$index];
			}
			return $currentNode;
		}
	private function getIndexOfNode(array $graph, $name) {
	    for ($i = 0; $i < sizeof($graph); $i++) {
				  $item = $graph[$i];
	        if($item->isEqual($name)) return $i;
	    }
	    return -1;
   }
	private function optimizeTransactions(){
		$maxBalanceNode = $this->finMaxBalanceNode($this->debtGraph);
		$minBalanceNode = $this->findMinBalanceNode($this->debtGraph);

		if($maxBalanceNode == null && $minBalanceNode == null)
    return ;
		else if($maxBalanceNode->isEqual($minBalanceNode))
		return ;
		else{
			$min = $this->getMin(-($minBalanceNode->getBalance()), $maxBalanceNode->getBalance());
      $minBalanceNode->removeDebt($maxBalanceNode, $min);
			$this->optimizedResultMatrix = $this->populateOptimizedDebtMatrix($minBalanceNode, $maxBalanceNode, $min, $this->optimizedResultMatrix);
			$this->optimizeTransactions();
		}
	}
	private function finMaxBalanceNode($graph){
		$max = 0;
		$max ;
		foreach ($graph as $node) {
			if($node->getBalance() > $max){
				$max = $node->getBalance();
				$maxNode =  $node;
			}
		}
		return $maxNode;
	}
	private function findMinBalanceNode($graph){
		$min = 0;
		$minNode;
		foreach ($graph as $node) {
			if($node->getBalance() < $min){
				$min = $node->getBalance();
				$minNode =  $node;
			}
		}
		return $minNode;
	}
	private function populateOptimizedDebtMatrix($debtor,$creditor,$amount, $matrix){
	    $creditorList = $matrix[$debtor->getName()];
			$creditorList[$creditor->getName()] += $amount ;
			$matrix[$debtor->getName()] = $creditorList;
			return $matrix;
		}
	private function getMin($value1, $value2){
		return ($value1 < $value2) ? $value1 : $value2;
	}


}

class Node{
	public $name;
	public $creditAmount;
	public $debtAmount;
	public $creditorList = [];

	public function __construct($name){
		$this->name = $name;
	}
	public function getName(){
		return $this->name;
	}
	public function getCreditorList(){
		return $this->creditorList;
	}
	public function setDebtAmount($debtAmount){
		 $this->debtAmount = $debtAmount;
	}
	public function setCreditor($creditor, $amountToPay){
		$creditor->loan($amountToPay);
		$this->creditorList[$creditor->getName()] += $amountToPay;
	  $this->debtAmount += $amountToPay;
	}
	/*Get debt amount to the debtor*/
	public function getDebt($debtor){
	 return	$this->creditorList[$debtor];
	}
	public function getBalance(){
		$balanceVal = $this->creditAmount - $this->debtAmount;
		if(round($balanceVal) == 0) return 0;
		else return $balanceVal;
	}
	/* To remove already given credit of mine*/
	public function removeDebt($debtorNode, $debtToRemove){
		$this->debtAmount -= $debtToRemove;
		$debtorNode->loan(-$debtToRemove);
		$this->creditorList[$debtorNode->name] -= $debtToRemove;
		if($this->creditorList[$debtorNode->name] == 0) unset($this->creditorList[$debtorNode->name]);
	}
	/*Give credit to someone*/
	public function loan($creditAmount){
		$this->creditAmount += $creditAmount;
	}
	public function toString(){
		echo "Name: $this->name, Debt Amount: $this->debtAmount , Credit Amount: $this->creditAmount".PHP_EOL;
	}
	public function isEqual($nameToCompare){
		return $this->name == $nameToCompare;
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
