<?php
// Copyright (c) 2017 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3, see LICENSE

/**
 * Basic class for doing statistics
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @version $Id$
 *
 */
class ilExtendedTestStatistics
{
	const LEVEL_TEST = 'test';
	const LEVEL_QUESTION = 'question';

	const PROVIDES_VALUE = 'value';
	const PROVIDES_DETAILS = 'details';

	/**
	 * @var ilExtendedTestStatisticsPlugin
	 */
	protected $plugin;

	/**
	 * @var	ilExtendedTestStatisticsConfig
	 */
	protected $config;

	/*
	 * @var ilObjTest
	 */
	protected $object;

	/**
	 * @var ilExteStatSourceData
	 */
	protected $data;

	/**
	 * @var ilExtendedTestStatisticsCache
	 */
	protected $cache;

	/**
	 * @var ilExteEvalBase[]	indexed by class name
	 */
	protected $evaluations;


	/**
	 * @var string
	 */
	protected $pass_selection;

	/**
	 * ilExtendedTestStatistics constructor.
	 *
	 * @param ilObjTest $a_test_obj
	 * @param ilExtendedTestStatisticsPlugin $a_plugin
	 */
	public function __construct($a_test_obj, $a_plugin)
	{
		$this->plugin = $a_plugin;
		$this->object = $a_test_obj;

		//Set the config object
		$this->config = $this->plugin->getConfig();

		// Set which pass should be evaluated
		$this->plugin->includeClass('models/class.ilExteStatSourceData.php');
		$this->pass_selection = $this->plugin->getUserPreference('evaluated_pass', ilExteStatSourceData::PASS_SCORED);

		// load the cache object
		$this->plugin->includeClass('class.ilExtendedTestStatisticsCache.php');
		$this->cache = new ilExtendedTestStatisticsCache($this->object->getTestId(), $this->pass_selection);
	}

	/**
	 * Clear the cache of this test
	 */
	public function flushCache()
	{
		$this->cache->flush();
	}

	/**
	 * Get the source data object
	 * @return ilExteStatSourceData
	 */
	public function getSourceData()
	{
		if (!isset($this->data))
		{
			$this->loadSourceData();
		}
		return $this->data;
	}

	/**
	 * Get a single loaded evaluation
	 * @param string $a_class  class name of the evaluation
	 * @return ilExteEvalBase|ilExteEvalTest|ilExteEvalQuestion|null
	 */
	public function getEvaluation($a_class)
	{
		if (!isset($this->evaluations))
		{
			$this->loadEvaluations();
		}
		return isset($this->evaluations[$a_class]) ? $this->evaluations[$a_class] : null;
	}

	/**
	 * Get a subset of the loaded evaluation objects
	 * @param   string  $a_level			level of the statistics, e.g. self::LEVEL_TEST or empty for all
	 * @param   string  $a_provides			provided result, e.g. self::PROVIDES_VALUE or empty for all
	 * @param   string  $a_question_type	question type (for question evaluations) or empty for all
	 * @return  ilExteEvalBase[]    indexed by class name
	 */
	public function getEvaluations($a_level = '', $a_provides = '', $a_question_type = '')
	{
		if (!isset($this->evaluations))
		{
			$this->loadEvaluations();
		}

		$selected = array();
		foreach ($this->evaluations as $class => $evaluation)
		{
			// check evaluation type
			if (($a_level == self::LEVEL_TEST && !is_subclass_of($class, 'ilExteEvalTest')) ||
				($a_level == self::LEVEL_QUESTION && !is_subclass_of($class, 'ilExteEvalQuestion')))
			{
				continue;
			}

			// check if value or details are provided
			if (($a_provides == self::PROVIDES_VALUE && !$evaluation->providesValue()) ||
				($a_provides == self::PROVIDES_DETAILS && !$evaluation->providesDetails()))
			{
				continue;
			}

			// check if question type is supported
			if (!empty($a_question_type) && !$evaluation->isQuestionTypeAllowed($a_question_type))
			{
				continue;
			}

			$selected[$class] = $evaluation;
		}
		return $selected;
	}

	/**
	 * Load the source data for all evaluations
	 */
	protected function loadSourceData()
	{
		// workaround for missing include in ilObjtest::getQuestionCount()
		if ($this->object->isRandomTest())
		{
			require_once('Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php');
		}
		$this->data = new ilExteStatSourceData($this->object, $this->plugin, $this->cache);
		$this->data->load($this->pass_selection);
	}

	/**
	 * Load the evaluation objects
	 */
	protected function loadEvaluations()
	{
		$isAdmin = $this->isAdmin();

		$this->evaluations = array();
		foreach ($this->config->getEvaluationClasses() as $class => $availability)
		{
			// check configured availability
			if ( $availability == ilExtendedTestStatisticsConfig::FOR_NONE ||
                !($isAdmin || $availability == ilExtendedTestStatisticsConfig::FOR_USER))
			{
				continue;
			}

			// instantiate the evaluation object
			$this->evaluations[$class] = new $class($this->plugin, $this->cache);
			$this->evaluations[$class]->setData($this->getSourceData());
		}
	}


	/**
	 * Check if the current user is administrator of the test system
	 * @return bool
	 */
	protected function isAdmin()
	{
		global $tree, $rbacsystem;

		foreach ($tree->getChilds(SYSTEM_FOLDER_ID) as $object)
		{
			if ($object["type"] == "assf")
			{
				return $rbacsystem->checkAccess("visible", $object["ref_id"]);
			}
		}
		return false;
	}
}

?>