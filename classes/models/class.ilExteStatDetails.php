<?php

/**
 * Class ilExteStatDetails
 */
class ilExteStatDetails
{
	/**
	 * Individual message for empty details
	 * @var string
	 */
	protected $emptyMessage;

    /**
     * Table columns
     * @var ilExteStatColumn[]
     */
    public $columns = array();

    /**
     * Table rows
     * @var array   rownum => colname => ilExteStatValue
     */
	public $rows = array();


	/**
	 * Get the message for empty details
	 * @return string
	 */
	public function getEmptyMessage()
	{
		global $lng;

		if (isset($this->emptyMessage))
		{
			return $this->emptyMessage;
		}
		return $lng->txt('no_items');
	}

	/**
	 * Get the message for empty details
	 * @param string	$message
	 * @return self
	 */
	public function setEmptyMessage($message)
	{
		$this->emptyMessage = $message;
		return $this;
	}
}