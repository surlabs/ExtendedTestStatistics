<?php

/**
 * Base class for all statistical evaluations
 * This class is not directly inherited,
 * but their childs ilExteEvalTest and ilExteEvalQuestion
 */
abstract class ilExteEvalBase
{
	##################
	# region constants
	##################

	/**
	 * type setting value for fixed question set
	 */
	const TEST_TYPE_FIXED = 'FIXED';

	/**
	 * type setting value for random question set
	 */
	const TEST_TYPE_RANDOM = 'RANDOM';

	/**
	 * type setting value for dynamic question set (continues testing mode)
	 */
	const TEST_TYPE_DYNAMIC = 'DYNAMIC';

	# endregion

	#########################
	# region static variables
	#########################

	/**
	 * @var bool    evaluation provides a single value for the overview level
	 */
	protected static $provides_value = false;

	/**
	 * @var bool    evaluation provides data for a details screen
	 */
	protected static $provides_details = false;

	/**
	 * @var array list of allowed test types, e.g. array(self::TEST_TYPE_FIXED)
	 */
	protected static $allowed_test_types = array();

	/**
	 * @var array    list of question types, e.g. array('assSingleChoice', 'assMultipleChoice', ...)
	 */
	protected static $allowed_question_types = array();

	# endregion

	########################
	# region class variables
	########################

	/**
	 * @var ilExtendedTestStatisticsPlugin    plugin object for txt() method
	 */
	protected $plugin;

	/**
	 * @var ilExteStatSourceData        source data for the calculations
	 */
	protected $data;

	# endregion

	#####################
	# region API (static)
	#####################

	/**
	 * @return string	class id (class name)
	 */
	final public static function _getId()
	{
		return strtolower(get_called_class());
	}

	/**
	 * @return bool
	 */
	public static function _isTestEvaluation()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public static function _isQuestionEvaluation()
	{
		return false;
	}

	/**
	 * @return array
	 */
	final public static function _allowedTestTypes()
	{
		return static::$allowed_test_types;
	}

	/**
	 * @return array
	 */
	final public static function _allowedQuestionTypes()
	{
		return static::$allowed_question_types;
	}

	/**
	 * @param string	$a_type		test type
	 * @return bool
	 */
	final public static function _isTestTypeAllowed($a_type)
	{
		return empty(static::$allowed_test_types) || in_array($a_type, static::$allowed_test_types);
	}

	/**
	 * @param string	$a_type		classname of question type
	 * @return bool
	 */
	final public static function _isQuestionTypeAllowed($a_type)
	{
		return empty(static::$allowed_question_types) || in_array($a_type, static::$allowed_question_types);
	}

	/**
	 * @return bool	evaluation provides a single value
	 */
	final public static function _providesValue()
	{
		return static::$provides_value;
	}

	/**
	 * @return bool	evaluation provides an array of details
	 */
	final public static function _providesDetails()
	{
		return static::$provides_details;
	}
	# endregion


	#####################
	# region API (object)
	#####################

	/**
	 * ilExtendedTestStatisticsEvalBase constructor.
	 * @param ilExteStatSourceData $a_data
	 * @param ilExtendedTestStatisticsPlugin $a_plugin
	 */
	final public function __construct($a_data, $a_plugin)
	{
		$this->data = $a_data;
		$this->plugin = $a_plugin;

		$this->plugin->includeClass('models/class.ilExteStatValue.php');
		$this->plugin->includeClass('models/class.ilExteStatColumn.php');
		$this->plugin->includeClass('models/class.ilExteStatDetails.php');
	}

	/**
	 * Get the title of the evaluation (to be used in lists or as headline)
	 * @return string
	 */
	public function getTitle()
	{
		return $this->txt('title_long');
	}

	/**
	 * Get a short title of the evaluation (to be used as a column header)
	 * @return string
	 */
	public function getShortTitle()
	{
		return $this->txt('title_short');
	}

	/**
	 * Get a description of the evaluation (shown as tooltip or info text)
	 * @return string
	 */
	public function getDescription()
	{
		return $this->txt('description');
	}

	# endregion

	##################################
	# region methods for child classes
	##################################

	/**
	 * Get a localized text
	 * The language variable will be prefixed with lowercase class name, e.g. 'ilmyevaluation_'
	 *
	 * @param string $a_langvar language variable
	 * @return string
	 */
	protected function txt($a_langvar)
	{
		return $this->plugin->txt(self::_getId() . '_' . $a_langvar);
	}

	/**
     * Calculate the sum of powers of the difference from values to their mean
     * (intermediate calculation for the standard deviation)
     *
	 * @param array         $data   list of values
	 * @param float         $mean   mean of values
	 * @param integer       $power  power to use
	 * @return float|int            calculated sum
	 */
	protected function sumOfPowersOfDifferenceToMean($data, $mean, $power = 2)
	{
		$sum_power_diff = 0.0;

		//Fetch the sum of squared differences between total score and it's mean
		foreach ($data as $id => $item) {
			$sum_power_diff += pow((float)$item - $mean, $power);
		}

		return $sum_power_diff;
	}

	# endregion
}