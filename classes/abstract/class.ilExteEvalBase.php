<?php
// Copyright (c) 2017 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3, see LICENSE

/**
 * Base class for all statistical evaluations
 * This class is not directly inherited,
 * but their childs ilExteEvalTest and ilExteEvalQuestion
 */
abstract class ilExteEvalBase
{

	/**
	 * type settings for test types
	 */
	const TEST_TYPE_FIXED = 'FIXED';
	const TEST_TYPE_RANDOM = 'RANDOM';
	const TEST_TYPE_DYNAMIC = 'DYNAMIC';
	const TEST_TYPE_UNKNOWN = 'UNKNOWN';

	/**
	 * @var bool    evaluation provides a single value for the overview level
	 */
	protected $provides_value = false;

	/**
	 * @var bool    evaluation provides data for a details screen
	 */
	protected $provides_details = false;

    /**
     * @var bool    evaluation provides a chart
     */
	protected $provides_chart = false;

	/**
	 * @var bool    evaluation provides custom HTML
	 */
	protected $provides_HTML = false;

	/**
	 * @var array 	list of allowed test types, e.g. array(self::TEST_TYPE_FIXED)
	 */
	protected $allowed_test_types = array();

	/**
	 * @var array    list of question types, e.g. array('assSingleChoice', 'assMultipleChoice', ...)
	 */
	protected $allowed_question_types = array();

	/**
	 * @var string	specific prefix of language variables (lowercase class name is used as default)
	 */
	protected $lang_prefix = null;


	/**
	 * @var ilExtendedTestStatisticsPlugin    plugin object, used in txt() method
	 */
	protected $plugin;

	/**
	 * @var ilExtendedTestStatisticsCache	cache object
	 */
	protected $cache;

	/**
	 * @var ilExteStatSourceData        	source data for the calculations
	 */
	protected $data;

	/**
	 * @var ilExteStatParam[]				evaluation parameters (indexed by name)
	 */
	protected $params = array();



	/**
	 * Constructor
	 * @param ilExtendedTestStatisticsPlugin $a_plugin
	 * @param ilExtendedTestStatisticsCache	$a_cache
	 */
	public function __construct($a_plugin, $a_cache)
	{
		$this->plugin = $a_plugin;
		$this->cache = $a_cache;

		$this->plugin->includeClass('models/class.ilExteStatParam.php');
		$this->plugin->includeClass('models/class.ilExteStatValue.php');
		$this->plugin->includeClass('models/class.ilExteStatColumn.php');
		$this->plugin->includeClass('models/class.ilExteStatDetails.php');

		$this->initParams();

		// preload all values of the initialized evaluations
		$this->cache->preload(get_called_class(), 'value');
	}

	protected function initParams()
	{
		// get all parameter data for the evaluation
		$data = $this->plugin->getConfig()->getEvaluationParameters(get_class($this));

		// initialize the parameters of the evaluation
		foreach ($this->getAvailableParams() as $param)
		{
			// add the parameter
			$this->params[$param->name] = $param;

			//set the stored data if it exists
			if (isset($data[$param->name]))
			{
				switch ($param->type)
				{
					case ilExteStatParam::TYPE_INT:
						$param->value = (int) $data[$param->name];
						break;
					case ilExteStatParam::TYPE_FLOAT:
						$param->value = (float) $data[$param->name];
						break;
					case ilExteStatParam::TYPE_BOOLEAN:
						$param->value = (bool) $data[$param->name];
						break;
					case ilExteStatParam::TYPE_STRING:
						$param->value = (string) $data[$param->name];
						break;
				}
			}
		}
	}


	/**
	 * Set the source data
	 * This should be done before the evaluation is used on the PageGUI
	 * It can be ignored when the evaluation is called from the ConfigGUI
	 *
	 * @param ilExteStatSourceData $a_data
	 */
	public function setData($a_data)
	{
		$this->data = $a_data;
	}


	/**
	 * Get the prefix for language variables of the evaluation
	 * This prefix is used additionally to the prefix of the plugin
	 *
	 * @return string	prefix
	 */
	public function getLangPrefix()
	{
		return isset($this->lang_prefix) ? $this->lang_prefix : strtolower(get_called_class());
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

	/**
	 * Get a list of available parameters
	 *	@return ilExteStatParam[]
	 */
	public function getAvailableParams()
	{
		return array();
	}

	/**
	 * Get the initialized params
	 * @return ilExteStatParam[]		$name => ilExteStatParam
	 */
	public function getParams()
	{
		return $this->params;
	}


	/**
	 * Get the value of a single parameter
	 * @param $a_name
	 * @return mixed
	 */
	public function getParam($a_name)
	{
		return $this->params[$a_name]->value;
	}

	/**
	 * @return bool
	 */
	public function isTestTypeAllowed()
	{
		return empty($this->allowed_test_types) || in_array($this->data->getTestType(), $this->allowed_test_types);
	}

	/**
	 * @return bool
	 */
	final public function isQuestionTypeAllowed($a_type)
	{
		return empty($this->allowed_question_types) || in_array($a_type, $this->allowed_question_types);
	}

	/**
	 * @return bool	evaluation provides a single value
	 */
	public function providesValue()
	{
		return $this->provides_value;
	}

	/**
	 * @return bool	evaluation provides an array of details
	 */
	public function providesDetails()
	{
		return $this->provides_details;
	}

    /**
     * @return bool evaluation provides a chart
     */
	public function providesChart()
    {
        return $this->provides_chart;
    }

    /**
     * @return bool evaluation provides custom HTML
     */
    public function providesHTML()
    {
    	return $this->provides_HTML;
    }

	/**
	 * Get a localized text
	 * The language variable will be prefixed by self::_getLangPrefix()
	 *
	 * @param string $a_langvar language variable
	 * @return string
	 */
	public function txt($a_langvar)
	{
		return $this->plugin->txt($this->getLangPrefix() . '_' . $a_langvar);
	}


	/**
	 * Get a message saying that the evaluation is not available for the test type
	 * @return	string
	 */
	public function getMessageNotAvailableForTestType()
	{
		switch ($this->data->getTestType())
		{
			case self::TEST_TYPE_FIXED:
				return $this->plugin->txt('not_for_fixed_test');

			case self::TEST_TYPE_RANDOM:
				return $this->plugin->txt('not_for_random_test');

			case self::TEST_TYPE_DYNAMIC:
				return $this->plugin->txt('not_for_dynamic_test');

			default:
				return $this->plugin->txt('not_for_test_type');
		}
	}

	/**
	 * Get a message saying that the evaluation is not available for the question type
	 * @return	string
	 */
	public function getMessageNotAvailableForQuestionType()
	{
		return $this->plugin->txt('not_for_question_type');
	}

    /**
     * Generate a chart
     * @param ilExteStatDetails $a_details
     * @return ilChart
     */
	protected function generateChart($a_details)
    {
        include_once "Services/Chart/classes/class.ilChart.php";

        $id = rand(100000,999999);
        $datatype = null;
        switch ($a_details->chartType)
        {
            case ilExteStatDetails::CHART_PIE:
                /** @var ilChartPie $chart */
                $chart = ilChart::getInstanceByType(ilChart::TYPE_PIE, $id);
                break;

            case ilExteStatDetails::CHART_SPIDER:
                /** @var ilChartSpider $chart */
                $chart = ilChart::getInstanceByType(ilChart::TYPE_SPIDER, $id);
                break;

            case ilExteStatDetails::CHART_BARS:
            default:
                /** @var ilChartGrid $chart */
                $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $id);
                $chart->setXAxisToInteger(true);
                $datatype = ilChartGrid::DATA_BARS;
        }

        $labels = array();
		if (isset($a_details->chartLabelsColumn))
		{
			$colname = $a_details->columns[$a_details->chartLabelsColumn]->name;
			foreach ($a_details->rows as $rownum => $row)
			{
				$labels[$rownum] = ilUtil::secureString($row[$colname]->value, true);
			}

			if ($chart instanceof ilChartGrid)
			{
				foreach ($labels as $rownum => $label)
				{
					$labels[$rownum] = '<div class="ilExteStatDiagramLabelOuter"><div class="ilExteStatDiagramLabelInner">'.$label.'</div></div>';
				}
				$chart->setTicks($labels, $a_details->chartLines ?? false, true);
			}
			elseif ($chart instanceof ilChartSpider)    
			{
				$chart->setLegLabels($labels);
			}
		}

        foreach ($a_details->columns as $index => $column)
        {
            if ($column->isChartData)
            {
                $data = $chart->getDataInstance($datatype);
                $data->setLabel($column->title);
                if ($data instanceof ilChartDataBars)
				{
					$data->setBarOptions(0.5, "center", false);
				}

                foreach ($a_details->rows as $rownum => $row)
                {
                    /** @var ilExteStatValue $value */
                    foreach ($row as $colname => $value)
                    {
                        if ($colname == $column->name)
                        {
                        	if ($data instanceof ilChartDataBars)
							{
								$data->addPoint($rownum, $value->value);
							}
							elseif ($data instanceof ilChartDataPie)
							{
								$data->addPoint($value->value, isset($labels[$rownum]) ? $labels[$rownum] : $rownum);
							}
							elseif ($data instanceof ilChartDataSpider)
							{
								$data->addPoint($rownum, $value->value);
							}
                        }
                    }
                }
                $chart->addData($data);
            }
        }


		$legend = new ilChartLegend();
		$chart->setLegend($legend);
        $chart->setSize("100%",500);
		$chart->setAutoResize(true);
        return $chart;
    }

    /**
     * Calculate the mean value of a set of values (min 1)
     * @param float[] $values
     * @return float
     */
    protected function calcMean($values) : ?float
    {
        if (count($values) < 1) {
            return null;
        }
        $sum = 0;
        foreach ($values as $value) {
            $sum += $value;
        }
        return $sum / count($values);
    }

    /**
     * Calculate the variance of a set of values (min 2)
     * @param float[] $values
     * @param bool $with_bessel use the bessel correction
     * @return float
     */
    protected function calcVariance(array $values, bool $with_bessel)
    {
        if (count($values) < 2) {
            return null;
        }
        $mean = $this->calcMean($values);
        $sum = 0;
        foreach ($values as $value) {
            $sum += pow($value - $mean, 2);
        }
        if ($with_bessel) {
            return $sum / (count($values) - 1);
        }
        else {
            return $sum / (count($values));
        }
    }

    /**
     * Calculate the covariance of two sets of values (min 2)
     * @param float[] $values1
     * @param float[] $values2
     * @param bool $with_bessel use the bessel correction
     * @return float
     * @see https://de.wikipedia.org/wiki/Stichprobenkovarianz
     */
    protected function calcCovariance(array $values1, array $values2, bool $with_bessel)
    {
        if (count($values1) < 2 || count($values1) != count($values2)) {
            return null;
        }
        // ensure numeric keys for correct indexing
        $v1 = array_values($values1);
        $v2 = array_values($values2);

        $mean1 = $this->calcMean($v1);
        $mean2 = $this->calcMean($v2);

        $sum = 0;
        for ($i = 0; $i < count($values1); $i++) {
            $sum += ($v1[$i] - $mean1) * ($v2[$i] - $mean2);
        }
        if ($with_bessel) {
            return $sum / (count($v1) - 1);
        }
        else {
            return $sum / (count($v1));
        }
    }
}