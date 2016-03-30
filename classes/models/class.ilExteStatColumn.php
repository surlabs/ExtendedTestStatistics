<?php

/**
 * Class ilExteStatColumn
 */
class ilExteStatColumn
{
	/**
	 * Defined column types
	 */
    const SORT_NONE = '';
	const SORT_TEXT = 'text';
	const SORT_NUMBER = 'number';


    /**
     * Unique name of the column in the table
     * (should be like a variable name)
     * @var string
     */
    public $name = '';


	/**
	 * Title to be shown in the header
	 * @var string
	 */
	public $title = null;


    /**
     * Sorting type
     *
     * @var string
     */
    public $sort = self::SORT_NONE;


    /**
     * Comment for the title
     * @var string
     */
    public $comment = '';


    /**
     * Create a value by parameters
     *
     * @param string $a_name
     * @param string $a_title
     * @param string $a_sort
     * @param string $a_comment
     * @return ilExteStatColumn
     */
    public static function _create($a_name, $a_title = '', $a_sort = self::SORT_NONE, $a_comment = '')
    {
        $column = new self;
        $column->name = $a_name;
        $column->title = $a_title ? $a_title : $a_name;
        $column->sort = $a_sort;
        $column->comment = $a_comment;

        return $column;
    }


}