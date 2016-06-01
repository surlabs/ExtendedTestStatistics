<?php

/**
 * Class ilExteEvalTest
 */
abstract class ilExteEvalTest extends ilExteEvalBase
{

	/**
	 * This evaluation is for tests
	 *
	 * @return bool
	 */
	final public static function _isTestEvaluation()
	{
		return true;
	}

	/**
	 * Calculate and get the single value for a test (to be overwritten)
	 *
	 * @return ilExteStatValue
	 */
	public function calculateValue()
	{
		return new ilExteStatValue;
	}

	/**
	 * Calculate the details for a test (to be overwritten)
	 *
	 * @return ilExteStatDetails[]
	 */
	public function calculateDetails()
	{
		return array();
	}

    ##################################
    # region methods for child classes
    ##################################

    /**
     * Get the mean of reached points
     * @return ilExteStatValue|null
     */
    protected function getMeanOfReachedPoints()
	{
		$mean = $this->data->getCachedData("ilExteEvalTest::getMeanOfReachedPoints");

		if (!isset($mean)) {

			$mean = new ilExteStatValue;
			$basic_test_values = $this->data->getBasicTestValues();
			$scoring_sum = 0;

			//Total attemps evaluated
			$total_attempts = $basic_test_values["tst_eval_total_persons"]->value;

			//Sum all current reached points
			$data = $this->data->getAllParticipants();
			foreach ($data as $attemp) {
				$scoring_sum += (float)$attemp->current_reached_points;
			}

			//Returns the mean
			$mean->type = ilExteStatValue::TYPE_NUMBER;
			$mean->value = $scoring_sum / $total_attempts;
			$mean->precision = 4;

			$this->data->setCachedData("ilExteEvalTest::getMeanOfReachedPoints", $mean);
		}

		return $mean;
	}

    /**
     * Get the standard deviation of test results
     * @return ilExteStatValue|null
     */
	protected function getStandardDeviationOfTestResults()
	{
		$standard_deviation = $this->data->getCachedData("ilExteEvalTest::getStandardDeviationOfTestResults");
		if (!isset($standard_deviation)) {

			$standard_deviation = new ilExteStatValue;

			//Needed values
			$data = $this->data->getAllParticipants();
			$mean = $this->getMeanOfReachedPoints();
			$sum_sq_diff = 0;

            $value_data = array();

			//If more than one participant, then calculate.
			if (count($data) > 1) {
				//Fetch the sum of squared differences between total score and it's mean
				$sum_sq_diff = $this->sumOfPowersOfDifferenceToMean($value_data, $mean->value, 2);

				//Calculate Standard deviation
				$std_deviation = sqrt($sum_sq_diff / (count($data) - 1));

				$standard_deviation->type = ilExteStatValue::TYPE_NUMBER;
				$standard_deviation->value = $std_deviation;
				$standard_deviation->precision = 4;

			} else {
				$std_deviation = $this->txt("only_one_participant");

				$standard_deviation->type = ilExteStatValue::TYPE_TEXT;
				$standard_deviation->comment = $std_deviation;
				$standard_deviation->alert = ilExteStatValue::ALERT_MEDIUM;
			}

			$this->data->setCachedData("ilExteEvalTest::getStandardDeviationOfTestResults", $standard_deviation);
		}
		return $standard_deviation;
	}


    /**
     * Calculate the standard deviation of values
     * @param   array   $data   list of values
     * @param   float   $mean   mean value
     * @return  float           standard deviation
     */
	protected function getStandardDeviation($data, $mean)
	{
		//Fetch the sum of squared differences between total score and it's mean
		$sum_sq_diff = $this->sumOfPowersOfDifferenceToMean($data, $mean, 2);

		//Calculate Standard deviation
		$std_deviation = sqrt($sum_sq_diff / (count($data) - 1));
	}

    # endregion

}